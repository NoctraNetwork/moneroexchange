<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MoneroRpcService;
use App\Models\Setting;

class CheckMoneroHealth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'xmr:health';

    /**
     * The console command description.
     */
    protected $description = 'Check Monero daemon and wallet health status';

    /**
     * Execute the console command.
     */
    public function handle(MoneroRpcService $moneroService): int
    {
        $this->info('Checking Monero health...');

        // Check daemon connection
        $daemonHeight = $moneroService->getDaemonHeight();
        if (!$daemonHeight) {
            $this->error('❌ Cannot connect to Monero daemon');
            return 1;
        }
        $this->info("✅ Daemon connected (height: {$daemonHeight})");

        // Check wallet connection
        $walletHeight = $moneroService->getWalletHeight();
        if (!$walletHeight) {
            $this->error('❌ Cannot connect to Monero wallet RPC');
            return 1;
        }
        $this->info("✅ Wallet RPC connected (height: {$walletHeight})");

        // Check sync status
        $isSynced = $moneroService->isDaemonSynced();
        if ($isSynced) {
            $this->info('✅ Daemon and wallet are synced');
        } else {
            $blocksBehind = $daemonHeight - $walletHeight;
            $this->warn("⚠️  Wallet is {$blocksBehind} blocks behind daemon");
        }

        // Check wallet balance
        $balance = $moneroService->getBalance();
        if ($balance) {
            $balanceXmr = $balance['balance'] / 1e12;
            $unlockedXmr = $balance['unlocked_balance'] / 1e12;
            $this->info("💰 Wallet balance: {$balanceXmr} XMR (unlocked: {$unlockedXmr} XMR)");
        } else {
            $this->error('❌ Cannot retrieve wallet balance');
        }

        // Check wallet address
        $address = $moneroService->getAddress();
        if ($address) {
            $this->info("📍 Wallet address: {$address}");
        } else {
            $this->error('❌ Cannot retrieve wallet address');
        }

        // Store health status
        Setting::set('monero_last_health_check', now()->toISOString());
        Setting::set('monero_daemon_height', $daemonHeight);
        Setting::set('monero_wallet_height', $walletHeight);
        Setting::set('monero_is_synced', $isSynced);

        $this->info('Health check completed successfully');

        return 0;
    }
}

