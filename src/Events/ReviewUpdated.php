<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Events;

class ReviewUpdated
{
    public function __construct(
        public array $payload
    ) {
        //
    }
}
