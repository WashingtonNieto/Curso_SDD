<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\AppointmentRequest;
use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\Patient;
use App\Services\AppointmentService;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'modalidad' => ['nullable', Rule::in(array_keys(Appointment::MODALITIES))],
            'estado' => ['nullable', Rule::in(array_keys(Appointment::STATUSES))],
            'origen' => ['nullable', Rule::in(array_keys(Appointment::SOURCES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $appointments = Appointment::query()
            ->with('patient')
            ->when($filters['desde'] ?? null, fn (Builder $query, $date) => $query->where('starts_at', '>=', CarbonImmutable::parse($date)->startOfDay()))
            ->when($filters['hasta'] ?? null, fn (Builder $query, $date) => $query->where('starts_at', '<=', CarbonImmutable::parse($date)->endOfDay()))
            ->when($filters['modalidad'] ?? null, fn (Builder $query, $modality) => $query->where('modality', $modality))
            ->when($filters['estado'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['origen'] ?? null, fn (Builder $query, $source) => $query->where('source', $source))
            ->when($filters['q'] ?? null, fn (Builder $query, $text) => $this->applySearch($query, $text))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('panel.appointments.index', [
            'appointments' => $appointments,
            'filters' => $filters,
            'hasAny' => Appointment::exists(),
        ]);
    }

    public function create(Request $request): View
    {
        $appointment = new Appointment([
            'modality' => in_array($request->query('modalidad'), AvailabilitySetting::MODALITIES, true) ? $request->query('modalidad') : 'presencial',
            'status' => 'confirmada',
            'source' => 'telefono',
        ]);

        if ($patient = Patient::find($request->integer('paciente') ?: null)) {
            $appointment->setRelation('patient', $patient);

            if (! $request->query('modalidad') && $patient->preferred_modality) {
                $appointment->modality = $patient->preferred_modality;
            }
        }

        return view('panel.appointments.create', $this->formData($appointment) + [
            'initialDate' => $this->validDate($request->query('fecha')) ?? CarbonImmutable::today()->toDateString(),
            'initialTime' => $this->validTime($request->query('hora')),
        ]);
    }

    public function store(AppointmentRequest $request): RedirectResponse
    {
        $appointment = $this->appointments->create($request->appointmentData());

        return redirect()->route('panel.appointments.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cita creada para '.$appointment->patient->full_name.' el '.$appointment->starts_at->translatedFormat('j \d\e F \a \l\a\s H:i').'.',
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        $appointment->load('patient');

        return view('panel.appointments.edit', $this->formData($appointment) + [
            'initialDate' => $appointment->starts_at->toDateString(),
            'initialTime' => $appointment->starts_at->format('H:i'),
        ]);
    }

    public function update(AppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->appointments->update($appointment, $request->appointmentData());

        return redirect()->route('panel.appointments.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cita actualizada correctamente.',
        ]);
    }

    public function status(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in(array_keys(Appointment::STATUSES))]])['status'];
        $this->appointments->changeStatus($appointment, $status);
        $message = 'Estado cambiado a “'.Appointment::STATUSES[$status].'”.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'status' => $status]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function move(Request $request, Appointment $appointment): JsonResponse
    {
        $start = $request->validate(['start' => ['required', 'date_format:Y-m-d H:i']])['start'];
        $appointment = $this->appointments->move($appointment, CarbonImmutable::createFromFormat('Y-m-d H:i', $start)->startOfMinute());

        return response()->json([
            'message' => 'Cita movida al '.$appointment->starts_at->translatedFormat('l j \d\e F \a \l\a\s H:i').'.',
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $this->appointments->delete($appointment);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Cita eliminada.']);
        }

        return redirect()->route('panel.appointments.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cita eliminada.',
        ]);
    }

    private function formData(Appointment $appointment): array
    {
        $prices = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $prices[$modality] = $this->appointments->defaultPrice($modality);
        }

        return ['appointment' => $appointment, 'prices' => $prices];
    }

    private function applySearch(Builder $query, string $text): Builder
    {
        $text = trim($text);
        $phone = Phone::normalize($text);

        return $query->where(function (Builder $query) use ($text, $phone) {
            $query->where('reason', 'like', "%$text%")
                ->orWhereHas('patient', function (Builder $patient) use ($text, $phone) {
                    $patient->withTrashed()->where(function (Builder $patient) use ($text, $phone) {
                        $patient->where(function (Builder $names) use ($text) {
                            foreach (preg_split('/\s+/', $text) as $word) {
                                $names->where(fn (Builder $name) => $name->where('first_name', 'like', "%$word%")->orWhere('last_name', 'like', "%$word%"));
                            }
                        });

                        if ($phone !== null && strlen($phone) >= 3) {
                            $patient->orWhere('phone', 'like', "%$phone%");
                        }
                    });
                });
        });
    }

    private function validDate(?string $value): ?string
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }

    private function validTime(?string $value): ?string
    {
        return $value && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : null;
    }
}
