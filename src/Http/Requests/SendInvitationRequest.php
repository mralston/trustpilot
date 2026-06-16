<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Send Invitation Request
 * Trustpilot expects a JSON body for email invitations.
 */
class SendInvitationRequest extends Request implements \Saloon\Contracts\Body\HasBody
{
    protected Method $method = Method::POST;

    public ?bool $allowBaseUrlOverride = true;

    public function __construct(
        protected string $businessUnitId,
        protected array $payload
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        // Trustpilot Invitations API is hosted on a separate domain from the core API.
        // Use the configurable invitations base URL and return an absolute URL so Saloon
        // will skip the connector base URL.
        $base = rtrim((string) config('trustpilot.invitations_base_url', 'https://invitations-api.trustpilot.com'), '/');
        return $base . "/v1/private/business-units/{$this->businessUnitId}/invitations";
    }

    // Saloon v3: Use JSON body trait and implement HasBody to set Content-Type correctly
    use \Saloon\Traits\Body\HasJsonBody;

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
