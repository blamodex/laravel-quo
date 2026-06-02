<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests;

use Blamodex\Quo\QuoServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QuoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('quo.api_key', 'test-api-key');
        $app['config']->set('quo.base_url', 'https://api.openphone.com');
    }
}
