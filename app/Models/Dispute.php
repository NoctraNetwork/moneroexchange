<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'opened_by_id',
        'assigned_to_id',
        'status',
        'resolution',
        'notes',
    ];

    /**
     * Get the trade
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the user who opened the dispute
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    /**
     * Get the moderator assigned to the dispute
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Check if dispute is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if dispute is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if dispute is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if dispute has a resolution
     */
    public function hasResolution(): bool
    {
        return !is_null($this->resolution);
    }

    /**
     * Check if dispute is assigned
     */
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to_id);
    }

    /**
     * Scope for open disputes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for resolved disputes
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for closed disputes
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for assigned disputes
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to_id');
    }

    /**
     * Scope for unassigned disputes
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to_id');
    }
}

