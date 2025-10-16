<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requires_reference',
        'cash_possible',
        'metadata_schema_json',
        'active',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'cash_possible' => 'boolean',
        'metadata_schema_json' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Get offers using this payment method
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Check if payment method is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Check if payment method requires reference
     */
    public function requiresReference(): bool
    {
        return $this->requires_reference;
    }

    /**
     * Check if payment method allows cash
     */
    public function allowsCash(): bool
    {
        return $this->cash_possible;
    }

    /**
     * Get metadata schema
     */
    public function getMetadataSchema(): array
    {
        return $this->metadata_schema_json ?? [];
    }

    /**
     * Scope for active payment methods
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

