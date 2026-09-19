<?php

namespace App\Models;

/**
 * @deprecated La configuración del negocio ahora vive en el modelo Restaurante
 * (multi-tenant). Esta clase se mantiene como alias por compatibilidad.
 */
class Configuracion extends Restaurante
{
    public static function actual(): ?Restaurante
    {
        return Restaurante::actual();
    }
}
