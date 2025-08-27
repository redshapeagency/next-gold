<?php

namespace App\Console\Commands;

use App\Services\GoldPrice\GoldPriceService;
use Illuminate\Console\Command;

class FetchGoldPriceCommand extends Command
{
    protected $signature = 'gold:fetch';
    protected $description = 'Fetch latest gold price from configured provider';

    public function handle(GoldPriceService $goldPriceService): int
    {
        $this->info('Fetching gold price...');
        
        try {
            $quote = $goldPriceService->fetchLatestPrice();
            
            if ($quote) {
                $this->info("Gold price fetched successfully:");
                $this->info("Provider: {$quote->provider}");
                $this->info("Bid: {$quote->formatted_bid}");
                $this->info("Ask: {$quote->formatted_ask}");
                $this->info("Fetched at: {$quote->fetched_at}");
                
                return Command::SUCCESS;
            } else {
                $this->error('Failed to fetch gold price');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error fetching gold price: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
