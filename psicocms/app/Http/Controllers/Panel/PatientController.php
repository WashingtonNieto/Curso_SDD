<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PatientRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    private const PER_PAGE = 10;

    public const TABS = ['datos', 'citas', 'historia', 'documentos'];

    public function __construct(private readonly PatientService $patients) {}

    public function index(Request $request): View
    {
        return view('panel.patients.index', [
            'filters' => $this->filters($request),
            'stats' => $this->patients->stats(),
            'hasAny' => Patient::exists(),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $filters = $this->filters($request);
        $page = $this->patients->listQuery($filters)->paginate(self::PER_PAGE)->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Patient $patient) => $this->patients->row($patient))->values(),
            'meta' => [
                'page' => $page->currentPage(),
                'lastPage' => $page->lastPage(),
                'total' => $page->total(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
            ],
            'filtered' => collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty(),
            'stats' => $this->patients->stats(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $text = trim((string) $request->query('q'));

        if (mb_strlen($text) < 2) {
            return response()->json(['data' => []]);
        }

        $patients = Patient::query()
            ->search(mb_substr($text, 0, 100))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => $patients->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'firstName' => $patient->first_name,
                'lastName' => $patient->last_name,
                'name' => $patient->full_name,
                'initials' => $patient->initials,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'statusLabel' => Patient::STATUSES[$patient->status] ?? $patient->status,
            ])->values(),
        ]);
    }

    public function create(): View
    {
        return view('panel.patients.create', [
            'patient' => new Patient(['status' => 'activo']),
        ]);
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        [$patient, $restored] = $this->patients->createManual($request->validated());

        return redirect()->route('panel.patients.show', $patient)->with('toast', [
            'type' => 'success',
            'message' => $restored
                ? 'Se ha recuperado la ficha anterior de '.$patient->full_name.' con los datos nuevos.'
                : 'Paciente '.$patient->full_name.' añadido correctamente.',
        ]);
    }

    public function show(Request $request, Patient $patient): View
    {
        $appointments = $patient->appointments()->orderByDesc('starts_at')->get();
        $tab = in_array($request->query('pestana'), self::TABS, true) ? $request->query('pestana') : 'datos';

        return view('panel.patients.show', [
            'patient' => $patient,
            'summary' => $this->patients->summary($patient),
            'upcoming' => $appointments->filter(fn (Appointment $appointment) => $appointment->starts_at->isFuture())->sortBy('starts_at')->values(),
            'past' => $appointments->reject(fn (Appointment $appointment) => $appointment->starts_at->isFuture())->values(),
            'tab' => $tab,
            'deleteMessage' => $this->patients->deleteMessage($patient),
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('panel.patients.edit', ['patient' => $patient]);
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $this->patients->save($patient, $request->validated());

        return redirect()->route('panel.patients.show', $patient)->with('toast', [
            'type' => 'success',
            'message' => 'Datos de '.$patient->full_name.' actualizados.',
        ]);
    }

    public function destroy(Request $request, Patient $patient): RedirectResponse|JsonResponse
    {
        $patient->delete();
        $message = 'Ficha de '.$patient->full_name.' eliminada.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('panel.patients.index')->with('toast', ['type' => 'success', 'message' => $message]);
    }

    private function filters(Request $request): array
    {
        return validator($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(array_keys(Patient::STATUSES))],
            'modalidad' => ['nullable', Rule::in(array_keys(Appointment::MODALITIES))],
            'genero' => ['nullable', Rule::in(array_keys(Patient::GENDERS))],
        ])->valid();
    }
}
