<?php

namespace App\Facturacion\Paises\Peru;

use App\Facturacion\Exceptions\FacturacionException;
use App\Models\Restaurante;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

/**
 * Construye y configura el objeto Greenter\See.
 *
 * Prioriza la configuración guardada por empresa (tenant) en la pantalla de
 * Configuración de Facturación; si algún dato falta, usa los valores globales
 * de config/facturacion.php (.env) como respaldo. Así cada restaurante emite
 * con su propio RUC, credenciales SOL y certificado.
 */
class SunatConnectionFactory
{
    public function __construct(private readonly array $config) {}

    public function crear(string $ruc): See
    {
        $rest = Restaurante::withoutGlobalScopes()->where('ruc', $ruc)->first();

        $ambiente = $rest?->sunat_ambiente ?: ($this->config['ambiente'] ?? 'beta');
        $solUser  = $rest?->sunat_sol_user ?: ($this->config['usuario_sol'] ?? '');
        $solPass  = $rest?->sunat_sol_pass ?: ($this->config['clave_sol'] ?? '');
        $certPath = $rest?->sunat_cert_path ?: ($this->config['certificado'] ?? null);

        $see = new See();
        $see->setCertificate($this->leerCertificado($certPath));

        $endpoint = $ambiente === 'produccion'
            ? SunatEndpoints::FE_PRODUCCION
            : SunatEndpoints::FE_BETA;
        $see->setService($this->config['endpoint'] ?? $endpoint);

        $see->setClaveSOL($ruc, (string) $solUser, (string) $solPass);

        return $see;
    }

    private function leerCertificado(?string $ruta): string
    {
        if (! $ruta) {
            throw new FacturacionException('No se configuró el certificado de SUNAT para esta empresa.');
        }

        $absoluta = str_starts_with($ruta, '/') ? $ruta : base_path($ruta);

        if (! is_file($absoluta)) {
            throw new FacturacionException("Certificado SUNAT no encontrado en: {$absoluta}");
        }

        return (string) file_get_contents($absoluta);
    }
}
