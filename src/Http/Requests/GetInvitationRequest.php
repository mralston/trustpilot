<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetInvitationRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $businessUnitId,
        protected string $invitationId
    ) {}

    public function resolveEndpoint(): string
    {
        $base = rtrim((string) config('trustpilot.invitations_base_url', 'https://invitations-api.trustpilot.com'), '/');
        return $base . "/v1/private/business-units/{$this->businessUnitId}/invitations/{$this->invitationId}";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
