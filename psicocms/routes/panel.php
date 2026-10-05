<?php

use App\Http\Controllers\Panel\AppointmentController;
use App\Http\Controllers\Panel\AvailabilityController;
use App\Http\Controllers\Panel\CalendarController;
use App\Http\Controllers\Panel\ComingSoonController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\VacationPeriodController;
use App\Models\AvailabilitySetting;
use Illuminate\Support\Facades\Route;

Route::prefix('panel-psicologa')
    ->name('panel.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('home');
        Route::redirect('/perfil', '/panel-psicologa/configuracion/general')->name('profile');

        Route::controller(AvailabilityController::class)->prefix('disponibilidad')->name('availability')->group(function () {
            Route::get('/', 'index')->name('');
            Route::get('/huecos', 'slots')->name('.slots');
            Route::patch('/modo-vacaciones', 'vacationMode')->name('.vacation-mode');
            Route::put('/{modality}/configuracion', 'updateSchedule')->whereIn('modality', AvailabilitySetting::MODALITIES)->name('.schedule');
            Route::put('/{modality}/huecos-semanales', 'updateSlots')->whereIn('modality', AvailabilitySetting::MODALITIES)->name('.weekly');
        });

        Route::post('/disponibilidad/vacaciones', [VacationPeriodController::class, 'store'])->name('availability.vacations.store');
        Route::delete('/disponibilidad/vacaciones/{period}', [VacationPeriodController::class, 'destroy'])->name('availability.vacations.destroy');

        Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/calendario/eventos', [CalendarController::class, 'events'])->name('calendar.events');

        Route::patch('/citas/{appointment}/estado', [AppointmentController::class, 'status'])->name('appointments.status');
        Route::patch('/citas/{appointment}/mover', [AppointmentController::class, 'move'])->name('appointments.move');
        Route::resource('citas', AppointmentController::class)
            ->parameters(['citas' => 'appointment'])
            ->names('appointments')
            ->except('show');

        foreach (config('panel.coming_soon') as $name => $section) {
            Route::get($section['uri'], ComingSoonController::class)->name($name);
        }
    });
