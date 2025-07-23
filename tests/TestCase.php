<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests;

use Eltabarani\ReusableQuery\Providers\ReusableQueryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ReusableQueryServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
