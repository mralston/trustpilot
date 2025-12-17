<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

class InvitationResponse
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $id = null,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: isset($data['status']) ? (string)$data['status'] : null,
            id: isset($data['id']) ? (string)$data['id'] : null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'id' => $this->id,
            'raw' => $this->raw,
        ], fn ($v) => $v !== null);
    }
}
