<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'from_user_id',
        'to_user_id',
        'rating',
        'comment',
    ];

    /**
     * Get the trade
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the user who gave feedback
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who received feedback
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Check if feedback is positive
     */
    public function isPositive(): bool
    {
        return $this->rating === '+1';
    }

    /**
     * Check if feedback is neutral
     */
    public function isNeutral(): bool
    {
        return $this->rating === '0';
    }

    /**
     * Check if feedback is negative
     */
    public function isNegative(): bool
    {
        return $this->rating === '-1';
    }

    /**
     * Get numeric rating value
     */
    public function getNumericRating(): int
    {
        return match ($this->rating) {
            '+1' => 1,
            '0' => 0,
            '-1' => -1,
            default => 0,
        };
    }

    /**
     * Scope for positive feedback
     */
    public function scopePositive($query)
    {
        return $query->where('rating', '+1');
    }

    /**
     * Scope for neutral feedback
     */
    public function scopeNeutral($query)
    {
        return $query->where('rating', '0');
    }

    /**
     * Scope for negative feedback
     */
    public function scopeNegative($query)
    {
        return $query->where('rating', '-1');
    }
}

