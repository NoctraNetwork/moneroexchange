<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_hash',
        'ua_hash',
        'is_tor',
    ];

    protected $casts = [
        'is_tor' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if log is from Tor
     */
    public function isFromTor(): bool
    {
        return $this->is_tor;
    }

    /**
     * Scope for Tor logs
     */
    public function scopeTor($query)
    {
        return $query->where('is_tor', true);
    }

    /**
     * Scope for clearnet logs
     */
    public function scopeClearnet($query)
    {
        return $query->where('is_tor', false);
    }
}

