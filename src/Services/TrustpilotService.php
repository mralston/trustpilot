<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Mralston\Trustpilot\DTOs\BusinessUnit;
use Mralston\Trustpilot\DTOs\Invitation;
use Mralston\Trustpilot\DTOs\InvitationResponse;
use Mralston\Trustpilot\DTOs\InvitationStatus;
use Mralston\Trustpilot\DTOs\BusinessUnitStats;
use Mralston\Trustpilot\DTOs\Review;
use Mralston\Trustpilot\Http\TrustpilotConnector;
use Mralston\Trustpilot\Http\Requests\GetBusinessUnitRequest;
use Mralston\Trustpilot\Http\Requests\GetBusinessUnitReviewsRequest;
use Mralston\Trustpilot\Http\Requests\GetReviewRequest;
use Mralston\Trustpilot\Http\Requests\SendInvitationRequest;
use Mralston\Trustpilot\Http\Requests\GetInvitationRequest;

class TrustpilotService
{
    protected TrustpilotConnector $connector;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        protected ?string $businessUnitId = null,
        ?string $baseUrl = null
    ) {
        $this->connector = new TrustpilotConnector($apiKey, $apiSecret, $baseUrl);
    }

    public function getBusinessUnit(?string $businessUnitId = null, array $query = []): BusinessUnit
    {
        $id = $businessUnitId ?? $this->requireBusinessUnit();
        $request = new GetBusinessUnitRequest($id, $query);
        $data = $this->connector->send($request)->json();
        return BusinessUnit::fromArray($data ?? []);
    }

    public function getBusinessUnitStats(?string $businessUnitId = null, ?array $fields = ['score', 'numberOfReviews']): BusinessUnitStats
    {
        // Many stats are on the business unit object; allow selecting fields.
        $query = [];
        if (!empty($fields)) {
            $query['fields'] = implode(',', $fields);
        }
        $id = $businessUnitId ?? $this->requireBusinessUnit();
        $request = new GetBusinessUnitRequest($id, $query);
        $data = $this->connector->send($request)->json();
        return BusinessUnitStats::fromArray($data ?? []);
    }

    public function listReviews(?string $businessUnitId = null, array $query = []): Collection
    {
        $id = $businessUnitId ?? $this->requireBusinessUnit();
        $request = new GetBusinessUnitReviewsRequest($id, $query);
        $payload = $this->connector->send($request)->json();
        $reviews = is_array($payload) && isset($payload['reviews']) && is_array($payload['reviews'])
            ? $payload['reviews']
            : (is_array($payload) ? $payload : []);

        return collect($reviews)
            ->map(fn ($review) => Review::fromArray($review));
    }

    /**
     * Stream all reviews across pages using Trustpilot's pageToken pagination.
     */
    public function listReviewsLazy(?string $businessUnitId = null, array $query = []): LazyCollection
    {
        $id = $businessUnitId ?? $this->requireBusinessUnit();

        return LazyCollection::make(function () use ($id, $query) {
            $nextToken = $query['pageToken'] ?? null;

            do {
                $pageQuery = $query;
                if ($nextToken) {
                    $pageQuery['pageToken'] = $nextToken;
                } else {
                    unset($pageQuery['pageToken']);
                }

                $request = new GetBusinessUnitReviewsRequest($id, $pageQuery);
                $payload = $this->connector->send($request)->json();

                $items = is_array($payload) && isset($payload['reviews']) && is_array($payload['reviews'])
                    ? $payload['reviews']
                    : (is_array($payload) ? $payload : []);

                foreach ($items as $item) {
                    yield Review::fromArray($item);
                }

                $nextToken = is_array($payload) && isset($payload['nextPageToken']) && is_string($payload['nextPageToken'])
                    ? $payload['nextPageToken']
                    : null;
            } while ($nextToken !== null && $nextToken !== '');
        });
    }

    public function getReview(string $reviewId): Review
    {
        $request = new GetReviewRequest($reviewId);
        $data = $this->connector->send($request)->json();
        return Review::fromArray($data ?? []);
    }

    public function sendInvitation(Invitation $invitation, ?string $businessUnitId = null): InvitationResponse
    {
        $id = $businessUnitId ?? $this->requireBusinessUnit();
        $request = new SendInvitationRequest($id, $invitation->toArray());
        $response = $this->connector->send($request);

        // Throw detailed exception on error responses
        if ($response->failed()) {
            $status = $response->status();
            $snippet = is_string($response->body()) ? substr($response->body(), 0, 300) : '';
            $message = 'Trustpilot invitation request failed (HTTP ' . $status . ')';
            if ($snippet !== '') {
                $message .= ' Response snippet: ' . $snippet;
            }
            throw new \RuntimeException($message);
        }

        // Handle empty or non-JSON bodies gracefully (e.g., 204 No Content)
        $body = (string) $response->body();
        if (trim($body) === '') {
            return new InvitationResponse(status: 'ok', id: null, raw: []);
        }

        $contentType = (string) ($response->header('Content-Type') ?? '');
        $isJson = str_contains(strtolower($contentType), 'application/json');

        if ($isJson) {
            $data = $response->json();
            return InvitationResponse::fromArray($data ?? []);
        }

        // Try to decode JSON even without header; if it fails, return a minimal response
        try {
            $data = $response->json();
            return InvitationResponse::fromArray($data ?? []);
        } catch (\JsonException) {
            return new InvitationResponse(status: 'ok', id: null, raw: []);
        }
    }

    public function connector(): TrustpilotConnector
    {
        return $this->connector;
    }

    public function getInvitation(string $invitationId, ?string $businessUnitId = null): InvitationStatus
    {
        $id = $businessUnitId ?? $this->requireBusinessUnit();
        $request = new GetInvitationRequest($id, $invitationId);
        $response = $this->connector->send($request);

        if ($response->failed()) {
            $status = $response->status();
            $snippet = is_string($response->body()) ? substr($response->body(), 0, 300) : '';
            $message = 'Trustpilot get invitation failed (HTTP ' . $status . ')';
            if ($snippet !== '') {
                $message .= ' Response snippet: ' . $snippet;
            }
            throw new \RuntimeException($message);
        }

        // Assume JSON on success; fallback like sendInvitation
        $body = (string)$response->body();
        if (trim($body) === '') {
            return new InvitationStatus(raw: []);
        }

        try {
            $data = $response->json();
            return InvitationStatus::fromArray($data ?? []);
        } catch (\JsonException) {
            return new InvitationStatus(raw: ['body' => $body]);
        }
    }

    protected function requireBusinessUnit(): string
    {
        if (!$this->businessUnitId) {
            throw new \InvalidArgumentException('Business Unit ID is required. Provide it to the service or the method call.');
        }
        return $this->businessUnitId;
    }
}
