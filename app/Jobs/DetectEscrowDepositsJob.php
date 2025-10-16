<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MoneroRpcService;
use App\Models\Trade;
use App\Models\EscrowMovement;
use Illuminate\Support\Facades\Log;

class DetectEscrowDepositsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $tradeId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $tradeId)
    {
        $this->tradeId = $tradeId;
    }

    /**
     * Execute the job.
     */
    public function handle(MoneroRpcService $moneroService): void
    {
        $trade = Trade::find($this->tradeId);
        
        if (!$trade) {
            Log::warning("Trade not found for escrow deposit detection", ['trade_id' => $this->tradeId]);
            return;
        }

        if (!$trade->isAwaitingDeposit()) {
            Log::info("Trade is not awaiting deposit, skipping", ['trade_id' => $this->tradeId]);
            return;
        }

        try {
            // Get transfers for the specific subaddress
            $transfers = $moneroService->getTransfers([
                'in' => true,
                'subaddr_indices' => [0], // Assuming subaddress index 0
                'filter_by_height' => true,
                'min_height' => $trade->created_at->timestamp,
            ]);

            if (!$transfers || !isset($transfers['in'])) {
                Log::info("No incoming transfers found", ['trade_id' => $this->tradeId]);
                return;
            }

            foreach ($transfers['in'] as $transfer) {
                // Check if this transfer is for our specific subaddress
                if ($transfer['address'] !== $trade->escrow_subaddr) {
                    continue;
                }

                // Check if we already processed this transfer
                $existingMovement = EscrowMovement::where('trade_id', $trade->id)
                    ->where('tx_hash', $transfer['txid'])
                    ->first();

                if ($existingMovement) {
                    continue;
                }

                // Create escrow movement record
                $movement = EscrowMovement::create([
                    'trade_id' => $trade->id,
                    'direction' => 'in',
                    'amount_atomic' => $transfer['amount'],
                    'tx_hash' => $transfer['txid'],
                    'height' => $transfer['height'],
                    'confirmations' => $transfer['confirmations'] ?? 0,
                ]);

                Log::info("Escrow deposit detected", [
                    'trade_id' => $trade->id,
                    'amount' => $transfer['amount'],
                    'tx_hash' => $transfer['txid'],
                ]);

                // Check if we have enough confirmations
                $requiredConfirmations = config('monero.confirmations', 10);
                if ($movement->confirmations >= $requiredConfirmations) {
                    // Dispatch job to advance trade state
                    ConfirmAndAdvanceTradeStateJob::dispatch($trade->id);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to detect escrow deposits", [
                'trade_id' => $this->tradeId,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("DetectEscrowDepositsJob failed", [
            'trade_id' => $this->tradeId,
            'error' => $exception->getMessage(),
        ]);
    }
}

