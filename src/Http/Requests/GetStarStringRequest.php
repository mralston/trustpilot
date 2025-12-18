<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStarStringRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string|int|float $stars
    ) {}

    public function resolveEndpoint(): string
    {
        // Lives on the core API host
        return "/v1/resources/strings/stars/{$this->stars}";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
