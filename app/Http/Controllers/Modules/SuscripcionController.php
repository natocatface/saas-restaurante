<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurante;
use App\Models\Suscripcion;
use Illuminate\Http\Request;

class SuscripcionController extends Controller
{
    public function index()
    {
        $restaurante = Restaurante::actual();
        $planes = Plan::where('activo', true)->orderBy('orden')->get();
        $historial = $restaurante->suscripciones()->latest()->get();

        return view('modules.suscripcion.index', compact('restaurante', 'planes', 'historial'));
    }

    /** Pago simulado: activa/renueva la suscripción del restaurante. */
    public function pagar(Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:planes,id',
            'metodo_pago' => 'required|in:tarjeta,yape,plin,transferencia',
        ]);

        $restaurante = Restaurante::actual();
        $plan = Plan::findOrFail($data['plan_id']);

        $inicio = now();
        $fin = $plan->intervalo === 'anual' ? $inicio->copy()->addYear() : $inicio->copy()->addMonth();

        Suscripcion::create([
            'restaurante_id' => $restaurante->id,
            'plan_id' => $plan->id,
            'nombre_plan' => $plan->nombre,
            'monto' => $plan->precio,
            'intervalo' => $plan->intervalo,
            'estado' => 'pagado',
            'metodo_pago' => $data['metodo_pago'],
            'referencia' => 'SIM-'.strtoupper(uniqid()),
            'periodo_inicio' => $inicio->toDateString(),
            'periodo_fin' => $fin->toDateString(),
        ]);

        $restaurante->update([
            'plan_id' => $plan->id,
            'estado' => 'activo',
            'subscription_ends_at' => $fin,
            'activo' => true,
        ]);

        return redirect()->route('suscripcion.index')
            ->with('success', "¡Pago simulado exitoso! Tu plan {$plan->nombre} está activo hasta el {$fin->format('d/m/Y')}.");
    }
}
