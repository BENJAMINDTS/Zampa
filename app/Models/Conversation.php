<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa una sesión de chat del chatbot IA ligada a una mesa.
 *
 * Una mesa puede tener varias conversaciones a lo largo del tiempo.
 * Se cierra (status=closed) al finalizar la sesión o al pagar.
 * Los mensajes se eliminan en cascada al borrar la conversación.
 *
 * @author AyrtonAlania
 *
 * @property int    $id
 * @property int    $table_id     Mesa a la que pertenece la conversación
 * @property string $status       'active' o 'closed'
 * @property int    $tokens_used  Tokens acumulados consumidos en la API de IA
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'status',
        'tokens_used',
    ];

    /**
     * Mesa física a la que pertenece esta conversación.
     *
     * @return BelongsTo
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Mensajes intercambiados en esta conversación (user, assistant, system).
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
