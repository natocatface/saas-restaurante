<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use App\Models\SerieComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración de Facturación Electrónica por empresa (tenant).
 * Gestiona los datos del emisor, credenciales SUNAT, certificado y series.
 */
class FacturacionConfigController extends Controller
{
    public function edit()
    {
        $rest = Restaurante::actual();

        $series = SerieComprobante::where('pais', 'PE')->get()->keyBy('tipo');
        $serieFactura = $series->get('factura');
        $serieBoleta = $series->get('boleta');

        return view('modules.facturacion.config', compact('rest', 'serieFactura', 'serieBoleta'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ruc'             => 'nullable|string|max:20',
            'nombre'          => 'required|string|max:255',
            'direccion'       => 'nullable|string|max:255',
            'ubigeo'          => 'nullable|string|size:6',
            'moneda'          => 'required|string|max:10',
            'igv'             => 'required|numeric|min:0|max:100',
            'fe_activo'       => 'nullable|boolean',
            'sunat_ambiente'  => 'required|in:beta,produccion',
            'sunat_sol_user'  => 'nullable|string|max:100',
            'sunat_sol_pass'  => 'nullable|string|max:100',
            'certificado'     => 'nullable|file|max:5120', // .pem / .pfx (máx 5MB)
            'factura_serie'   => 'nullable|string|max:8',
            'factura_corr'    => 'nullable|integer|min:0',
            'boleta_serie'    => 'nullable|string|max:8',
            'boleta_corr'     => 'nullable|integer|min:0',
        ]);

        $rest = Restaurante::actual();

        // Certificado: se guarda por RUC en storage/app/certs y se registra la ruta.
        $certPath = $rest->sunat_cert_path;
        if ($request->hasFile('certificado')) {
            $ruc = $data['ruc'] ?: $rest->ruc ?: 'sin-ruc';
            $ext = $request->file('certificado')->getClientOriginalExtension() ?: 'pem';
            $stored = $request->file('certificado')->storeAs('certs', "{$ruc}.{$ext}");
            $certPath = 'storage/app/'.$stored; // ruta relativa a base_path()
        }

        $rest->update([
            'ruc'             => $data['ruc'] ?? null,
            'nombre'          => $data['nombre'],
            'direccion'       => $data['direccion'] ?? null,
            'ubigeo'          => $data['ubigeo'] ?? null,
            'moneda'          => $data['moneda'],
            'igv'             => $data['igv'],
            'pais'            => 'PE',
            'fe_activo'       => (bool) ($data['fe_activo'] ?? false),
            'sunat_ambiente'  => $data['sunat_ambiente'],
            'sunat_sol_user'  => $data['sunat_sol_user'] ?? null,
            // Solo actualiza la clave si se ingresó una nueva (no borra la existente).
            'sunat_sol_pass'  => filled($data['sunat_sol_pass'] ?? null) ? $data['sunat_sol_pass'] : $rest->sunat_sol_pass,
            'sunat_cert_path' => $certPath,
        ]);

        $this->upsertSerie('factura', $data['factura_serie'] ?? 'F001', (int) ($data['factura_corr'] ?? 0));
        $this->upsertSerie('boleta', $data['boleta_serie'] ?? 'B001', (int) ($data['boleta_corr'] ?? 0));

        return back()->with('success', 'Configuración de facturación guardada correctamente.');
    }

    private function upsertSerie(string $tipo, string $serie, int $correlativo): void
    {
        SerieComprobante::updateOrCreate(
            ['pais' => 'PE', 'tipo' => $tipo],
            ['serie' => strtoupper($serie), 'correlativo_actual' => $correlativo, 'activa' => true],
        );
    }
}
