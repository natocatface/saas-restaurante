<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre', 'slug', 'descripcion', 'precio', 'intervalo',
        'max_mesas', 'max_productos', 'max_usuarios',
        'features', 'destacado', 'activo', 'orden',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'features' => 'array',
        'destacado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function restaurantes()
    {
        return $this->hasMany(Restaurante::class);
    }

    public function limite(string $campo): ?int
    {
        return $this->{"max_$campo"};
    }

    public function limiteTexto(string $campo): string
    {
        $v = $this->limite($campo);
        return is_null($v) ? 'Ilimitado' : (string) $v;
    }
}
