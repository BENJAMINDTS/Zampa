<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa un mensaje individual dentro de una conversación del chatbot.
 *
 * El rol indica el origen del mensaje: 'user' (cliente), 'assistant' (IA)
 * o 'system' (prompt de contexto del restaurante).
 *
 * @author AyrtonAlania
 *
 * @property int    $id
 * @property int    $conversation_id  Conversación a la que pertenece
 * @property string $role             'user', 'assistant' o 'system'
 * @property string $content          Texto del mensaje
 * @property int|null $tokens_used    Tokens consumidos en este mensaje
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
    ];

    /**
     * Conversación a la que pertenece este mensaje.
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
