<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Services\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardStatsService $stats, AvailabilityService $availability): View
    {
        return view('panel.home', [
            'summary' => $stats->summary(),
            'todayAppointments' => $stats->todayAppointments(),
            'newBookings' => $stats->newWebBookings(),
            'weeklyAvailability' => $stats->weeklyAvailability(),
            'activity' => $stats->lastWeeksActivity(),
            'vacationMode' => $availability->isVacationMode(),
            'needsReview' => $availability->modalitiesNeedingReview(),
        ]);
    }
}
