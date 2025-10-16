<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password_hash',
        'pin_hash',
        'pin_attempts',
        'pin_locked_until',
        'pgp_public_key',
        'pgp_fpr',
        'pgp_verified_at',
        'country',
        'is_tor_only',
        'is_admin',
        'status',
    ];

    protected $hidden = [
        'password_hash',
        'pin_hash',
    ];

    protected $casts = [
        'pin_locked_until' => 'datetime',
        'pgp_verified_at' => 'datetime',
        'is_tor_only' => 'boolean',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the user's offers
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get trades where user is buyer
     */
    public function buyerTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    /**
     * Get trades where user is seller
     */
    public function sellerTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'seller_id');
    }

    /**
     * Get all trades for user
     */
    public function trades(): BelongsToMany
    {
        return $this->belongsToMany(Trade::class, 'trade_users', 'user_id', 'trade_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get user's security logs
     */
    public function securityLogs(): HasMany
    {
        return $this->hasMany(UserSecurityLog::class);
    }

    /**
     * Get user's verification
     */
    public function verification(): HasOne
    {
        return $this->hasOne(Verification::class);
    }

    /**
     * Get feedback given by user
     */
    public function feedbackGiven(): HasMany
    {
        return $this->hasMany(Feedback::class, 'from_user_id');
    }

    /**
     * Get feedback received by user
     */
    public function feedbackReceived(): HasMany
    {
        return $this->hasMany(Feedback::class, 'to_user_id');
    }

    /**
     * Get user's messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get disputes opened by user
     */
    public function disputesOpened(): HasMany
    {
        return $this->hasMany(Dispute::class, 'opened_by_id');
    }

    /**
     * Get disputes assigned to user
     */
    public function disputesAssigned(): HasMany
    {
        return $this->hasMany(Dispute::class, 'assigned_to_id');
    }

    /**
     * Set password with Argon2id hashing
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }

    /**
     * Set PIN with Argon2id hashing
     */
    public function setPinAttribute($value): void
    {
        $this->attributes['pin_hash'] = Hash::make($value);
    }

    /**
     * Verify PIN
     */
    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    /**
     * Check if PIN is locked
     */
    public function isPinLocked(): bool
    {
        return $this->pin_locked_until && $this->pin_locked_until->isFuture();
    }

    /**
     * Increment PIN attempts and lock if necessary
     */
    public function incrementPinAttempts(): void
    {
        $this->pin_attempts++;
        
        $maxAttempts = config('auth.pin_max_attempts', 5);
        $lockoutMinutes = config('auth.pin_lockout_minutes', 15);
        
        if ($this->pin_attempts >= $maxAttempts) {
            $this->pin_locked_until = now()->addMinutes($lockoutMinutes);
        }
        
        $this->save();
    }

    /**
     * Reset PIN attempts
     */
    public function resetPinAttempts(): void
    {
        $this->pin_attempts = 0;
        $this->pin_locked_until = null;
        $this->save();
    }

    /**
     * Check if user has PGP key verified
     */
    public function hasVerifiedPgp(): bool
    {
        return !is_null($this->pgp_verified_at);
    }

    /**
     * Get user's reputation score
     */
    public function getReputationScore(): float
    {
        return Cache::remember("user_reputation_{$this->id}", 300, function () {
            $feedback = $this->feedbackReceived();
            
            $positive = $feedback->where('rating', '+1')->count();
            $neutral = $feedback->where('rating', '0')->count();
            $negative = $feedback->where('rating', '-1')->count();
            
            $total = $positive + $neutral + $negative;
            
            if ($total === 0) {
                return 0.0;
            }
            
            return (($positive - $negative) / $total) * 100;
        });
    }

    /**
     * Get user's trade completion rate
     */
    public function getCompletionRate(): float
    {
        return Cache::remember("user_completion_rate_{$this->id}", 300, function () {
            $totalTrades = $this->buyerTrades()->count() + $this->sellerTrades()->count();
            $completedTrades = $this->buyerTrades()->where('state', 'completed')->count() + 
                              $this->sellerTrades()->where('state', 'completed')->count();
            
            if ($totalTrades === 0) {
                return 0.0;
            }
            
            return ($completedTrades / $totalTrades) * 100;
        });
    }

    /**
     * Get user's account age in days
     */
    public function getAccountAgeDays(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
}

