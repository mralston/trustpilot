<?php

use Illuminate\Support\Facades\Route;
use Mralston\Trustpilot\Http\Controllers\TrustpilotWebhookController;

Route::post(config('trustpilot.webhook.path', '/trustpilot/webhook'), [TrustpilotWebhookController::class, 'handle'])
    ->name('trustpilot.webhook');
