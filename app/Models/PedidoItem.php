<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class PedidoItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pedido_items';

    protected $fillable = [
        'restaurante_id', 'pedido_id', 'producto_id', 'nombre_producto', 'cantidad', 'precio', 'subtotal', 'notas',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
