<?php

namespace App\Providers;

use App\Support\EnvWriter;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

class EnvironmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        $env = EnvWriter::forApp();
        $example = base_path('.env.example');

        if (! $env->exists() && is_file($example)) {
            copy($example, base_path('.env'));
        }

        if (empty(config('app.key'))) {
            $key = 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher')));
            $env->set(['APP_KEY' => $key]);
            config(['app.key' => $key]);
        }
    }
}
