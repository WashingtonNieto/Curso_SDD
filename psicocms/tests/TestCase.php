<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markAsInstalled();
    }

    protected function markAsInstalled(): void
    {
        $lock = storage_path('framework/testing/installed.lock');
        File::ensureDirectoryExists(dirname($lock));
        File::put($lock, 'testing');

        config(['psicocms.installed_lock' => $lock]);
    }
}
