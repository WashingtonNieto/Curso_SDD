<?php

use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\EnsureInstallerStep;
use App\Http\Middleware\RedirectIfInstalled;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [EnsureInstalled::class], append: [SecurityHeaders::class]);
        $middleware->alias([
            'not.installed' => RedirectIfInstalled::class,
            'installer.step' => EnsureInstallerStep::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel.home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && $request->routeIs('login.store')) {
                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->withErrors(['login' => 'La página llevaba mucho tiempo abierta. Vuelve a introducir tus datos.']);
            }
        });
    })->create();
