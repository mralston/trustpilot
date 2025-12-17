<?php

return [
    'api_key' => env('TRUSTPILOT_API_KEY'),
    'api_secret' => env('TRUSTPILOT_API_SECRET'),
    'business_unit_id' => env('TRUSTPILOT_BUSINESS_UNIT_ID'),
    'base_url' => env('TRUSTPILOT_API_BASE', 'https://api.trustpilot.com'),

    // Some APIs (like Invitations) are served from a different host
    'invitations_base_url' => env('TRUSTPILOT_INVITATIONS_API_BASE', 'https://invitations-api.trustpilot.com'),

    // Webhooks
    'webhook' => [
        'enabled' => env('TRUSTPILOT_WEBHOOK_ENABLED', true),
        'path' => env('TRUSTPILOT_WEBHOOK_PATH', '/trustpilot/webhook'),
        'secret' => env('TRUSTPILOT_WEBHOOK_SECRET'),
        // Header name for the shared secret validation
        'header' => env('TRUSTPILOT_WEBHOOK_HEADER', 'X-Trustpilot-Secret'),
    ],
];
