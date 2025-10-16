<?php

namespace App\Services;

use App\Models\User;
use App\Models\Offer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WalletBalanceService
{
    protected $moneroRpc;

    public function __construct(MoneroRpcService $moneroRpc)
    {
        $this->moneroRpc = $moneroRpc;
    }

    /**
     * Get user's wallet balance in XMR
     */
    public function getUserBalance(User $user): float
    {
        try {
            // Check if user has a wallet address
            if (!$user->wallet_address) {
                return 0.0;
            }

            // Get balance from Monero RPC
            $balance = $this->moneroRpc->getBalance($user->wallet_address);
            
            if ($balance === false) {
                Log::error('Failed to get balance for user', [
                    'user_id' => $user->id,
                    'wallet_address' => $user->wallet_address
                ]);
                return 0.0;
            }

            return $balance;
        } catch (\Exception $e) {
            Log::error('Exception getting user balance', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Get user's available balance (excluding locked funds)
     */
    public function getAvailableBalance(User $user): float
    {
        $totalBalance = $this->getUserBalance($user);
        $lockedBalance = $this->getLockedBalance($user);
        
        return max(0, $totalBalance - $lockedBalance);
    }

    /**
     * Get user's locked balance (funds in active trades)
     */
    public function getLockedBalance(User $user): float
    {
        $lockedAmount = 0;

        // Get all active trades where user is the seller
        $activeTrades = $user->sellerTrades()
            ->whereIn('state', ['pending', 'escrowed', 'disputed'])
            ->get();

        foreach ($activeTrades as $trade) {
            $lockedAmount += $trade->getAmountXmr();
        }

        return $lockedAmount;
    }

    /**
     * Check if user has sufficient balance for an offer
     */
    public function hasSufficientBalance(User $user, float $requiredAmount): bool
    {
        $availableBalance = $this->getAvailableBalance($user);
        return $availableBalance >= $requiredAmount;
    }

    /**
     * Validate offer amount against user's available balance
     */
    public function validateOfferAmount(User $user, float $amount, string $offerType): array
    {
        $availableBalance = $this->getAvailableBalance($user);
        
        // For sell offers, user must have the XMR they want to sell
        if ($offerType === 'sell') {
            if ($amount > $availableBalance) {
                return [
                    'valid' => false,
                    'error' => 'Insufficient XMR balance. You can only sell what you have in your wallet.',
                    'available_balance' => $availableBalance,
                    'required_amount' => $amount
                ];
            }
        }

        // For buy offers, we don't need to check XMR balance as they're buying with fiat
        if ($offerType === 'buy') {
            return [
                'valid' => true,
                'available_balance' => $availableBalance
            ];
        }

        return [
            'valid' => true,
            'available_balance' => $availableBalance
        ];
    }

    /**
     * Get cached balance for performance
     */
    public function getCachedBalance(User $user): float
    {
        $cacheKey = "user_balance_{$user->id}";
        
        return Cache::remember($cacheKey, 60, function () use ($user) {
            return $this->getUserBalance($user);
        });
    }

    /**
     * Clear user's balance cache
     */
    public function clearBalanceCache(User $user): void
    {
        $cacheKey = "user_balance_{$user->id}";
        Cache::forget($cacheKey);
    }

    /**
     * Get balance statistics for user
     */
    public function getBalanceStats(User $user): array
    {
        $totalBalance = $this->getCachedBalance($user);
        $lockedBalance = $this->getLockedBalance($user);
        $availableBalance = max(0, $totalBalance - $lockedBalance);

        return [
            'total_balance' => $totalBalance,
            'available_balance' => $availableBalance,
            'locked_balance' => $lockedBalance,
            'wallet_address' => $user->wallet_address,
            'last_updated' => now()
        ];
    }

    /**
     * Validate trade amount against seller's balance
     */
    public function validateTradeAmount(User $seller, float $amount): array
    {
        $availableBalance = $this->getAvailableBalance($seller);
        
        if ($amount > $availableBalance) {
            return [
                'valid' => false,
                'error' => 'Seller has insufficient XMR balance for this trade.',
                'seller_available_balance' => $availableBalance,
                'required_amount' => $amount
            ];
        }

        return [
            'valid' => true,
            'seller_available_balance' => $availableBalance
        ];
    }

    /**
     * Check if user can create a sell offer
     */
    public function canCreateSellOffer(User $user, float $amount): bool
    {
        return $this->hasSufficientBalance($user, $amount);
    }

    /**
     * Get all users with insufficient balance for their offers
     */
    public function getUsersWithInsufficientBalance(): array
    {
        $users = User::whereHas('offers', function ($query) {
            $query->where('type', 'sell')
                  ->where('status', 'active');
        })->get();

        $insufficientUsers = [];

        foreach ($users as $user) {
            $offers = $user->offers()
                ->where('type', 'sell')
                ->where('status', 'active')
                ->get();

            foreach ($offers as $offer) {
                $validation = $this->validateOfferAmount($user, $offer->amount, 'sell');
                
                if (!$validation['valid']) {
                    $insufficientUsers[] = [
                        'user' => $user,
                        'offer' => $offer,
                        'validation' => $validation
                    ];
                }
            }
        }

        return $insufficientUsers;
    }

    /**
     * Auto-disable offers with insufficient balance
     */
    public function autoDisableInsufficientOffers(): int
    {
        $insufficientUsers = $this->getUsersWithInsufficientBalance();
        $disabledCount = 0;

        foreach ($insufficientUsers as $item) {
            $offer = $item['offer'];
            $offer->update(['status' => 'insufficient_balance']);
            $disabledCount++;

            Log::info('Auto-disabled offer due to insufficient balance', [
                'user_id' => $offer->user_id,
                'offer_id' => $offer->id,
                'amount' => $offer->amount,
                'available_balance' => $item['validation']['available_balance']
            ]);
        }

        return $disabledCount;
    }
}
