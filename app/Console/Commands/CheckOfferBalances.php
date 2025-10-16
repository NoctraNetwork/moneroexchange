<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WalletBalanceService;
use App\Models\Offer;

class CheckOfferBalances extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'offers:check-balances {--disable-insufficient : Auto-disable offers with insufficient balance}';

    /**
     * The console command description.
     */
    protected $description = 'Check all sell offers for sufficient wallet balance';

    protected $walletBalanceService;

    public function __construct(WalletBalanceService $walletBalanceService)
    {
        parent::__construct();
        $this->walletBalanceService = $walletBalanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking sell offers for sufficient wallet balance...');

        $sellOffers = Offer::where('side', 'sell')
            ->where('active', true)
            ->with('user')
            ->get();

        $insufficientOffers = [];
        $disabledCount = 0;

        $progressBar = $this->output->createProgressBar($sellOffers->count());
        $progressBar->start();

        foreach ($sellOffers as $offer) {
            $validation = $this->walletBalanceService->validateOfferAmount(
                $offer->user, 
                $offer->getAmountXmr(), 
                'sell'
            );

            if (!$validation['valid']) {
                $insufficientOffers[] = [
                    'offer' => $offer,
                    'validation' => $validation
                ];

                if ($this->option('disable-insufficient')) {
                    $offer->update(['active' => false]);
                    $disabledCount++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Display results
        if (empty($insufficientOffers)) {
            $this->info('✅ All sell offers have sufficient balance!');
        } else {
            $this->warn("⚠️  Found " . count($insufficientOffers) . " offers with insufficient balance:");
            $this->newLine();

            $headers = ['Offer ID', 'User', 'Amount (XMR)', 'Available Balance', 'Status'];
            $rows = [];

            foreach ($insufficientOffers as $item) {
                $offer = $item['offer'];
                $validation = $item['validation'];
                
                $rows[] = [
                    $offer->id,
                    $offer->user->username,
                    number_format($offer->getAmountXmr(), 4),
                    number_format($validation['available_balance'] ?? 0, 4),
                    $this->option('disable-insufficient') ? 'DISABLED' : 'ACTIVE'
                ];
            }

            $this->table($headers, $rows);

            if ($this->option('disable-insufficient')) {
                $this->info("✅ Disabled {$disabledCount} offers with insufficient balance.");
            } else {
                $this->info("💡 Run with --disable-insufficient to auto-disable these offers.");
            }
        }

        // Show summary statistics
        $this->newLine();
        $this->info('Summary:');
        $this->line("- Total sell offers checked: " . $sellOffers->count());
        $this->line("- Offers with sufficient balance: " . ($sellOffers->count() - count($insufficientOffers)));
        $this->line("- Offers with insufficient balance: " . count($insufficientOffers));
        
        if ($this->option('disable-insufficient')) {
            $this->line("- Offers disabled: " . $disabledCount);
        }

        return Command::SUCCESS;
    }
}
