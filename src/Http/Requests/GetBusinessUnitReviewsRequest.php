<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetBusinessUnitReviewsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $businessUnitId,
        private array $queryParams = []
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return "/v1/business-units/{$this->businessUnitId}/reviews";
    }

    protected function defaultQuery(): array
    {
        return $this->queryParams;
    }
}
