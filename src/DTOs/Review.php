<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

readonly class Review
{
    public function __construct(
        public string $id,
        public ?int $stars = null,
        public ?string $text = null,
        public ?string $consumerName = null,
        public ?string $createdAt = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)($data['id'] ?? ''),
            stars: isset($data['stars']) ? (int)$data['stars'] : null,
            text: isset($data['text']) ? (string)$data['text'] : null,
            consumerName: isset($data['consumer']['displayName']) ? (string)$data['consumer']['displayName'] : (isset($data['consumerName']) ? (string)$data['consumerName'] : null),
            createdAt: isset($data['createdAt']) ? (string)$data['createdAt'] : null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'stars' => $this->stars,
            'text' => $this->text,
            'consumerName' => $this->consumerName,
            'createdAt' => $this->createdAt,
            'raw' => $this->raw,
        ], fn ($v) => $v !== null);
    }
}
