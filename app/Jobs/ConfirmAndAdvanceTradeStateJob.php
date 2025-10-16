<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Trade;
use App\Models\EscrowMovement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConfirmAndAdvanceTradeStateJob implements ShouldQueue
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
    public function handle(): void
    {
        $trade = Trade::find($this->tradeId);
        
        if (!$trade) {
            Log::warning("Trade not found for state advancement", ['trade_id' => $this->tradeId]);
            return;
        }

        if (!$trade->isAwaitingDeposit()) {
            Log::info("Trade is not awaiting deposit, skipping state advancement", ['trade_id' => $this->tradeId]);
            return;
        }

        try {
            DB::transaction(function () use ($trade) {
                // Check if we have sufficient confirmed escrow funds
                $confirmedBalance = EscrowMovement::where('trade_id', $trade->id)
                    ->where('direction', 'in')
                    ->where('confirmations', '>=', config('monero.confirmations', 10))
                    ->sum('amount_atomic');

                if ($confirmedBalance < $trade->amount_atomic) {
                    Log::info("Insufficient confirmed escrow balance", [
                        'trade_id' => $trade->id,
                        'required' => $trade->amount_atomic,
                        'available' => $confirmedBalance,
                    ]);
                    return;
                }

                // Advance trade state to escrowed
                $trade->update(['state' => 'escrowed']);

                // Add trade event
                $trade->addEvent('funds_escrowed', null, [
                    'amount_atomic' => $confirmedBalance,
                    'confirmations' => config('monero.confirmations', 10),
                ]);

                Log::info("Trade state advanced to escrowed", [
                    'trade_id' => $trade->id,
                    'amount' => $confirmedBalance,
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Failed to advance trade state", [
                'trade_id' => $this->tradeId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ConfirmAndAdvanceTradeStateJob failed", [
            'trade_id' => $this->tradeId,
            'error' => $exception->getMessage(),
        ]);
    }
}

