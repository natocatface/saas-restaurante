<?php

namespace App\Http\Controllers\Modules;

use App\Facturacion\Jobs\EmitirComprobanteJob;
use App\Facturacion\Models\Comprobante;
use App\Facturacion\Services\ComprobanteBuilder;
use App\Facturacion\Services\EmisorService;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Facturacion\Paises\Peru\NumberToLetters;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * Puente entre el ERP (pedidos) y el dominio de facturación.
 * El controlador NO conoce SUNAT/DIAN/etc.: solo habla con el builder,
 * el EmisorService y (opcionalmente) el Job asíncrono.
 */
class FacturacionController extends Controller
{
    public function index()
    {
        $comprobantes = Comprobante::with('pedido')
            ->latest()
            ->paginate(20);

        return view('modules.facturacion.index', compact('comprobantes'));
    }

    public function emitir(Request $request, Pedido $pedido, ComprobanteBuilder $builder, EmisorService $emisor)
    {
        $tipo = $request->input('tipo', 'boleta'); // boleta | factura
        $data = $builder->desdePedido($pedido, $tipo);

        if (config('facturacion.async')) {
            EmitirComprobanteJob::dispatch(Restaurante::actual()->id, $data);

            return back()->with('status', 'Comprobante en cola de emisión.');
        }

        $comprobante = $emisor->emitir($data);

        return back()->with('status', "Comprobante {$comprobante->serie}-{$comprobante->correlativo}: {$comprobante->estado->value}");
    }

    public function anular(Request $request, Comprobante $comprobante, ComprobanteBuilder $builder, EmisorService $emisor)
    {
        $motivo = $request->input('motivo', 'Anulación solicitada');
        $data = $builder->desdePedido($comprobante->pedido, $comprobante->tipo);
        $emisor->anular($comprobante, $data, $motivo);

        return back()->with('status', 'Anulación procesada.');
    }

    public function resumen(Request $request)
    {
        // Dispara el resumen diario de boletas del día para el tenant actual.
        $fecha = $request->input('fecha'); // opcional YYYY-MM-DD
        Artisan::call('sunat:resumen', array_filter(['fecha' => $fecha]));

        return back()->with('status', 'Resumen diario enviado a SUNAT. '.trim(Artisan::output()));
    }

    public function imprimir(Comprobante $comprobante)
    {
        $comprobante->load('pedido.items.producto', 'pedido.cliente');
        $rest = Restaurante::actual();

        $tipoMap = ['factura' => '01', 'boleta' => '03', 'nota_credito' => '07', 'nota_debito' => '08'];
        $tipoCode = $tipoMap[$comprobante->tipo] ?? '03';

        $cliente = $comprobante->pedido?->cliente;
        $docNro = $cliente?->documento ?: '00000000';
        $tipoDocReceptor = match (strlen($docNro)) {
            11      => '6',
            8       => '1',
            default => '0',
        };

        $qrData = implode('|', [
            $rest->ruc,
            $tipoCode,
            $comprobante->serie,
            $comprobante->correlativo,
            number_format((float) $comprobante->impuesto, 2, '.', ''),
            number_format((float) $comprobante->total, 2, '.', ''),
            $comprobante->created_at->format('Y-m-d'),
            $tipoDocReceptor,
            $docNro,
        ]);

        $qr = $this->generarQr($qrData);
        $enLetras = (new NumberToLetters())->convertir(
            (float) $comprobante->total,
            $comprobante->moneda === 'PEN' ? 'SOLES' : $comprobante->moneda,
        );

        return view('modules.facturacion.print', compact('comprobante', 'rest', 'qr', 'qrData', 'enLetras', 'tipoCode'));
    }

    private function generarQr(string $data): ?string
    {
        try {
            $writer = new SvgWriter();
            $qr = new QrCode(
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 160,
                margin: 4,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );

            return $writer->write($qr)->getDataUri();
        } catch (\Throwable $e) {
            return null; // Si la librería QR no está instalada, se omite el QR.
        }
    }
}
