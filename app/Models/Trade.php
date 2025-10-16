<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'offer_id',
        'state',
        'amount_atomic',
        'price_per_xmr',
        'currency',
        'escrow_subaddr',
        'buyer_address',
        'expires_at',
    ];

    protected $casts = [
        'price_per_xmr' => 'decimal:8',
        'amount_atomic' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the buyer
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the offer
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Get trade events
     */
    public function events(): HasMany
    {
        return $this->hasMany(TradeEvent::class);
    }

    /**
     * Get messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get escrow movements
     */
    public function escrowMovements(): HasMany
    {
        return $this->hasMany(EscrowMovement::class);
    }

    /**
     * Get dispute
     */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Get feedback
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get amount in XMR units
     */
    public function getAmountXmr(): float
    {
        return $this->amount_atomic / 1e12;
    }

    /**
     * Get total fiat amount
     */
    public function getTotalFiatAmount(): float
    {
        return $this->getAmountXmr() * $this->price_per_xmr;
    }

    /**
     * Check if trade is in draft state
     */
    public function isDraft(): bool
    {
        return $this->state === 'draft';
    }

    /**
     * Check if trade is awaiting deposit
     */
    public function isAwaitingDeposit(): bool
    {
        return $this->state === 'await_deposit';
    }

    /**
     * Check if trade is escrowed
     */
    public function isEscrowed(): bool
    {
        return $this->state === 'escrowed';
    }

    /**
     * Check if trade is awaiting payment
     */
    public function isAwaitingPayment(): bool
    {
        return $this->state === 'await_payment';
    }

    /**
     * Check if trade is pending release
     */
    public function isPendingRelease(): bool
    {
        return $this->state === 'release_pending';
    }

    /**
     * Check if trade is refunded
     */
    public function isRefunded(): bool
    {
        return $this->state === 'refunded';
    }

    /**
     * Check if trade is completed
     */
    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    /**
     * Check if trade is disputed
     */
    public function isDisputed(): bool
    {
        return $this->state === 'disputed';
    }

    /**
     * Check if trade is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->state === 'cancelled';
    }

    /**
     * Check if trade is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if trade can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->state, ['draft', 'await_deposit', 'await_payment']);
    }

    /**
     * Check if trade can be released
     */
    public function canBeReleased(): bool
    {
        return $this->state === 'escrowed' || $this->state === 'await_payment';
    }

    /**
     * Check if trade can be refunded
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->state, ['escrowed', 'await_payment', 'release_pending']);
    }

    /**
     * Check if trade can be disputed
     */
    public function canBeDisputed(): bool
    {
        return in_array($this->state, ['escrowed', 'await_payment', 'release_pending']);
    }

    /**
     * Get escrow balance
     */
    public function getEscrowBalance(): int
    {
        $in = $this->escrowMovements()->where('direction', 'in')->sum('amount_atomic');
        $out = $this->escrowMovements()->where('direction', 'out')->sum('amount_atomic');
        $fees = $this->escrowMovements()->where('direction', 'fee')->sum('amount_atomic');
        
        return $in - $out - $fees;
    }

    /**
     * Check if escrow has sufficient funds
     */
    public function hasEscrowFunds(): bool
    {
        return $this->getEscrowBalance() >= $this->amount_atomic;
    }

    /**
     * Get time remaining until expiration
     */
    public function getTimeRemaining(): ?Carbon
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at->isFuture() ? $this->expires_at : null;
    }

    /**
     * Add event to trade
     */
    public function addEvent(string $type, ?int $actorId = null, array $data = []): TradeEvent
    {
        return $this->events()->create([
            'type' => $type,
            'actor_id' => $actorId,
            'data_json' => $data,
        ]);
    }

    /**
     * Scope for active trades
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('state', ['completed', 'cancelled', 'refunded']);
    }

    /**
     * Scope for completed trades
     */
    public function scopeCompleted($query)
    {
        return $query->where('state', 'completed');
    }

    /**
     * Scope for trades by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)
              ->orWhere('seller_id', $userId);
        });
    }

    /**
     * Scope for trades by state
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }
}

