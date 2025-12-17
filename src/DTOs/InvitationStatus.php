<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

readonly class InvitationStatus
{
    public function __construct(
        public ?string $id = null,
        public ?string $status = null,
        public ?string $recipientEmail = null,
        public ?string $recipientName = null,
        public ?string $referenceId = null,
        public ?string $createdTime = null,
        public ?string $sentTime = null,
        public ?string $preferredSendTime = null,
        public ?string $source = null,
        public ?string $invitationType = null,
        public ?bool $includesProductReviewInvitations = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $recipient = is_array($data['recipient'] ?? null) ? $data['recipient'] : [];

        return new self(
            id: isset($data['id']) ? (string)$data['id'] : null,
            status: isset($data['status']) ? (string)$data['status'] : null,
            recipientEmail: isset($recipient['email']) ? (string)$recipient['email'] : (isset($data['recipientEmail']) ? (string)$data['recipientEmail'] : null),
            recipientName: isset($recipient['name']) ? (string)$recipient['name'] : (isset($data['recipientName']) ? (string)$data['recipientName'] : null),
            referenceId: isset($data['referenceId']) ? (string)$data['referenceId'] : null,
            createdTime: isset($data['createdTime']) ? (string)$data['createdTime'] : null,
            sentTime: isset($data['sentTime']) ? (string)$data['sentTime'] : null,
            preferredSendTime: isset($data['preferredSendTime']) ? (string)$data['preferredSendTime'] : null,
            source: isset($data['source']) ? (string)$data['source'] : null,
            invitationType: isset($data['invitationType']) ? (string)$data['invitationType'] : null,
            includesProductReviewInvitations: isset($data['includesProductReviewInvitations']) ? (bool)$data['includesProductReviewInvitations'] : null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'status' => $this->status,
            'recipientEmail' => $this->recipientEmail,
            'recipientName' => $this->recipientName,
            'referenceId' => $this->referenceId,
            'createdTime' => $this->createdTime,
            'sentTime' => $this->sentTime,
            'preferredSendTime' => $this->preferredSendTime,
            'source' => $this->source,
            'invitationType' => $this->invitationType,
            'includesProductReviewInvitations' => $this->includesProductReviewInvitations,
            'raw' => $this->raw,
        ], fn($v) => $v !== null);
    }
}
