<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\VacationPeriodRequest;
use App\Models\Appointment;
use App\Models\VacationPeriod;
use Illuminate\Http\RedirectResponse;

class VacationPeriodController extends Controller
{
    public function store(VacationPeriodRequest $request): RedirectResponse
    {
        $period = VacationPeriod::create($request->validated());

        $appointments = Appointment::notCancelled()
            ->whereBetween('starts_at', [$period->start_date->copy()->startOfDay(), $period->end_date->copy()->endOfDay()])
            ->count();

        $message = 'Periodo de vacaciones añadido: esos días quedan bloqueados para nuevas reservas.';

        if ($appointments > 0) {
            $message .= ' Ojo: ya tienes '.trans_choice('{1} 1 cita|[2,*] :count citas', $appointments).' en esas fechas; revísalas.';
        }

        return redirect()->route('panel.availability')->with('toast', [
            'type' => $appointments > 0 ? 'info' : 'success',
            'message' => $message,
        ]);
    }

    public function destroy(VacationPeriod $period): RedirectResponse
    {
        $period->delete();

        return redirect()->route('panel.availability')->with('toast', [
            'type' => 'success',
            'message' => 'Periodo de vacaciones eliminado.',
        ]);
    }
}
