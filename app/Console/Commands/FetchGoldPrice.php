<?php

namespace App\Console\Commands;

use App\Services\GoldPriceService;
use Illuminate\Console\Command;

class FetchGoldPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the latest gold price from the configured provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching gold price...');

        try {
            $goldService = new GoldPriceService();
            $price = $goldService->fetchPrice();

            $this->info('Gold price fetched successfully!');
            $this->info('Bid: ' . $price['bid'] . ' EUR/g');
            $this->info('Ask: ' . $price['ask'] . ' EUR/g');
        } catch (\Exception $e) {
            $this->error('Failed to fetch gold price: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
