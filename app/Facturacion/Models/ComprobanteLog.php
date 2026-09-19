<?php

namespace App\Facturacion\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bitácora de auditoría: cada interacción con la autoridad fiscal
 * (request/response, cambios de estado, reintentos) queda registrada aquí.
 */
class ComprobanteLog extends Model
{
    protected $table = 'comprobante_logs';

    protected $fillable = [
        'comprobante_id', 'accion', 'estado', 'codigo', 'mensaje', 'payload',
    ];

    protected $casts = ['payload' => 'array'];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }
}
