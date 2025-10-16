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
use Illuminate\Support\Facades\DB;

class BroadcastReleaseOrRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $tradeId;
    private string $action; // 'release' or 'refund'
    private string $destinationAddress;
    private ?int $actorId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $tradeId, string $action, string $destinationAddress, ?int $actorId = null)
    {
        $this->tradeId = $tradeId;
        $this->action = $action;
        $this->destinationAddress = $destinationAddress;
        $this->actorId = $actorId;
    }

    /**
     * Execute the job.
     */
    public function handle(MoneroRpcService $moneroService): void
    {
        $trade = Trade::find($this->tradeId);
        
        if (!$trade) {
            Log::warning("Trade not found for release/refund", ['trade_id' => $this->tradeId]);
            return;
        }

        if (!in_array($this->action, ['release', 'refund'])) {
            Log::error("Invalid action for release/refund job", [
                'trade_id' => $this->tradeId,
                'action' => $this->action,
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($trade, $moneroService) {
                // Check if trade can be released/refunded
                if (!$trade->canBeReleased() && !$trade->canBeRefunded()) {
                    Log::warning("Trade cannot be released/refunded", [
                        'trade_id' => $trade->id,
                        'state' => $trade->state,
                        'action' => $this->action,
                    ]);
                    return;
                }

                // Get available escrow balance
                $escrowBalance = $trade->getEscrowBalance();
                
                if ($escrowBalance <= 0) {
                    Log::warning("No escrow balance available", [
                        'trade_id' => $trade->id,
                        'balance' => $escrowBalance,
                    ]);
                    return;
                }

                // Calculate fee
                $feeBps = config('monero.trade_fee_bps', 25);
                $fee = (int) round($escrowBalance * ($feeBps / 10000));
                $amountToSend = $escrowBalance - $fee;

                if ($amountToSend <= 0) {
                    Log::warning("Amount to send would be zero or negative after fees", [
                        'trade_id' => $trade->id,
                        'balance' => $escrowBalance,
                        'fee' => $fee,
                    ]);
                    return;
                }

                // Create transaction
                $destinations = [
                    [
                        'address' => $this->destinationAddress,
                        'amount' => $amountToSend,
                    ]
                ];

                $result = $moneroService->transfer($destinations);

                if (!$result || !isset($result['tx_hash'])) {
                    Log::error("Failed to create release/refund transaction", [
                        'trade_id' => $trade->id,
                        'action' => $this->action,
                    ]);
                    return;
                }

                // Record the outgoing movement
                EscrowMovement::create([
                    'trade_id' => $trade->id,
                    'direction' => 'out',
                    'amount_atomic' => $amountToSend,
                    'tx_hash' => $result['tx_hash'],
                    'height' => null, // Will be updated when confirmed
                    'confirmations' => 0,
                ]);

                // Record the fee
                if ($fee > 0) {
                    EscrowMovement::create([
                        'trade_id' => $trade->id,
                        'direction' => 'fee',
                        'amount_atomic' => $fee,
                        'tx_hash' => $result['tx_hash'],
                        'height' => null,
                        'confirmations' => 0,
                    ]);
                }

                // Update trade state
                $newState = $this->action === 'release' ? 'completed' : 'refunded';
                $trade->update(['state' => $newState]);

                // Add trade event
                $trade->addEvent($this->action . '_initiated', $this->actorId, [
                    'tx_hash' => $result['tx_hash'],
                    'amount_atomic' => $amountToSend,
                    'fee_atomic' => $fee,
                    'destination_address' => $this->destinationAddress,
                ]);

                Log::info("Release/refund transaction created", [
                    'trade_id' => $trade->id,
                    'action' => $this->action,
                    'tx_hash' => $result['tx_hash'],
                    'amount' => $amountToSend,
                    'fee' => $fee,
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Failed to broadcast release/refund", [
                'trade_id' => $this->tradeId,
                'action' => $this->action,
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
        Log::error("BroadcastReleaseOrRefundJob failed", [
            'trade_id' => $this->tradeId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}

