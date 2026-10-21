<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender',
        'content',
        'tokens_used',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function isFromUser(): bool
    {
        return $this->sender === 'user';
    }

    public function isFromAssistant(): bool
    {
        return $this->sender === 'assistant';
    }
}
