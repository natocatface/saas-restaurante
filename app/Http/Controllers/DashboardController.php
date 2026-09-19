<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Restaurante;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $config = Restaurante::actual();
        $hoy = today();

        // --- Tarjetas ---
        $ventasHoy = Pedido::where('estado', 'pagado')->whereDate('pagado_at', $hoy)->sum('total');
        $mesasOcupadas = Mesa::where('estado', 'ocupada')->count();
        $mesasTotal = Mesa::count();
        $pedidosActivos = Pedido::whereIn('estado', ['pendiente', 'preparando', 'servido'])->count();
        $clientesNuevos = Cliente::whereDate('created_at', $hoy)->count();

        // --- Meta mensual ---
        $ventasMes = Pedido::where('estado', 'pagado')
            ->whereMonth('pagado_at', $hoy->month)
            ->whereYear('pagado_at', $hoy->year)
            ->sum('total');
        $meta = (float) $config->meta_mensual ?: 1;
        $progresoMeta = min(100, round($ventasMes / $meta * 100, 1));

        // --- Ingreso actual (gauge): avance del día vs meta diaria ---
        $metaDiaria = $meta / max(1, (int) $hoy->daysInMonth);
        $ingresoPct = $metaDiaria > 0 ? min(100, round($ventasHoy / $metaDiaria * 100, 1)) : 0;

        // --- Actividad por hora (hoy) ---
        $porHora = Pedido::where('estado', 'pagado')
            ->whereDate('pagado_at', $hoy)
            ->select(DB::raw('HOUR(pagado_at) as h'), DB::raw('SUM(total) as t'))
            ->groupBy('h')->pluck('t', 'h');
        $horas = [];
        $serie = [];
        for ($h = 8; $h <= 23; $h++) {
            $horas[] = sprintf('%02d:00', $h);
            $serie[] = round((float) ($porHora[$h] ?? 0), 2);
        }

        // --- Ventas últimos 7 días (mini tendencia) ---
        $ultimos7Labels = [];
        $ultimos7Data = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = $hoy->copy()->subDays($i);
            $ultimos7Labels[] = $dia->isoFormat('ddd');
            $ultimos7Data[] = round((float) Pedido::where('estado', 'pagado')->whereDate('pagado_at', $dia)->sum('total'), 2);
        }

        // --- Distribución de mesas por estado (donut) ---
        $mesasPorEstado = Mesa::select('estado', DB::raw('COUNT(*) as t'))
            ->groupBy('estado')->pluck('t', 'estado');
        $estadoLabels = ['libre' => 'Libres', 'ocupada' => 'Ocupadas', 'reservada' => 'Reservadas', 'cuenta' => 'Por cobrar'];
        $mesasDonutLabels = [];
        $mesasDonutData = [];
        foreach ($estadoLabels as $k => $lbl) {
            $mesasDonutLabels[] = $lbl;
            $mesasDonutData[] = (int) ($mesasPorEstado[$k] ?? 0);
        }

        // --- Estado de salones ---
        $mesas = Mesa::orderByRaw('CAST(numero AS UNSIGNED)')->take(8)->get();

        // --- Más vendidos ---
        $masVendidos = PedidoItem::select('nombre_producto', DB::raw('SUM(cantidad) as total'))
            ->groupBy('nombre_producto')
            ->orderByDesc('total')
            ->take(6)->get();

        // --- Pedidos recientes ---
        $pedidosRecientes = Pedido::with('mesa', 'user')->latest()->take(6)->get();

        return view('dashboard.index', compact(
            'config', 'ventasHoy', 'mesasOcupadas', 'mesasTotal', 'pedidosActivos',
            'clientesNuevos', 'progresoMeta', 'ventasMes', 'meta', 'ingresoPct',
            'metaDiaria', 'horas', 'serie', 'ultimos7Labels', 'ultimos7Data', 'mesas',
            'mesasDonutLabels', 'mesasDonutData', 'masVendidos', 'pedidosRecientes'
        ));
    }
}
