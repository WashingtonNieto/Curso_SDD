<?php

namespace App\Http\Middleware;

use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\InstallerProgress;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallerStep
{
    public function __construct(
        private readonly InstallerProgress $progress,
        private readonly DatabaseInstaller $database,
    ) {}

    public function handle(Request $request, Closure $next, ?string $step = null): Response
    {
        $step ??= (string) $request->route('step');

        if (! $this->progress->isReachable($step)) {
            return redirect()->route('installer.show', $this->progress->firstPending());
        }

        if ($this->progress->needsDatabase($step) && ! $this->database->isReachable()) {
            $this->progress->forget('base-de-datos');

            return redirect()->route('installer.show', 'base-de-datos')->withErrors([
                'database' => 'No se puede conectar con la base de datos. Comprueba que MySQL está arrancado y vuelve a confirmar los datos de conexión.',
            ]);
        }

        return $next($request);
    }
}
