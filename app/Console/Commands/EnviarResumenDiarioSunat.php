<?php

namespace App\Console\Commands;

use App\Facturacion\Enums\EstadoComprobante;
use App\Facturacion\Models\Comprobante;
use App\Facturacion\Models\ComprobanteLog;
use App\Facturacion\Paises\Peru\SunatConnectionFactory;
use App\Facturacion\Paises\Peru\SunatResumenService;
use App\Models\Restaurante;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Comunica a SUNAT, por Resumen Diario, todas las boletas PE que quedaron
 * firmadas pero pendientes de informar. Agrupa por tenant (restaurante) y por
 * fecha de emisión. Se ejecuta programado a diario y también manualmente.
 *
 *   php artisan sunat:resumen            # boletas de HOY
 *   php artisan sunat:resumen 2026-07-06 # boletas de una fecha concreta
 */
class EnviarResumenDiarioSunat extends Command
{
    protected $signature = 'sunat:resumen {fecha? : Fecha de emisión (YYYY-MM-DD), por defecto hoy}';

    protected $description = 'Envía a SUNAT el resumen diario de boletas pendientes (Perú).';

    public function handle(): int
    {
        $fecha = $this->argument('fecha') ? Carbon::parse($this->argument('fecha')) : Carbon::today();
        $cfg = config('facturacion.paises.PE');

        if (! $cfg) {
            $this->error('No hay configuración SUNAT (PE).');

            return self::FAILURE;
        }

        $servicio = new SunatResumenService(new SunatConnectionFactory($cfg));

        // Tenants con boletas PE pendientes en la fecha (sin scope global de tenant).
        $tenantIds = Comprobante::withoutGlobalScopes()
            ->where('pais', 'PE')
            ->where('tipo', 'boleta')
            ->where('estado', EstadoComprobante::PENDIENTE->value)
            ->whereDate('created_at', $fecha)
            ->distinct()
            ->pluck('restaurante_id');

        if ($tenantIds->isEmpty()) {
            $this->info("Sin boletas pendientes para {$fecha->toDateString()}.");

            return self::SUCCESS;
        }

        foreach ($tenantIds as $tenantId) {
            $this->procesarTenant((int) $tenantId, $fecha, $servicio);
        }

        return self::SUCCESS;
    }

    private function procesarTenant(int $tenantId, Carbon $fecha, SunatResumenService $servicio): void
    {
        $rest = Restaurante::find($tenantId);
        if (! $rest) {
            return;
        }

        $boletas = Comprobante::withoutGlobalScopes()
            ->with('pedido.cliente')
            ->where('restaurante_id', $tenantId)
            ->where('pais', 'PE')
            ->where('tipo', 'boleta')
            ->where('estado', EstadoComprobante::PENDIENTE->value)
            ->whereDate('created_at', $fecha)
            ->get();

        // Correlativo del resumen: nº de resúmenes ya enviados ese día + 1.
        $correlativo = (string) (ComprobanteLog::whereDate('created_at', today())
            ->where('accion', 'resumen')
            ->count() + 1);

        $this->line("→ {$rest->nombre}: {$boletas->count()} boleta(s), resumen #{$correlativo}");

        $resultado = $servicio->enviar($rest, $boletas, $fecha, $correlativo);

        // Auditoría del envío del resumen.
        ComprobanteLog::create([
            'comprobante_id' => $boletas->first()?->id,
            'accion'         => 'resumen',
            'estado'         => $resultado->estado->value,
            'codigo'         => $resultado->codigoRespuesta,
            'mensaje'        => $resultado->mensaje,
            'payload'        => $resultado->crudo,
        ]);

        if ($resultado->exito) {
            Comprobante::withoutGlobalScopes()
                ->whereIn('id', $boletas->pluck('id'))
                ->update([
                    'estado'               => EstadoComprobante::ACEPTADO->value,
                    'identificador_fiscal' => $resultado->identificadorFiscal,
                    'mensaje'              => $resultado->mensaje,
                    'enviado_at'           => now(),
                ]);
            $this->info("  ✔ Aceptado: {$resultado->mensaje}");
        } else {
            $this->error("  ✖ {$resultado->mensaje}");
        }
    }
}
