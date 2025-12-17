<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http;

use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Response;
use Saloon\Exceptions\Request\RequestException;

/**
 * Trustpilot Saloon Connector
 * Handles base URL and OAuth Client Credentials for API access.
 */
class TrustpilotConnector extends Connector
{
    protected string $baseUrl;

    protected string $apiKey;
    protected string $apiSecret;

    protected ?string $accessToken = null;
    protected ?int $tokenExpiresAt = null;

    public function __construct(string $apiKey, string $apiSecret, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = rtrim($baseUrl ?: 'https://api.trustpilot.com', '/');
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    public function withAuth(): ?Authenticator
    {
        // We manually set the Authorization header on each request in the sender below
        return null;
    }

    /**
     * Ensure we have a valid OAuth token, refresh if expired.
     * Trustpilot client-credentials token endpoint.
     *
     * @throws RequestException
     */
    public function ensureAccessToken(): string
    {
        $now = time();
        if ($this->accessToken !== null && $this->tokenExpiresAt !== null && $now < ($this->tokenExpiresAt - 30)) {
            return $this->accessToken;
        }

        // If a MockClient is attached (testing), avoid making a real HTTP request.
        if (method_exists($this, 'hasMockClient') && $this->hasMockClient()) {
            $this->accessToken = 'mock-access-token';
            $this->tokenExpiresAt = time() + 3600;
            return $this->accessToken;
        }

        $request = new class($this->apiKey, $this->apiSecret, config('trustpilot.oauth.scopes') ?? null) extends Request implements \Saloon\Contracts\Body\HasBody {
            use \Saloon\Traits\Body\HasFormBody;

            protected Method $method = Method::POST;

            public function __construct(protected string $clientId, protected string $clientSecret, protected $scopes = null)
            {
            }

            public function resolveEndpoint(): string
            {
                return '/v1/oauth/oauth-business-users-for-applications/accesstoken';
            }

            protected function defaultHeaders(): array
            {
                // HasFormBody will ensure correct encoding for x-www-form-urlencoded
                return [
                    'Accept' => 'application/json',
                ];
            }

            protected function defaultBody(): array
            {
                $body = [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ];

                // Optional OAuth scopes support: accept string or array from config
                if (!empty($this->scopes)) {
                    if (is_array($this->scopes)) {
                        $scope = implode(' ', array_filter(array_map('strval', $this->scopes)));
                    } else {
                        $scope = (string) $this->scopes;
                    }
                    if ($scope !== '') {
                        $body['scope'] = $scope;
                    }
                }

                return $body;
            }
        };

        /** @var Response $response */
        $response = $this->send($request);
        $data = $response->json();
        if (!isset($data['access_token'])) {
            $status = $response->status();
            $error = is_array($data) ? ($data['error'] ?? null) : null;
            $desc = is_array($data) ? ($data['error_description'] ?? null) : null;
            $snippet = is_string($response->body()) ? substr($response->body(), 0, 300) : '';
            $message = 'Unable to obtain Trustpilot access token';
            $message .= " (HTTP $status" . ($error ? ", error: $error" : '') . ($desc ? ", description: $desc" : '') . ')';
            if (empty($error) && empty($desc) && $snippet !== '') {
                $message .= ' Response snippet: ' . $snippet;
            }
            throw new \RuntimeException($message);
        }

        $this->accessToken = $data['access_token'];
        $expiresIn = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
        $this->tokenExpiresAt = time() + $expiresIn;

        return $this->accessToken;
    }

    /**
     * Override sender to inject Authorization header.
     */
    public function send(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null): Response
    {
        // Only add Authorization for non-token requests
        $endpoint = $request->resolveEndpoint();
        if (strpos($endpoint, '/accesstoken') === false) {
            $token = $this->ensureAccessToken();
            // In Saloon v3, headers() returns an ArrayStore; mutate via ->add()
            $request->headers()->add('Authorization', 'Bearer ' . $token);
        }

        return parent::send($request, $mockClient, $handleRetry);
    }
}
