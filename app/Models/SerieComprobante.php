<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SerieComprobante extends Model
{
    use BelongsToTenant;

    protected $table = 'series_comprobantes';

    protected $fillable = [
        'restaurante_id', 'pais', 'tipo', 'serie', 'correlativo_actual', 'activa',
    ];

    protected $casts = [
        'correlativo_actual' => 'integer',
        'activa' => 'boolean',
    ];
}
