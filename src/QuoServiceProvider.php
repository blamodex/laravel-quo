<?php

declare(strict_types=1);

namespace Blamodex\Quo;

use Blamodex\Quo\Services\QuoService;
use Illuminate\Support\ServiceProvider;

class QuoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/quo.php', 'blamodex.quo');

        $this->app->singleton(QuoService::class, function ($app) {
            return new QuoService(
                apiKey: config('blamodex.quo.api_key'),
                baseUrl: config('blamodex.quo.base_url'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/quo.php' => config_path('quo.php'),
        ], 'blamodex-quo-config');
    }
}
