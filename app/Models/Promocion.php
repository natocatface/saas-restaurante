<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'promociones';

    protected $fillable = [
        'restaurante_id', 'nombre', 'codigo', 'tipo', 'valor', 'alcance',
        'producto_id', 'min_compra', 'inicia_at', 'termina_at', 'activo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'min_compra' => 'decimal:2',
        'inicia_at' => 'date',
        'termina_at' => 'date',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function vigente(): bool
    {
        if (! $this->activo) {
            return false;
        }
        $hoy = today();
        if ($this->inicia_at && $hoy->lt($this->inicia_at)) {
            return false;
        }
        if ($this->termina_at && $hoy->gt($this->termina_at)) {
            return false;
        }
        return true;
    }

    /**
     * Calcula el descuento que aplica esta promoción.
     * $subtotal: subtotal del pedido. $lineasPorProducto: [producto_id => importe].
     */
    public function calcularDescuento(float $subtotal, array $lineasPorProducto = []): float
    {
        if (! $this->vigente()) {
            return 0;
        }
        if ($this->min_compra && $subtotal < (float) $this->min_compra) {
            return 0;
        }

        $baseAfectada = $this->alcance === 'producto'
            ? (float) ($lineasPorProducto[$this->producto_id] ?? 0)
            : $subtotal;

        if ($baseAfectada <= 0) {
            return 0;
        }

        $desc = $this->tipo === 'porcentaje'
            ? $baseAfectada * ((float) $this->valor / 100)
            : min((float) $this->valor, $baseAfectada);

        return round(min($desc, $subtotal), 2);
    }
}
