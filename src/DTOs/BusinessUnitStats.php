<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\DTOs;

readonly class BusinessUnitStats
{
    public function __construct(
        public ?float $stars = null,
        public ?float $trustScore = null,
        public ?int $totalReviews = null,
        public ?int $reviewsUsedForTrustScoreCalculation = null,
        public ?int $oneStarReviews = null,
        public ?int $twoStarReviews = null,
        public ?int $threeStarReviews = null,
        public ?int $fourStarReviews = null,
        public ?int $fiveStarReviews = null,
        public array $raw = [],
    ) {}

    /**
     * Hydrate from the Business Unit payload returned by Trustpilot.
     */
    public static function fromArray(array $data): self
    {
        $score = is_array($data['score'] ?? null) ? $data['score'] : [];
        $reviews = is_array($data['numberOfReviews'] ?? null) ? $data['numberOfReviews'] : [];

        return new self(
            stars: isset($score['stars']) ? (float)$score['stars'] : (isset($data['stars']) ? (float)$data['stars'] : null),
            trustScore: isset($score['trustScore']) ? (float)$score['trustScore'] : null,
            totalReviews: isset($reviews['total']) ? (int)$reviews['total'] : (isset($data['numberOfReviews']) && !is_array($data['numberOfReviews']) ? (int)$data['numberOfReviews'] : null),
            reviewsUsedForTrustScoreCalculation: isset($reviews['usedForTrustScoreCalculation']) ? (int)$reviews['usedForTrustScoreCalculation'] : null,
            oneStarReviews: isset($reviews['oneStar']) ? (int)$reviews['oneStar'] : null,
            twoStarReviews: isset($reviews['twoStars']) ? (int)$reviews['twoStars'] : null,
            threeStarReviews: isset($reviews['threeStars']) ? (int)$reviews['threeStars'] : null,
            fourStarReviews: isset($reviews['fourStars']) ? (int)$reviews['fourStars'] : null,
            fiveStarReviews: isset($reviews['fiveStars']) ? (int)$reviews['fiveStars'] : null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'stars' => $this->stars,
            'trustScore' => $this->trustScore,
            'totalReviews' => $this->totalReviews,
            'reviewsUsedForTrustScoreCalculation' => $this->reviewsUsedForTrustScoreCalculation,
            'oneStarReviews' => $this->oneStarReviews,
            'twoStarReviews' => $this->twoStarReviews,
            'threeStarReviews' => $this->threeStarReviews,
            'fourStarReviews' => $this->fourStarReviews,
            'fiveStarReviews' => $this->fiveStarReviews,
//            'raw' => $this->raw,
        ], fn ($v) => $v !== null);
    }
}
