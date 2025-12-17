<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

class BusinessUnit
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $displayName = null,
        public readonly ?float $stars = null,
        public readonly ?int $numberOfReviews = null,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        // Extract stars: prefer nested score.stars, fallback to top-level stars for backward compatibility.
        $stars = null;
        if (isset($data['score']) && is_array($data['score']) && array_key_exists('stars', $data['score'])) {
            $stars = $data['score']['stars'];
        } elseif (array_key_exists('stars', $data)) {
            $stars = $data['stars'];
        }

        $stars = isset($stars) ? (float) $stars : null;

        // Extract numberOfReviews: prefer nested numberOfReviews.total, fallback to top-level integer value.
        $numberOfReviews = null;
        if (isset($data['numberOfReviews']) && is_array($data['numberOfReviews']) && array_key_exists('total', $data['numberOfReviews'])) {
            $numberOfReviews = $data['numberOfReviews']['total'];
        } elseif (array_key_exists('numberOfReviews', $data) && !is_array($data['numberOfReviews'])) {
            $numberOfReviews = $data['numberOfReviews'];
        }

        $numberOfReviews = isset($numberOfReviews) ? (int) $numberOfReviews : null;

        return new self(
            id: (string)($data['id'] ?? ''),
            displayName: isset($data['displayName']) ? (string)$data['displayName'] : null,
            stars: $stars,
            numberOfReviews: $numberOfReviews,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'displayName' => $this->displayName,
            'stars' => $this->stars,
            'numberOfReviews' => $this->numberOfReviews,
            'raw' => $this->raw,
        ], fn ($v) => $v !== null);
    }
}
