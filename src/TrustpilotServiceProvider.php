<?php

declare(strict_types=1);

namespace Mralston\Trustpilot;

use Illuminate\Support\ServiceProvider;
use Mralston\Trustpilot\Services\TrustpilotService;

class TrustpilotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/trustpilot.php', 'trustpilot');

        $this->app->singleton(TrustpilotService::class, function ($app) {
            $config = $app['config']->get('trustpilot');
            return new TrustpilotService(
                $config['api_key'] ?? '',
                $config['api_secret'] ?? '',
                $config['business_unit_id'] ?? null,
                $config['base_url'] ?? null
            );
        });

        $this->app->alias(TrustpilotService::class, 'trustpilot');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/trustpilot.php' => config_path('trustpilot.php'),
        ], 'trustpilot-config');

        // Routes for webhooks
        $config = $this->app['config']->get('trustpilot.webhook');
        if (($config['enabled'] ?? true) && function_exists('config_path')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }
}
