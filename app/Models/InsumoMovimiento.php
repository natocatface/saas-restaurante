<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoMovimiento extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'insumo_movimientos';

    protected $fillable = [
        'restaurante_id', 'insumo_id', 'user_id', 'tipo', 'cantidad',
        'stock_anterior', 'stock_nuevo', 'motivo', 'referencia',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'stock_anterior' => 'decimal:2',
        'stock_nuevo' => 'decimal:2',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Aplica un movimiento de inventario y deja registro en el kardex.
     * $tipo: entrada | salida | ajuste.  $cantidad siempre positivo.
     * Para 'ajuste', $cantidad es el nuevo stock absoluto.
     */
    public static function registrar(Insumo $insumo, string $tipo, float $cantidad, ?string $motivo = null, ?string $referencia = null, ?int $userId = null): self
    {
        $anterior = (float) $insumo->stock;

        $nuevo = match ($tipo) {
            'entrada' => $anterior + $cantidad,
            'salida'  => $anterior - $cantidad,
            'ajuste'  => $cantidad,
            default   => $anterior,
        };

        $insumo->update(['stock' => $nuevo]);

        return static::create([
            'insumo_id'      => $insumo->id,
            'user_id'        => $userId ?? auth()->id(),
            'tipo'           => $tipo,
            'cantidad'       => $tipo === 'ajuste' ? abs($nuevo - $anterior) : $cantidad,
            'stock_anterior' => $anterior,
            'stock_nuevo'    => $nuevo,
            'motivo'         => $motivo,
            'referencia'     => $referencia,
        ]);
    }
}
