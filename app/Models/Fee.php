<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'bps',
        'flat_atomic',
        'active',
    ];

    protected $casts = [
        'bps' => 'integer',
        'flat_atomic' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get fee amount in basis points as decimal
     */
    public function getFeeRate(): float
    {
        return $this->bps / 10000;
    }

    /**
     * Get flat fee in XMR units
     */
    public function getFlatFeeXmr(): float
    {
        return $this->flat_atomic / 1e12;
    }

    /**
     * Calculate fee for given amount
     */
    public function calculateFee(int $amountAtomic): int
    {
        $percentageFee = (int) round($amountAtomic * $this->getFeeRate());
        return $percentageFee + $this->flat_atomic;
    }

    /**
     * Check if fee is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Scope for active fees
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for specific type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}

