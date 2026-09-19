<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Producto extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'productos';

    protected $fillable = [
        'restaurante_id', 'categoria_id', 'nombre', 'sku', 'descripcion', 'precio', 'costo',
        'imagen', 'disponible', 'controla_stock', 'stock', 'vendidos',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'costo' => 'decimal:2',
        'disponible' => 'boolean',
        'controla_stock' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'recetas')->withPivot('cantidad')->withTimestamps();
    }

    /** Costo real del producto calculado a partir de su receta. */
    public function costoReceta(): float
    {
        return (float) $this->recetas->sum(fn ($r) => (float) $r->cantidad * (float) ($r->insumo->costo ?? 0));
    }

    /** Margen de ganancia (%) usando el costo de la receta. */
    public function margenReceta(): ?float
    {
        $costo = $this->costoReceta();
        if ($this->precio <= 0) {
            return null;
        }
        return round((($this->precio - $costo) / $this->precio) * 100, 1);
    }

    public function tieneReceta(): bool
    {
        return $this->recetas()->exists();
    }
}
