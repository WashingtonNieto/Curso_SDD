<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\AvailabilityScheduleRequest;
use App\Http\Requests\Panel\WeeklySlotsRequest;
use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\VacationPeriod;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public const WEEKDAYS = [1 => ['Lun', 'Lunes'], 2 => ['Mar', 'Martes'], 3 => ['Mié', 'Miércoles'], 4 => ['Jue', 'Jueves'], 5 => ['Vie', 'Viernes'], 6 => ['Sáb', 'Sábado'], 7 => ['Dom', 'Domingo']];

    public function __construct(private readonly AvailabilityService $availability) {}

    public function index(Request $request): View
    {
        $modalities = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $setting = $this->availability->setting($modality);
            $modalities[$modality] = [
                'setting' => $setting,
                'grid' => $this->availability->grid($modality),
                'marked' => $this->availability->weeklySlots($modality),
            ];
        }

        $activeTab = in_array($request->query('tab'), AvailabilitySetting::MODALITIES, true) ? $request->query('tab') : 'online';

        return view('panel.availability.index', [
            'modalities' => $modalities,
            'activeTab' => $activeTab,
            'weekdays' => self::WEEKDAYS,
            'presets' => config('psicocms.session_duration_presets'),
            'vacationMode' => $this->availability->isVacationMode(),
            'vacationPeriods' => VacationPeriod::upcoming()->get()->each(function (VacationPeriod $period) {
                $period->appointments_count = $this->appointmentsInPeriod($period);
            }),
            'needsReview' => $this->availability->modalitiesNeedingReview(),
        ]);
    }

    public function updateSchedule(AvailabilityScheduleRequest $request, string $modality): RedirectResponse
    {
        $changed = $this->availability->updateSchedule($modality, $request->validated($modality));

        return redirect()->route('panel.availability', ['tab' => $modality])->with('toast', [
            'type' => 'success',
            'message' => $changed
                ? 'Horario guardado. Revisa y vuelve a marcar tus huecos semanales online y presenciales.'
                : 'Horario guardado. No ha habido cambios en tus huecos.',
        ]);
    }

    public function updateSlots(WeeklySlotsRequest $request, string $modality): JsonResponse
    {
        $saved = $this->availability->saveWeeklySlots($modality, $request->validated('slots'));

        return response()->json([
            'message' => $saved > 0
                ? "Disponibilidad guardada: $saved huecos semanales marcados."
                : 'Disponibilidad guardada. No has marcado ningún hueco para esta modalidad.',
            'saved' => $saved,
            'needs_review' => $this->availability->modalitiesNeedingReview(),
        ]);
    }

    public function vacationMode(Request $request): JsonResponse
    {
        $enabled = $request->validate(['enabled' => ['required', 'boolean']])['enabled'];
        $this->availability->setVacationMode((bool) $enabled);

        return response()->json([
            'enabled' => (bool) $enabled,
            'message' => $enabled
                ? 'Modo vacaciones activado: tus pacientes no podrán reservar nuevas citas.'
                : 'Modo vacaciones desactivado: las reservas vuelven a estar abiertas.',
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'modalidad' => ['required', Rule::in(AvailabilitySetting::MODALITIES)],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'ignorar' => ['nullable', 'integer'],
        ]);

        $blocked = $this->availability->blockReason($data['fecha']);
        $slots = $this->availability->slotsForDate($data['modalidad'], $data['fecha'], $data['ignorar'] ?? null, true);

        return response()->json([
            'slots' => $slots,
            'blocked' => $blocked,
            'message' => match (true) {
                $blocked === 'vacation_mode' => 'Tienes activado el modo vacaciones. Puedes usar una hora personalizada.',
                $blocked === 'vacation_period' => 'Ese día está dentro de un periodo de vacaciones.',
                $slots === [] => 'No quedan huecos libres ese día para esta modalidad.',
                default => null,
            },
        ]);
    }

    private function appointmentsInPeriod(VacationPeriod $period): int
    {
        return Appointment::notCancelled()
            ->whereBetween('starts_at', [$period->start_date->copy()->startOfDay(), $period->end_date->copy()->endOfDay()])
            ->count();
    }
}
