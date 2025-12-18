<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Facades;

use Illuminate\Support\Facades\Facade;
use Mralston\Trustpilot\Services\TrustpilotService;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Mralston\Trustpilot\DTOs\BusinessUnit;
use Mralston\Trustpilot\DTOs\BusinessUnitStats;
use Mralston\Trustpilot\DTOs\Invitation;
use Mralston\Trustpilot\DTOs\InvitationResponse;
use Mralston\Trustpilot\DTOs\Review;

/**
 * @method static BusinessUnit getBusinessUnit(?string $businessUnitId = null, array $query = [])
 * @method static BusinessUnitStats getBusinessUnitStats(?string $businessUnitId = null, ?array $fields = ['score', 'numberOfReviews'])
 * @method static Collection listReviews(?string $businessUnitId = null, array $query = [])
 * @method static LazyCollection listReviewsLazy(?string $businessUnitId = null, array $query = [])
 * @method static Review getReview(string $reviewId)
 * @method static InvitationResponse sendInvitation(Invitation $payload, ?string $businessUnitId = null)
 * @method static \Mralston\Trustpilot\DTOs\InvitationStatus getInvitation(string $invitationId, ?string $businessUnitId = null)
 * @method static null|string getStarString(float|int|string $stars)
 */
class Trustpilot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TrustpilotService::class;
    }
}
