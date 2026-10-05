<?php

use App\Http\Controllers\Installer\InstallerController;
use App\Services\Installer\InstallerProgress;
use Illuminate\Support\Facades\Route;

Route::prefix('instalacion')
    ->name('installer.')
    ->middleware('not.installed')
    ->controller(InstallerController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{step}', 'show')
            ->whereIn('step', array_keys(InstallerProgress::STEPS))
            ->middleware('installer.step')
            ->name('show');

        Route::post('/requisitos', 'requirements')->middleware('installer.step:requisitos')->name('requirements');
        Route::post('/base-de-datos', 'database')->middleware('installer.step:base-de-datos')->name('database');
        Route::post('/cuenta', 'account')->middleware('installer.step:cuenta')->name('account');
        Route::post('/datos-publicos', 'publicData')->middleware('installer.step:datos-publicos')->name('public-data');
        Route::post('/horarios', 'schedule')->middleware('installer.step:horarios')->name('schedule');
        Route::post('/foto', 'photo')->middleware('installer.step:foto')->name('photo');
        Route::post('/tema', 'theme')->middleware('installer.step:tema')->name('theme');
        Route::post('/finalizar', 'finish')->middleware('installer.step:finalizar')->name('finish');
    });
