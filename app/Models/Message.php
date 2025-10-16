<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'sender_id',
        'body_stored',
        'is_pgp_encrypted',
    ];

    protected $casts = [
        'is_pgp_encrypted' => 'boolean',
    ];

    /**
     * Get the trade
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get decrypted message body
     */
    public function getDecryptedBody(): string
    {
        if (!$this->is_pgp_encrypted) {
            return $this->body_stored;
        }

        // TODO: Implement PGP decryption
        return $this->body_stored;
    }

    /**
     * Set encrypted message body
     */
    public function setEncryptedBody(string $body): void
    {
        $this->body_stored = $body;
        $this->is_pgp_encrypted = true;
    }

    /**
     * Set plain message body
     */
    public function setPlainBody(string $body): void
    {
        $this->body_stored = $body;
        $this->is_pgp_encrypted = false;
    }
}

