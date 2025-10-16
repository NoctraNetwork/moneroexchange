<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    /**
     * Get the actor (user who performed the action)
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the subject model
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
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

    /**
     * Log an action
     */
    public static function log(
        ?int $actorId,
        string $action,
        string $subjectType,
        int $subjectId,
        array $data = []
    ): self {
        return static::create([
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'data_json' => $data,
        ]);
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific subject type
     */
    public function scopeSubjectType($query, $subjectType)
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * Scope for specific actor
     */
    public function scopeActor($query, $actorId)
    {
        return $query->where('actor_id', $actorId);
    }
}

