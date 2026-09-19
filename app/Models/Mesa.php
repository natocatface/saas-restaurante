<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Mesa extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'mesas';

    protected $fillable = [
        'restaurante_id', 'numero', 'nombre', 'capacidad', 'zona', 'estado',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidoActivo()
    {
        return $this->hasOne(Pedido::class)->whereNotIn('estado', ['pagado', 'cancelado'])->latestOfMany();
    }

    public function getColorEstadoAttribute(): string
    {
        return match ($this->estado) {
            'libre'     => 'emerald',
            'ocupada'   => 'rose',
            'reservada' => 'amber',
            'cuenta'    => 'indigo',
            default     => 'slate',
        };
    }
}
