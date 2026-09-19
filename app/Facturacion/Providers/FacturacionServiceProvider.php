<?php

namespace App\Facturacion\Providers;

use App\Facturacion\Paises\Peru\NumberToLetters;
use App\Facturacion\Paises\Peru\SunatConnectionFactory;
use App\Facturacion\Paises\Peru\SunatFacturador;
use App\Facturacion\Paises\Peru\SunatMapper;
use App\Facturacion\Services\FacturacionManager;
use Illuminate\Support\ServiceProvider;

/**
 * Cablea el dominio de facturación con el contenedor de Laravel.
 *
 * Aquí —y SOLO aquí— se registran los adaptadores de cada país leyendo
 * config/facturacion.php. Sumar un país no toca el resto del sistema:
 * se añade su bloque de config y su caso en construirAdaptador().
 */
class FacturacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FacturacionManager::class, function ($app) {
            $manager = new FacturacionManager();

            foreach (config('facturacion.paises', []) as $codigo => $cfg) {
                $manager->registrar($this->construirAdaptador($codigo, $cfg));
            }

            return $manager;
        });
    }

    private function construirAdaptador(string $codigo, array $cfg): object
    {
        return match ($codigo) {
            'PE' => new SunatFacturador(
                new SunatConnectionFactory($cfg),
                new SunatMapper(new NumberToLetters()),
                $cfg,
            ),
            // 'CO' => new DianFacturador(...),
            // 'CL' => new SiiFacturador(...),
            default => $this->app->make($cfg['driver']),
        };
    }
}
