<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Reserva extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'reservas';

    protected $fillable = [
        'restaurante_id', 'cliente_id', 'mesa_id', 'nombre_cliente', 'telefono',
        'fecha', 'hora', 'personas', 'estado', 'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }
}
