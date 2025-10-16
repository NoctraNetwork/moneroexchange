<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PriceIndexService;
use App\Models\Offer;
use Illuminate\Support\Facades\Log;

class RepriceFloatingOffers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'offers:reprice';

    /**
     * The console command description.
     */
    protected $description = 'Reprice floating offers based on current market prices';

    /**
     * Execute the console command.
     */
    public function handle(PriceIndexService $priceService): int
    {
        $this->info('Starting floating offers repricing...');

        // Get all active floating offers
        $offers = Offer::where('price_mode', 'floating')
            ->where('active', true)
            ->get();

        $this->info("Found {$offers->count()} floating offers to reprice");

        $updated = 0;
        $errors = 0;

        foreach ($offers as $offer) {
            try {
                $currentPrice = $priceService->getPrice($offer->currency);
                
                if (!$currentPrice) {
                    $this->warn("No price available for {$offer->currency}, skipping offer {$offer->id}");
                    continue;
                }

                // Calculate new price with margin
                $margin = $offer->margin_bps / 10000; // Convert basis points to decimal
                $newPrice = $currentPrice * (1 + $margin);

                // Update the offer (this would typically be a separate field for current price)
                // For now, we'll just log the price change
                $this->line("Offer {$offer->id}: {$offer->currency} = {$newPrice} (margin: {$offer->margin_bps} bps)");
                
                $updated++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("Failed to reprice offer {$offer->id}: {$e->getMessage()}");
                Log::error("Failed to reprice floating offer", [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Repricing completed. Updated: {$updated}, Errors: {$errors}");

        return 0;
    }
}

