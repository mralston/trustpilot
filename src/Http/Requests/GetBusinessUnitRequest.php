<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetBusinessUnitRequest extends Request
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
        return "/v1/business-units/{$this->businessUnitId}";
    }

    protected function defaultQuery(): array
    {
        return $this->queryParams;
    }
}
