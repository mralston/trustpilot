<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Mralston\Trustpilot\DTOs\BusinessUnit;
use Mralston\Trustpilot\DTOs\BusinessUnitStats;
use Mralston\Trustpilot\DTOs\Invitation;
use Mralston\Trustpilot\DTOs\InvitationResponse;
use Mralston\Trustpilot\DTOs\InvitationStatus;
use Mralston\Trustpilot\DTOs\Review;
use Mralston\Trustpilot\Services\TrustpilotService;
use Mralston\Trustpilot\Http\Requests\GetBusinessUnitRequest;
use Mralston\Trustpilot\Http\Requests\GetBusinessUnitReviewsRequest;
use Mralston\Trustpilot\Http\Requests\SendInvitationRequest;
use Mralston\Trustpilot\Http\Requests\GetInvitationRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('retrieves business unit details', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetBusinessUnitRequest::class => MockResponse::make([
            'id' => 'business_unit_123',
            'displayName' => 'Acme Inc',
            'stars' => 4.7,
            'numberOfReviews' => 1234,
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $unit = $service->getBusinessUnit();
    expect($unit)->toBeInstanceOf(BusinessUnit::class)
        ->and($unit->displayName)->toBe('Acme Inc')
        ->and($unit->stars)->toBe(4.7)
        ->and($unit->numberOfReviews)->toBe(1234);
});

it('retrieves business unit details with nested fields like real API', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetBusinessUnitRequest::class => MockResponse::make([
            'id' => 'business_unit_456',
            'displayName' => 'Project Solar',
            'score' => [
                'stars' => 4.5,
                'trustScore' => 4.6,
            ],
            'numberOfReviews' => [
                'total' => 6110,
                'usedForTrustScoreCalculation' => 5962,
                'oneStar' => 589,
                'twoStars' => 82,
                'threeStars' => 81,
                'fourStars' => 480,
                'fiveStars' => 4878,
            ],
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $unit = $service->getBusinessUnit();
    expect($unit)->toBeInstanceOf(BusinessUnit::class)
        ->and($unit->displayName)->toBe('Project Solar')
        ->and($unit->stars)->toBe(4.5)
        ->and($unit->numberOfReviews)->toBe(6110);
});

it('retrieves business unit stats DTO with flattened fields', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetBusinessUnitRequest::class => MockResponse::make([
            'id' => 'business_unit_789',
            'displayName' => 'Project Solar',
            'score' => [
                'stars' => 4.5,
                'trustScore' => 4.6,
            ],
            'numberOfReviews' => [
                'total' => 6110,
                'usedForTrustScoreCalculation' => 5962,
                'oneStar' => 589,
                'twoStars' => 82,
                'threeStars' => 81,
                'fourStars' => 480,
                'fiveStars' => 4878,
            ],
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $stats = $service->getBusinessUnitStats();

    expect($stats)->toBeInstanceOf(BusinessUnitStats::class)
        ->and($stats->stars)->toBe(4.5)
        ->and($stats->trustScore)->toBe(4.6)
        ->and($stats->totalReviews)->toBe(6110)
        ->and($stats->reviewsUsedForTrustScoreCalculation)->toBe(5962)
        ->and($stats->oneStarReviews)->toBe(589)
        ->and($stats->twoStarReviews)->toBe(82)
        ->and($stats->threeStarReviews)->toBe(81)
        ->and($stats->fourStarReviews)->toBe(480)
        ->and($stats->fiveStarReviews)->toBe(4878);
});

it('lists reviews for a business unit', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetBusinessUnitReviewsRequest::class => MockResponse::make([
            'reviews' => [
                ['id' => 'r1', 'stars' => 5, 'text' => 'Great!'],
                ['id' => 'r2', 'stars' => 3, 'text' => 'Okay'],
            ],
            'total' => 2,
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $reviews = $service->listReviews();
    expect($reviews)->toBeInstanceOf(Collection::class)
        ->and($reviews->count())->toBe(2)
        ->and($reviews->first())->toBeInstanceOf(Review::class)
        ->and($reviews->first()->stars)->toBe(5);
});

it('retrieves invitation status by id', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetInvitationRequest::class => MockResponse::make([
            'id' => 'inv_123',
            'status' => 'notsent',
            'recipient' => [
                'name' => 'Jane',
                'email' => 'jane@example.com',
            ],
            'referenceId' => 'order-1',
            'createdTime' => '2025-01-01T10:00:00Z',
            'sentTime' => null,
            'preferredSendTime' => null,
            'source' => 'InvitationApi',
            'invitationType' => null,
            'includesProductReviewInvitations' => false,
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $status = $service->getInvitation('inv_123');

    expect($status)->toBeInstanceOf(InvitationStatus::class)
        ->and($status->id)->toBe('inv_123')
        ->and($status->status)->toBe('notsent')
        ->and($status->recipientEmail)->toBe('jane@example.com')
        ->and($status->recipientName)->toBe('Jane')
        ->and($status->referenceId)->toBe('order-1')
        ->and($status->source)->toBe('InvitationApi');
});

it('throws on error when retrieving invitation status', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        GetInvitationRequest::class => MockResponse::make('<html>Not found</html>', 404),
    ]);

    $service->connector()->withMockClient($mock);

    $service->getInvitation('missing');
})->throws(RuntimeException::class);

it('lists reviews lazily across pages', function () {
    $service = app(TrustpilotService::class);

    $responses = [
        MockResponse::make([
            'reviews' => [
                ['id' => 'r1', 'stars' => 5, 'text' => 'Great!'],
                ['id' => 'r2', 'stars' => 4, 'text' => 'Good'],
            ],
            'nextPageToken' => 'next-123',
        ], 200),
        MockResponse::make([
            'reviews' => [
                ['id' => 'r3', 'stars' => 3, 'text' => 'Okay'],
            ],
        ], 200),
    ];

    $mock = new MockClient([
        GetBusinessUnitReviewsRequest::class => function () use (&$responses) {
            return array_shift($responses);
        },
    ]);

    $service->connector()->withMockClient($mock);

    $lazy = $service->listReviewsLazy();

    expect($lazy)->toBeInstanceOf(LazyCollection::class);

    $collected = $lazy->collect();

    expect($collected)->toBeInstanceOf(Collection::class)
        ->and($collected->count())->toBe(3)
        ->and($collected->first())->toBeInstanceOf(Review::class)
        ->and($collected->pluck('id')->all())->toBe(['r1', 'r2', 'r3']);
});

it('sends an invitation email', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        SendInvitationRequest::class => MockResponse::make([
            'status' => 'ok',
            'id' => 'invite_123',
        ], 200),
    ]);

    $service->connector()->withMockClient($mock);

    $resp = $service->sendInvitation(new Invitation(
        recipientEmail: 'jane@example.com',
        recipientName: 'Jane',
        referenceId: 'order-123',
    ));

    expect($resp)->toBeInstanceOf(InvitationResponse::class)
        ->and($resp->status)->toBe('ok')
        ->and($resp->id)->toBe('invite_123');
});

it('handles 204 No Content on invitation gracefully', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        SendInvitationRequest::class => MockResponse::make('', 204, [
            'Content-Type' => 'application/json',
        ]),
    ]);

    $service->connector()->withMockClient($mock);

    $resp = $service->sendInvitation(new Invitation(
        recipientEmail: 'jane@example.com',
        recipientName: 'Jane',
    ));

    expect($resp)->toBeInstanceOf(InvitationResponse::class)
        ->and($resp->status)->toBe('ok');
});

it('throws on error response for invitation', function () {
    $service = app(TrustpilotService::class);

    $mock = new MockClient([
        SendInvitationRequest::class => MockResponse::make('Bad Request', 400, [
            'Content-Type' => 'text/plain',
        ]),
    ]);

    $service->connector()->withMockClient($mock);

    expect(fn () => $service->sendInvitation(new Invitation(
        recipientEmail: 'joe@example.com',
    )))->toThrow(RuntimeException::class);
});
