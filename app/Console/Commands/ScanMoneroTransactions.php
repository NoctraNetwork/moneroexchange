<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MoneroRpcService;
use App\Jobs\DetectEscrowDepositsJob;
use App\Models\Trade;
use Illuminate\Support\Facades\Log;

class ScanMoneroTransactions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'xmr:scan {--force : Force scan even if daemon is not synced}';

    /**
     * The console command description.
     */
    protected $description = 'Scan for new Monero transactions and process escrow deposits';

    /**
     * Execute the console command.
     */
    public function handle(MoneroRpcService $moneroService): int
    {
        $this->info('Starting Monero transaction scan...');

        // Check if daemon is synced
        if (!$moneroService->isDaemonSynced() && !$this->option('force')) {
            $this->error('Monero daemon is not synced. Use --force to scan anyway.');
            return 1;
        }

        // Get sync status
        $syncStatus = $moneroService->getSyncStatus();
        $this->info("Daemon height: {$syncStatus['daemon_height']}");
        $this->info("Wallet height: {$syncStatus['wallet_height']}");
        $this->info("Blocks behind: {$syncStatus['blocks_behind']}");

        // Get trades awaiting deposit
        $trades = Trade::where('state', 'await_deposit')
            ->whereNotNull('escrow_subaddr')
            ->get();

        $this->info("Found {$trades->count()} trades awaiting deposit");

        $processed = 0;
        $errors = 0;

        foreach ($trades as $trade) {
            try {
                DetectEscrowDepositsJob::dispatch($trade->id);
                $processed++;
                $this->line("Queued scan for trade {$trade->id}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("Failed to queue scan for trade {$trade->id}: {$e->getMessage()}");
                Log::error("Failed to queue escrow deposit detection", [
                    'trade_id' => $trade->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Scan completed. Processed: {$processed}, Errors: {$errors}");

        return 0;
    }
}

