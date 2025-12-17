<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

class Invitation
{
    public function __construct(
        public readonly string $recipientEmail,
        public readonly ?string $recipientName = null,
        public readonly ?string $referenceId = null,
        public readonly ?string $locale = null,
        public readonly ?string $redirectUri = null,
        public readonly array $extra = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            recipientEmail: (string)($data['recipientEmail'] ?? ''),
            recipientName: isset($data['recipientName']) ? (string)$data['recipientName'] : null,
            referenceId: isset($data['referenceId']) ? (string)$data['referenceId'] : null,
            locale: isset($data['locale']) ? (string)$data['locale'] : null,
            redirectUri: isset($data['redirectUri']) ? (string)$data['redirectUri'] : null,
            extra: array_diff_key($data, array_flip(['recipientEmail', 'recipientName', 'referenceId', 'locale', 'redirectUri'])),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'recipientEmail' => $this->recipientEmail,
            'recipientName' => $this->recipientName,
            'referenceId' => $this->referenceId,
            'locale' => $this->locale,
            'redirectUri' => $this->redirectUri,
        ] + $this->extra, fn ($v) => $v !== null);
    }
}
