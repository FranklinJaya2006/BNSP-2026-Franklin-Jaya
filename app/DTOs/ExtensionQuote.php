<?php

namespace App\DTOs;

use Carbon\Carbon;

final readonly class ExtensionQuote
{
    public function __construct(
        public int $extensionNumber,
        public Carbon $previousEnd,
        public Carbon $newEnd,
        public float $fee,
    ) {}
}
