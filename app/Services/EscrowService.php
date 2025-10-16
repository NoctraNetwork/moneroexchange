<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\EscrowMovement;
use App\Models\TradeEvent;
use App\Services\MoneroRpcService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    private MoneroRpcService $moneroService;

    public function __construct(MoneroRpcService $moneroService)
    {
        $this->moneroService = $moneroService;
    }

    /**
     * Create escrow subaddress for trade
     */
    public function createEscrowSubaddress(Trade $trade): ?string
    {
        try {
            // Create subaddress for this trade
            $subaddress = $this->moneroService->createSubaddress($trade->id);
            
            if (!$subaddress || !isset($subaddress['address'])) {
                Log::error('Failed to create escrow subaddress', ['trade_id' => $trade->id]);
                return null;
            }

            // Update trade with escrow subaddress
            $trade->update([
                'escrow_subaddr' => $subaddress['address']
            ]);

            // Log the event
            $trade->addEvent('escrow_subaddress_created', null, [
                'subaddress' => $subaddress['address'],
                'address_index' => $subaddress['address_index']
            ]);

            Log::info('Escrow subaddress created', [
                'trade_id' => $trade->id,
                'subaddress' => $subaddress['address']
            ]);

            return $subaddress['address'];
        } catch (\Exception $e) {
            Log::error('Failed to create escrow subaddress', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check for incoming XMR deposits
     */
    public function checkForDeposits(Trade $trade): bool
    {
        if (!$trade->escrow_subaddr) {
            return false;
        }

        try {
            // Get transfers for the escrow subaddress
            $transfers = $this->moneroService->getTransfers([
                'in' => true,
                'subaddr_indices' => [$trade->id], // Using trade ID as subaddress index
                'pending' => false
            ]);

            if (!$transfers || !isset($transfers['in'])) {
                return false;
            }

            $totalDeposited = 0;
            $newDeposits = [];

            foreach ($transfers['in'] as $transfer) {
                // Check if this transfer is already recorded
                $existingMovement = EscrowMovement::where('trade_id', $trade->id)
                    ->where('tx_hash', $transfer['txid'])
                    ->first();

                if (!$existingMovement) {
                    $amount = $transfer['amount'];
                    $totalDeposited += $amount;

                    // Record the escrow movement
                    EscrowMovement::create([
                        'trade_id' => $trade->id,
                        'direction' => 'in',
                        'amount_atomic' => $amount,
                        'tx_hash' => $transfer['txid'],
                        'confirmations' => $transfer['confirmations'] ?? 0,
                        'subaddr_index' => $trade->id,
                    ]);

                    $newDeposits[] = [
                        'tx_hash' => $transfer['txid'],
                        'amount' => $amount,
                        'confirmations' => $transfer['confirmations'] ?? 0
                    ];
                }
            }

            if ($totalDeposited > 0) {
                // Update trade state if sufficient funds are deposited
                if ($trade->getEscrowBalance() >= $trade->amount_atomic) {
                    $trade->update(['state' => 'escrowed']);
                    $trade->addEvent('escrow_funded', null, [
                        'amount_atomic' => $totalDeposited,
                        'new_deposits' => $newDeposits
                    ]);

                    Log::info('Trade escrow funded', [
                        'trade_id' => $trade->id,
                        'amount_atomic' => $totalDeposited,
                        'balance' => $trade->getEscrowBalance()
                    ]);
                } else {
                    $trade->addEvent('partial_deposit', null, [
                        'amount_atomic' => $totalDeposited,
                        'required_atomic' => $trade->amount_atomic,
                        'balance' => $trade->getEscrowBalance()
                    ]);
                }

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check for deposits', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Release escrow funds to buyer
     */
    public function releaseEscrow(Trade $trade, string $buyerAddress, int $actorId): bool
    {
        if (!$trade->canBeReleased()) {
            Log::warning('Trade cannot be released', [
                'trade_id' => $trade->id,
                'state' => $trade->state
            ]);
            return false;
        }

        if (!$trade->hasEscrowFunds()) {
            Log::warning('Insufficient escrow funds', [
                'trade_id' => $trade->id,
                'required' => $trade->amount_atomic,
                'available' => $trade->getEscrowBalance()
            ]);
            return false;
        }

        try {
            DB::beginTransaction();

            // Calculate fees
            $feeAmount = $this->calculateFee($trade->amount_atomic);
            $releaseAmount = $trade->amount_atomic - $feeAmount;

            // Transfer XMR to buyer
            $transferResult = $this->moneroService->transfer([
                [
                    'address' => $buyerAddress,
                    'amount' => $releaseAmount
                ]
            ]);

            if (!$transferResult || !isset($transferResult['tx_hash'])) {
                throw new \Exception('Failed to transfer XMR');
            }

            // Record escrow movement
            EscrowMovement::create([
                'trade_id' => $trade->id,
                'direction' => 'out',
                'amount_atomic' => $releaseAmount,
                'tx_hash' => $transferResult['tx_hash'],
                'subaddr_index' => $trade->id,
            ]);

            // Record fee if applicable
            if ($feeAmount > 0) {
                EscrowMovement::create([
                    'trade_id' => $trade->id,
                    'direction' => 'fee',
                    'amount_atomic' => $feeAmount,
                    'tx_hash' => $transferResult['tx_hash'],
                    'subaddr_index' => $trade->id,
                ]);
            }

            // Update trade state
            $trade->update([
                'state' => 'completed',
                'buyer_address' => $buyerAddress
            ]);

            // Add event
            $trade->addEvent('escrow_released', $actorId, [
                'buyer_address' => $buyerAddress,
                'amount_atomic' => $releaseAmount,
                'fee_atomic' => $feeAmount,
                'tx_hash' => $transferResult['tx_hash']
            ]);

            DB::commit();

            Log::info('Escrow released successfully', [
                'trade_id' => $trade->id,
                'buyer_address' => $buyerAddress,
                'amount_atomic' => $releaseAmount,
                'tx_hash' => $transferResult['tx_hash']
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to release escrow', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Refund escrow funds to seller
     */
    public function refundEscrow(Trade $trade, int $actorId): bool
    {
        if (!$trade->canBeRefunded()) {
            Log::warning('Trade cannot be refunded', [
                'trade_id' => $trade->id,
                'state' => $trade->state
            ]);
            return false;
        }

        if (!$trade->hasEscrowFunds()) {
            Log::warning('No escrow funds to refund', [
                'trade_id' => $trade->id
            ]);
            return false;
        }

        try {
            DB::beginTransaction();

            $refundAmount = $trade->getEscrowBalance();
            $sellerAddress = $trade->seller->getMoneroAddress(); // Assuming user has XMR address

            if (!$sellerAddress) {
                throw new \Exception('Seller has no Monero address');
            }

            // Transfer XMR back to seller
            $transferResult = $this->moneroService->transfer([
                [
                    'address' => $sellerAddress,
                    'amount' => $refundAmount
                ]
            ]);

            if (!$transferResult || !isset($transferResult['tx_hash'])) {
                throw new \Exception('Failed to transfer XMR');
            }

            // Record escrow movement
            EscrowMovement::create([
                'trade_id' => $trade->id,
                'direction' => 'out',
                'amount_atomic' => $refundAmount,
                'tx_hash' => $transferResult['tx_hash'],
                'subaddr_index' => $trade->id,
            ]);

            // Update trade state
            $trade->update(['state' => 'refunded']);

            // Add event
            $trade->addEvent('escrow_refunded', $actorId, [
                'seller_address' => $sellerAddress,
                'amount_atomic' => $refundAmount,
                'tx_hash' => $transferResult['tx_hash']
            ]);

            DB::commit();

            Log::info('Escrow refunded successfully', [
                'trade_id' => $trade->id,
                'seller_address' => $sellerAddress,
                'amount_atomic' => $refundAmount,
                'tx_hash' => $transferResult['tx_hash']
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to refund escrow', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Calculate trading fee
     */
    private function calculateFee(int $amountAtomic): int
    {
        $feeBps = config('monero.trade_fee_bps', 25); // 0.25%
        return intval($amountAtomic * $feeBps / 10000);
    }

    /**
     * Get escrow balance for trade
     */
    public function getEscrowBalance(Trade $trade): int
    {
        return $trade->getEscrowBalance();
    }

    /**
     * Check if escrow has sufficient funds
     */
    public function hasEscrowFunds(Trade $trade): bool
    {
        return $trade->hasEscrowFunds();
    }

    /**
     * Get escrow status
     */
    public function getEscrowStatus(Trade $trade): array
    {
        $balance = $this->getEscrowBalance($trade);
        $required = $trade->amount_atomic;
        $hasFunds = $this->hasEscrowFunds($trade);

        return [
            'balance_atomic' => $balance,
            'balance_xmr' => $balance / 1e12,
            'required_atomic' => $required,
            'required_xmr' => $required / 1e12,
            'has_sufficient_funds' => $hasFunds,
            'subaddress' => $trade->escrow_subaddr,
            'state' => $trade->state
        ];
    }

    /**
     * Process all pending escrow deposits
     */
    public function processPendingDeposits(): int
    {
        $processed = 0;
        
        // Get all trades awaiting deposit
        $trades = Trade::where('state', 'await_deposit')
            ->whereNotNull('escrow_subaddr')
            ->get();

        foreach ($trades as $trade) {
            if ($this->checkForDeposits($trade)) {
                $processed++;
            }
        }

        Log::info('Processed pending deposits', ['count' => $processed]);
        return $processed;
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance(): array
    {
        return $this->moneroService->getBalance() ?? [
            'balance' => 0,
            'unlocked_balance' => 0
        ];
    }

    /**
     * Get wallet address
     */
    public function getWalletAddress(): ?string
    {
        return $this->moneroService->getAddress();
    }

    /**
     * Check if wallet is synced
     */
    public function isWalletSynced(): bool
    {
        return $this->moneroService->isDaemonSynced();
    }

    /**
     * Get sync status
     */
    public function getSyncStatus(): array
    {
        return $this->moneroService->getSyncStatus();
    }
}
