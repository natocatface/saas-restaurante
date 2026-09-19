<?php

namespace App\Facturacion\Exceptions;

use App\Facturacion\Enums\Pais;

class PaisNoSoportadoException extends FacturacionException
{
    public static function para(Pais|string $pais): self
    {
        $codigo = $pais instanceof Pais ? $pais->value : $pais;

        return new self("No hay un adaptador de facturación registrado para el país [{$codigo}].");
    }
}
