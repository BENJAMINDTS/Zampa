<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración del ticket PDF por restaurante.
 * Relación 1:1 con User.
 *
 * @package App\Models
 * @property int         $user_id
 * @property string|null $logo         Ruta en storage/app/public/tickets
 * @property string|null $tax_id       NIF / CIF del negocio
 * @property string|null $footer_text  Texto libre en el pie del ticket
 * @property string      $template     Plantilla: classic | modern | minimal
 *
 * @author SebastianBCF
 */
class TicketConfig extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    public const TEMPLATES = ['classic', 'modern', 'minimal'];

    /** @var array<string, mixed> */
    protected $attributes = ['template' => 'classic'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'logo',
        'tax_id',
        'footer_text',
        'template',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Devuelve la URL pública del logo o null si no hay logo configurado.
     *
     * @return string|null
     */
    public function getLogoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * @return bool
     */
    public function hasLogo(): bool
    {
        return ! empty($this->logo);
    }
}
