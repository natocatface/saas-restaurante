<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurante;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $total = Restaurante::count();
        $activos = Restaurante::where('estado', 'activo')->count();
        $trial = Restaurante::where('estado', 'trial')->count();
        $suspendidos = Restaurante::whereIn('estado', ['suspendido', 'cancelado'])->count();
        $totalUsuarios = User::where('role', '!=', 'superadmin')->count();

        // MRR estimado: suma del precio mensual de los planes de restaurantes activos
        $mrr = Restaurante::where('estado', 'activo')
            ->join('planes', 'restaurantes.plan_id', '=', 'planes.id')
            ->sum(DB::raw("CASE WHEN planes.intervalo = 'anual' THEN planes.precio / 12 ELSE planes.precio END"));

        $ingresoTotal = Suscripcion::where('estado', 'pagado')->sum('monto');

        // Restaurantes por plan (para dona)
        $porPlan = Plan::withCount('restaurantes')->orderBy('orden')->get();
        $planLabels = $porPlan->pluck('nombre');
        $planData = $porPlan->pluck('restaurantes_count');

        // Crecimiento: altas de restaurantes e ingresos por mes (últimos 12 meses)
        $mesLabels = [];
        $altasData = [];
        $ingresosData = [];
        for ($i = 11; $i >= 0; $i--) {
            $mes = Carbon::now()->startOfMonth()->subMonths($i);
            $mesLabels[] = $mes->locale('es')->isoFormat('MMM YY');
            $altasData[] = Restaurante::whereYear('created_at', $mes->year)->whereMonth('created_at', $mes->month)->count();
            $ingresosData[] = round((float) Suscripcion::where('estado', 'pagado')
                ->whereYear('periodo_inicio', $mes->year)->whereMonth('periodo_inicio', $mes->month)
                ->sum('monto'), 2);
        }

        $recientes = Restaurante::with('plan')->latest()->take(8)->get();

        return view('superadmin.dashboard', compact(
            'total', 'activos', 'trial', 'suspendidos', 'totalUsuarios',
            'mrr', 'ingresoTotal', 'porPlan', 'recientes',
            'planLabels', 'planData', 'mesLabels', 'altasData', 'ingresosData'
        ));
    }
}
