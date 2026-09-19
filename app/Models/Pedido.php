<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Pedido extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pedidos';

    protected $fillable = [
        'restaurante_id', 'codigo', 'mesa_id', 'cliente_id', 'user_id', 'tipo', 'estado',
        'subtotal', 'descuento', 'impuesto', 'total', 'metodo_pago', 'notas', 'pagado_at',
        'promocion_id', 'puntos_usados', 'puntos_ganados',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'pagado_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getColorEstadoAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'amber',
            'preparando' => 'sky',
            'servido'    => 'indigo',
            'pagado'     => 'emerald',
            'cancelado'  => 'rose',
            default      => 'slate',
        };
    }

    /**
     * Restaura el inventario consumido por este pedido (al anular/cancelar).
     * Devuelve insumos según receta y stock de productos que lo controlen.
     */
    public function restaurarInventario(): void
    {
        $this->loadMissing('items.producto.recetas.insumo');

        foreach ($this->items as $item) {
            $prod = $item->producto;
            if (! $prod) {
                continue;
            }

            if ($prod->controla_stock) {
                $prod->increment('stock', $item->cantidad);
            }
            $prod->decrement('vendidos', min($item->cantidad, (int) $prod->vendidos));

            foreach ($prod->recetas as $r) {
                if ($r->insumo) {
                    \App\Models\InsumoMovimiento::registrar(
                        $r->insumo, 'entrada',
                        (float) $r->cantidad * $item->cantidad,
                        'Anulación de pedido', $this->codigo
                    );
                }
            }
        }
    }

    public static function generarCodigo(): string
    {
        return 'PED-'.now()->format('ymd').'-'.str_pad((string) (self::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
