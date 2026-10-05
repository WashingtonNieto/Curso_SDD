<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AvailabilityService;
use App\Services\CalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(private readonly CalendarService $calendar) {}

    public function index(AvailabilityService $availability): View
    {
        return view('panel.calendar.index', [
            'config' => [
                'eventsUrl' => route('panel.calendar.events'),
                'createUrl' => route('panel.appointments.create'),
                'businessHours' => $this->calendar->businessHours(),
                'scrollTime' => $this->calendar->scrollTime(),
            ],
            'statuses' => Appointment::STATUSES,
            'vacationMode' => $availability->isVacationMode(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}/'],
            'end' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}/'],
        ]);

        $from = CarbonImmutable::createFromFormat('Y-m-d', substr($data['start'], 0, 10))->startOfDay();
        $to = CarbonImmutable::createFromFormat('Y-m-d', substr($data['end'], 0, 10))->startOfDay();

        abort_if($to->lte($from) || $from->diffInDays($to) > 62, 422, 'El rango de fechas no es válido.');

        return response()->json($this->calendar->events($from, $to));
    }
}
