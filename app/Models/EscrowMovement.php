<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'direction',
        'amount_atomic',
        'tx_hash',
        'height',
        'confirmations',
    ];

    protected $casts = [
        'amount_atomic' => 'integer',
        'height' => 'integer',
        'confirmations' => 'integer',
    ];

    /**
     * Get the trade
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get amount in XMR units
     */
    public function getAmountXmr(): float
    {
        return $this->amount_atomic / 1e12;
    }

    /**
     * Check if movement is incoming
     */
    public function isIncoming(): bool
    {
        return $this->direction === 'in';
    }

    /**
     * Check if movement is outgoing
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'out';
    }

    /**
     * Check if movement is a fee
     */
    public function isFee(): bool
    {
        return $this->direction === 'fee';
    }

    /**
     * Check if transaction is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->confirmations >= config('monero.confirmations', 10);
    }

    /**
     * Scope for incoming movements
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'in');
    }

    /**
     * Scope for outgoing movements
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'out');
    }

    /**
     * Scope for fee movements
     */
    public function scopeFees($query)
    {
        return $query->where('direction', 'fee');
    }

    /**
     * Scope for confirmed movements
     */
    public function scopeConfirmed($query)
    {
        $minConfirmations = config('monero.confirmations', 10);
        return $query->where('confirmations', '>=', $minConfirmations);
    }
}

