<?php

namespace App\Facturacion\Models;

use App\Facturacion\Enums\EstadoComprobante;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Registro persistente de un comprobante electrónico (multi-tenant).
 * Es la fuente de verdad del ERP sobre lo emitido; la comunicación con la
 * autoridad fiscal se refleja aquí a través de estado + logs de auditoría.
 */
class Comprobante extends Model
{
    use BelongsToTenant;

    protected $table = 'comprobantes';

    protected $fillable = [
        'restaurante_id', 'pedido_id', 'pais', 'tipo', 'serie', 'correlativo',
        'moneda', 'subtotal', 'impuesto', 'total', 'estado',
        'identificador_fiscal', 'hash', 'xml_path', 'pdf_path',
        'codigo_respuesta', 'mensaje', 'intentos', 'enviado_at',
    ];

    protected $casts = [
        'estado'     => EstadoComprobante::class,
        'subtotal'   => 'decimal:2',
        'impuesto'   => 'decimal:2',
        'total'      => 'decimal:2',
        'correlativo' => 'integer',
        'intentos'   => 'integer',
        'enviado_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ComprobanteLog::class);
    }
}
