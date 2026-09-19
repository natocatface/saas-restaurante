<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Insumo extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'insumos';

    protected $fillable = [
        'restaurante_id', 'nombre', 'unidad', 'stock', 'stock_minimo', 'costo', 'proveedor',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'costo' => 'decimal:2',
    ];

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InsumoMovimiento::class);
    }

    public function getBajoStockAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
