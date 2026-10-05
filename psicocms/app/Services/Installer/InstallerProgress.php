<?php

namespace App\Services\Installer;

class InstallerProgress
{
    public const STEPS = [
        'requisitos' => 'Bienvenida',
        'base-de-datos' => 'Base de datos',
        'cuenta' => 'Cuenta de acceso',
        'datos-publicos' => 'Datos de tu web',
        'horarios' => 'Horarios',
        'foto' => 'Tu foto',
        'tema' => 'Tema visual',
        'finalizar' => 'Finalizar',
    ];

    private const SESSION_KEY = 'installer.completed';

    private const NEEDS_DATABASE_FROM = 'cuenta';

    public function completed(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function isCompleted(string $step): bool
    {
        return in_array($step, $this->completed(), true);
    }

    public function markCompleted(string $step): void
    {
        session()->put(self::SESSION_KEY, array_values(array_unique([...$this->completed(), $step])));
    }

    public function forget(string $step): void
    {
        session()->put(self::SESSION_KEY, array_values(array_diff($this->completed(), [$step])));
    }

    public function reset(): void
    {
        session()->forget('installer');
    }

    public function firstPending(): string
    {
        foreach (array_keys(self::STEPS) as $step) {
            if ($step !== 'finalizar' && ! $this->isCompleted($step)) {
                return $step;
            }
        }

        return 'finalizar';
    }

    public function position(string $step): int
    {
        return array_search($step, array_keys(self::STEPS), true) + 1;
    }

    public function isReachable(string $step): bool
    {
        return $this->position($step) <= $this->position($this->firstPending());
    }

    public function needsDatabase(string $step): bool
    {
        return $this->position($step) >= $this->position(self::NEEDS_DATABASE_FROM);
    }

    public function next(string $step): string
    {
        return array_keys(self::STEPS)[$this->position($step)] ?? 'finalizar';
    }

    public function previous(string $step): ?string
    {
        return array_keys(self::STEPS)[$this->position($step) - 2] ?? null;
    }
}
