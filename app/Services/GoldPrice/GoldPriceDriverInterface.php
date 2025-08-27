<?php

namespace App\Services\GoldPrice;

interface GoldPriceDriverInterface
{
    /**
     * Fetch the current gold price
     *
     * @return array{bid: float, ask: float}
     */
    public function fetchPrice(): array;
}
