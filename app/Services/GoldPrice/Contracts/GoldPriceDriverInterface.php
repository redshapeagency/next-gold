<?php

namespace App\Services\GoldPrice\Contracts;

interface GoldPriceDriverInterface
{
    /**
     * Fetch gold price data from the provider
     *
     * @return array|null
     */
    public function fetch(): ?array;
}
