<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetReviewRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $reviewId
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return "/v1/reviews/{$this->reviewId}";
    }
}
