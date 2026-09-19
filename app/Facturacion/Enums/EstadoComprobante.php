<?php

namespace App\Facturacion\Enums;

/**
 * Ciclo de vida de un comprobante dentro del ERP,
 * independiente de los códigos de estado de cada autoridad fiscal.
 */
enum EstadoComprobante: string
{
    case PENDIENTE  = 'pendiente';   // creado, aún no enviado
    case ENVIANDO   = 'enviando';    // en cola / en proceso
    case ACEPTADO   = 'aceptado';    // aceptado por la autoridad
    case RECHAZADO  = 'rechazado';   // rechazado (error de negocio)
    case OBSERVADO  = 'observado';   // aceptado con observaciones
    case ANULADO    = 'anulado';     // baja / anulación comunicada
    case ERROR      = 'error';       // fallo técnico, reintentable

    public function esFinal(): bool
    {
        return in_array($this, [self::ACEPTADO, self::RECHAZADO, self::ANULADO], true);
    }

    public function esReintentable(): bool
    {
        return in_array($this, [self::ERROR, self::PENDIENTE], true);
    }
}
