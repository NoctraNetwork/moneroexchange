<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'type',
        'actor_id',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    /**
     * Get the trade
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the actor (user who performed the action)
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get data as array
     */
    public function getData(): array
    {
        return $this->data_json ?? [];
    }

    /**
     * Set data from array
     */
    public function setData(array $data): void
    {
        $this->data_json = $data;
    }
}

