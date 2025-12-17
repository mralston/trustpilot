<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Mralston\Trustpilot\TrustpilotServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [TrustpilotServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('trustpilot.api_key', 'test_key');
        $app['config']->set('trustpilot.api_secret', 'test_secret');
        $app['config']->set('trustpilot.business_unit_id', 'business_unit_123');
        $app['config']->set('trustpilot.base_url', 'https://api.trustpilot.com');
        $app['config']->set('trustpilot.webhook.secret', 'whsec_123');
    }
}
