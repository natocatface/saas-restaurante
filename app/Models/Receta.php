<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'recetas';

    protected $fillable = [
        'restaurante_id', 'producto_id', 'insumo_id', 'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
