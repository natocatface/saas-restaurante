<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'restaurante_id', 'plan_id', 'nombre_plan', 'monto', 'intervalo',
        'estado', 'metodo_pago', 'referencia', 'periodo_inicio', 'periodo_fin',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
