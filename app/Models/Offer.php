<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'side',
        'price_mode',
        'fixed_price',
        'margin_bps',
        'currency',
        'min_xmr_atomic',
        'max_xmr_atomic',
        'payment_method_id',
        'country',
        'online_or_inperson',
        'terms_md',
        'active',
    ];

    protected $casts = [
        'fixed_price' => 'decimal:8',
        'min_xmr_atomic' => 'integer',
        'max_xmr_atomic' => 'integer',
        'margin_bps' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get the user who created the offer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get trades for this offer
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    /**
     * Get current price for floating offers
     */
    public function getCurrentPrice(): ?float
    {
        if ($this->price_mode === 'fixed') {
            return $this->fixed_price;
        }

        return Cache::remember("offer_price_{$this->id}", 60, function () {
            $priceService = app(\App\Services\PriceIndexService::class);
            $basePrice = $priceService->getPrice($this->currency);
            
            if (!$basePrice) {
                return null;
            }

            $margin = $this->margin_bps / 10000; // Convert basis points to decimal
            return $basePrice * (1 + $margin);
        });
    }

    /**
     * Get minimum XMR amount in XMR units
     */
    public function getMinXmr(): float
    {
        return $this->min_xmr_atomic / 1e12;
    }

    /**
     * Get maximum XMR amount in XMR units
     */
    public function getMaxXmr(): float
    {
        return $this->max_xmr_atomic / 1e12;
    }

    /**
     * Check if offer is active
     */
    public function isActive(): bool
    {
        return $this->active && $this->user->isActive();
    }

    /**
     * Check if offer is buy side
     */
    public function isBuy(): bool
    {
        return $this->side === 'buy';
    }

    /**
     * Check if offer is sell side
     */
    public function isSell(): bool
    {
        return $this->side === 'sell';
    }

    /**
     * Check if offer is online
     */
    public function isOnline(): bool
    {
        return $this->online_or_inperson === 'online';
    }

    /**
     * Check if offer is in-person
     */
    public function isInPerson(): bool
    {
        return $this->online_or_inperson === 'inperson';
    }

    /**
     * Get formatted terms (Markdown to HTML)
     */
    public function getFormattedTerms(): string
    {
        if (!$this->terms_md) {
            return '';
        }

        $converter = new \League\CommonMark\CommonMarkConverter();
        return $converter->convert($this->terms_md)->getContent();
    }

    /**
     * Scope for active offers
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for buy offers
     */
    public function scopeBuy($query)
    {
        return $query->where('side', 'buy');
    }

    /**
     * Scope for sell offers
     */
    public function scopeSell($query)
    {
        return $query->where('side', 'sell');
    }

    /**
     * Scope for online offers
     */
    public function scopeOnline($query)
    {
        return $query->where('online_or_inperson', 'online');
    }

    /**
     * Scope for in-person offers
     */
    public function scopeInPerson($query)
    {
        return $query->where('online_or_inperson', 'inperson');
    }

    /**
     * Scope for offers by country
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope for offers by payment method
     */
    public function scopeByPaymentMethod($query, $paymentMethodId)
    {
        return $query->where('payment_method_id', $paymentMethodId);
    }

    /**
     * Scope for offers by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope for offers within XMR range
     */
    public function scopeWithinXmrRange($query, $minXmr, $maxXmr)
    {
        $minAtomic = $minXmr * 1e12;
        $maxAtomic = $maxXmr * 1e12;
        
        return $query->where('min_xmr_atomic', '<=', $maxAtomic)
                    ->where('max_xmr_atomic', '>=', $minAtomic);
    }

    /**
     * Check if user has sufficient balance for this offer
     */
    public function hasSufficientBalance(): bool
    {
        if ($this->side !== 'sell') {
            return true; // Buy offers don't require XMR balance
        }

        $walletBalanceService = app(\App\Services\WalletBalanceService::class);
        return $walletBalanceService->hasSufficientBalance($this->user, $this->getAmountXmr());
    }

    /**
     * Validate offer against user's wallet balance
     */
    public function validateBalance(): array
    {
        if ($this->side !== 'sell') {
            return ['valid' => true];
        }

        $walletBalanceService = app(\App\Services\WalletBalanceService::class);
        return $walletBalanceService->validateOfferAmount($this->user, $this->getAmountXmr(), 'sell');
    }

    /**
     * Auto-disable offer if insufficient balance
     */
    public function checkAndDisableIfInsufficient(): bool
    {
        if ($this->side !== 'sell' || !$this->active) {
            return false;
        }

        $validation = $this->validateBalance();
        
        if (!$validation['valid']) {
            $this->update(['active' => false]);
            
            \Log::info('Auto-disabled offer due to insufficient balance', [
                'offer_id' => $this->id,
                'user_id' => $this->user_id,
                'amount' => $this->getAmountXmr(),
                'available_balance' => $validation['available_balance'] ?? 0
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Get offer amount in XMR
     */
    public function getAmountXmr(): float
    {
        // Use the maximum amount for validation to be safe
        return $this->max_xmr_atomic / 1e12;
    }

    /**
     * Check if offer can be activated
     */
    public function canBeActivated(): bool
    {
        if ($this->side !== 'sell') {
            return true;
        }

        return $this->hasSufficientBalance();
    }
}

