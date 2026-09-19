<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta, $data] = $this->datos($request);

        return view('modules.reportes.index', array_merge($data, [
            'config' => Restaurante::actual(),
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }

    /** Exporta la rentabilidad por plato a CSV (compatible con Excel). */
    public function exportarRentabilidad(Request $request): StreamedResponse
    {
        [, , $data] = $this->datos($request);
        $filas = $data['rentabilidad'];

        $nombre = 'rentabilidad_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($filas) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para acentos en Excel
            fputcsv($out, ['Producto', 'Unidades', 'Ingresos', 'Costo unitario', 'Costo total', 'Ganancia', 'Margen %']);
            foreach ($filas as $r) {
                fputcsv($out, [
                    $r->nombre, $r->unidades,
                    number_format($r->ingreso, 2, '.', ''),
                    number_format($r->costo_unit, 2, '.', ''),
                    number_format($r->costo_total, 2, '.', ''),
                    number_format($r->ganancia, 2, '.', ''),
                    $r->margen,
                ]);
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Vista imprimible (PDF vía navegador). */
    public function imprimir(Request $request)
    {
        [$desde, $hasta, $data] = $this->datos($request);

        return view('modules.reportes.imprimir', array_merge($data, [
            'config' => Restaurante::actual(),
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }

    /** Cálculos compartidos por las vistas y exportaciones. */
    private function datos(Request $request): array
    {
        $desde = $request->date('desde') ?? now()->startOfMonth();
        $hasta = $request->date('hasta') ?? now()->endOfDay();

        $base = Pedido::where('estado', 'pagado')->whereBetween('pagado_at', [$desde, $hasta]);

        $totalVentas = (clone $base)->sum('total');
        $numPedidos = (clone $base)->count();
        $ticketPromedio = $numPedidos ? $totalVentas / $numPedidos : 0;

        $porDia = (clone $base)
            ->select(DB::raw('DATE(pagado_at) as d'), DB::raw('SUM(total) as t'))
            ->groupBy('d')->orderBy('d')->get();

        $porMetodo = (clone $base)
            ->select('metodo_pago', DB::raw('SUM(total) as t'))
            ->groupBy('metodo_pago')->get();

        $topProductos = PedidoItem::select('nombre_producto', DB::raw('SUM(cantidad) as q'), DB::raw('SUM(subtotal) as t'))
            ->whereHas('pedido', fn ($qq) => $qq->where('estado', 'pagado')->whereBetween('pagado_at', [$desde, $hasta]))
            ->groupBy('nombre_producto')->orderByDesc('t')->take(10)->get();

        // Rentabilidad por plato (usa el costo del producto, que refleja la receta)
        $rentabilidad = PedidoItem::query()
            ->join('productos', 'productos.id', '=', 'pedido_items.producto_id')
            ->whereHas('pedido', fn ($qq) => $qq->where('estado', 'pagado')->whereBetween('pagado_at', [$desde, $hasta]))
            ->groupBy('productos.id', 'productos.nombre')
            ->select(
                'productos.nombre as nombre',
                DB::raw('SUM(pedido_items.cantidad) as unidades'),
                DB::raw('SUM(pedido_items.subtotal) as ingreso'),
                DB::raw('MAX(productos.costo) as costo_unit'),
                DB::raw('SUM(pedido_items.cantidad * productos.costo) as costo_total')
            )
            ->orderByDesc(DB::raw('SUM(pedido_items.subtotal) - SUM(pedido_items.cantidad * productos.costo)'))
            ->get()
            ->map(function ($r) {
                $r->ingreso = (float) $r->ingreso;
                $r->costo_total = (float) $r->costo_total;
                $r->costo_unit = (float) $r->costo_unit;
                $r->ganancia = round($r->ingreso - $r->costo_total, 2);
                $r->margen = $r->ingreso > 0 ? round($r->ganancia / $r->ingreso * 100, 1) : 0;
                return $r;
            });

        $ingresoTotalR = $rentabilidad->sum('ingreso');
        $costoTotalR = $rentabilidad->sum('costo_total');
        $gananciaTotalR = round($ingresoTotalR - $costoTotalR, 2);
        $margenTotalR = $ingresoTotalR > 0 ? round($gananciaTotalR / $ingresoTotalR * 100, 1) : 0;

        return [$desde, $hasta, compact(
            'totalVentas', 'numPedidos', 'ticketPromedio', 'porDia', 'porMetodo',
            'topProductos', 'rentabilidad', 'ingresoTotalR', 'costoTotalR', 'gananciaTotalR', 'margenTotalR'
        )];
    }
}
