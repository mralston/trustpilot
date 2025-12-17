<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Mralston\Trustpilot\Events\ReviewCreated;
use Mralston\Trustpilot\Events\InvitationCreated;
use Mralston\Trustpilot\Events\InvitationSent;
use Mralston\Trustpilot\Events\InvitationFailed;

it('accepts webhook and dispatches event when secret matches', function () {
    Event::fake();

    $path = config('trustpilot.webhook.path');
    $header = config('trustpilot.webhook.header');
    $secret = config('trustpilot.webhook.secret');

    $response = $this->postJson($path, [
        'event' => 'review.created',
        'data' => ['id' => 'r1'],
    ], [
        $header => $secret,
    ]);

    $response->assertOk();
    Event::assertDispatched(ReviewCreated::class);
});

it('rejects webhook when secret is missing or invalid', function () {
    $path = config('trustpilot.webhook.path');

    $response = $this->postJson($path, [
        'event' => 'review.created',
        'data' => ['id' => 'r1'],
    ]); // No secret header

    $response->assertStatus(403);
});

it('dispatches invitation lifecycle events', function () {
    Event::fake();

    $path = config('trustpilot.webhook.path');
    $header = config('trustpilot.webhook.header');
    $secret = config('trustpilot.webhook.secret');

    // created
    $res1 = $this->postJson($path, [
        'event' => 'invitation.created',
        'data' => ['id' => 'i1'],
    ], [
        $header => $secret,
    ]);
    $res1->assertOk();

    // sent
    $res2 = $this->postJson($path, [
        'event' => 'invitation.sent',
        'data' => ['id' => 'i1'],
    ], [
        $header => $secret,
    ]);
    $res2->assertOk();

    // failed
    $res3 = $this->postJson($path, [
        'event' => 'invitation.failed',
        'data' => ['id' => 'i1'],
    ], [
        $header => $secret,
    ]);
    $res3->assertOk();

    Event::assertDispatched(InvitationCreated::class);
    Event::assertDispatched(InvitationSent::class);
    Event::assertDispatched(InvitationFailed::class);
});
