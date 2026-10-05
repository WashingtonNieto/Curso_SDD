<?php

namespace App\Services\Installer;

use App\Support\EnvWriter;
use Database\Seeders\BaseDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use RuntimeException;

class DatabaseInstaller
{
    public function install(array $credentials): void
    {
        $this->createDatabase($credentials);
        $this->useConnection($credentials);

        Cache::flush();
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => BaseDataSeeder::class, '--force' => true]);
    }

    public function writeEnvironment(array $credentials): void
    {
        $values = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $credentials['db_host'],
            'DB_PORT' => (string) $credentials['db_port'],
            'DB_DATABASE' => $credentials['db_database'],
            'DB_USERNAME' => $credentials['db_username'],
            'DB_PASSWORD' => (string) ($credentials['db_password'] ?? ''),
        ];

        $env = EnvWriter::forApp();
        $unchanged = collect($values)->every(fn (string $value, string $key) => $env->get($key) === $value);

        if (! $unchanged) {
            $env->set($values);
        }
    }

    public function isReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return DB::getSchemaBuilder()->hasTable('users');
        } catch (\Throwable) {
            return false;
        }
    }

    private function createDatabase(array $credentials): void
    {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $credentials['db_host'], $credentials['db_port']),
                $credentials['db_username'],
                (string) ($credentials['db_password'] ?? ''),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $credentials['db_database']
            ));
        } catch (PDOException $e) {
            throw new RuntimeException($this->friendlyMessage($e), previous: $e);
        }
    }

    private function useConnection(array $credentials): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $credentials['db_host'],
            'database.connections.mysql.port' => $credentials['db_port'],
            'database.connections.mysql.database' => $credentials['db_database'],
            'database.connections.mysql.username' => $credentials['db_username'],
            'database.connections.mysql.password' => (string) ($credentials['db_password'] ?? ''),
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    private function friendlyMessage(PDOException $e): string
    {
        $code = (int) ($e->errorInfo[1] ?? $e->getCode());

        return match ($code) {
            1045 => 'El usuario o la contraseña de la base de datos no son correctos.',
            1044, 1227 => 'El usuario indicado no tiene permisos para crear la base de datos.',
            2002, 2003, 2006 => 'No se puede conectar con el servidor de base de datos. Comprueba que MySQL está arrancado (por ejemplo, desde el panel de XAMPP) y que el servidor y el puerto son correctos.',
            2005 => 'No se encuentra el servidor indicado. Revisa el nombre del servidor.',
            default => 'No se ha podido preparar la base de datos. Detalle técnico: '.$e->getMessage(),
        };
    }
}
