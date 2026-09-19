<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurante;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RestauranteController extends Controller
{
    public function index(Request $request)
    {
        $restaurantes = Restaurante::with('plan')
            ->withCount('usuarios')
            ->when($request->q, fn ($qq) => $qq->where('nombre', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%"))
            ->when($request->estado, fn ($qq) => $qq->where('estado', $request->estado))
            ->latest()->paginate(15)->withQueryString();

        return view('superadmin.restaurantes.index', compact('restaurantes'));
    }

    public function show(Restaurante $restaurante)
    {
        $restaurante->loadCount('usuarios');
        $restaurante->load('plan', 'suscripciones.plan');
        $planes = Plan::orderBy('orden')->get();
        return view('superadmin.restaurantes.show', compact('restaurante', 'planes'));
    }

    public function update(Request $request, Restaurante $restaurante)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'plan_id' => 'nullable|exists:planes,id',
            'estado' => 'required|in:trial,activo,suspendido,cancelado',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo');
        $restaurante->update($data);

        return back()->with('success', 'Restaurante actualizado.');
    }

    public function toggle(Restaurante $restaurante)
    {
        if (in_array($restaurante->estado, ['suspendido', 'cancelado'])) {
            $restaurante->update(['estado' => 'activo', 'activo' => true]);
            $msg = 'Restaurante reactivado.';
        } else {
            $restaurante->update(['estado' => 'suspendido', 'activo' => false]);
            $msg = 'Restaurante suspendido.';
        }
        return back()->with('success', $msg);
    }

    /** Extiende (o inicia) el periodo de prueba sumando N días. */
    public function extenderPrueba(Request $request, Restaurante $restaurante)
    {
        $data = $request->validate(['dias' => 'required|integer|min:1|max:365']);

        $base = $restaurante->trial_ends_at && $restaurante->trial_ends_at->isFuture()
            ? $restaurante->trial_ends_at
            : now();

        $restaurante->update([
            'estado'        => 'trial',
            'activo'        => true,
            'trial_ends_at' => $base->copy()->addDays((int) $data['dias']),
        ]);

        return back()->with('success', "Prueba extendida {$data['dias']} días.");
    }

    /** Registra un pago de suscripción manual y activa el restaurante. */
    public function registrarPago(Request $request, Restaurante $restaurante)
    {
        $data = $request->validate([
            'plan_id'     => 'required|exists:planes,id',
            'monto'       => 'required|numeric|min:0',
            'intervalo'   => 'required|in:mensual,anual',
            'metodo_pago' => 'required|in:efectivo,tarjeta,yape,plin,transferencia',
        ]);

        $plan = Plan::find($data['plan_id']);
        $inicio = Carbon::today();
        $fin = $data['intervalo'] === 'anual' ? $inicio->copy()->addYear() : $inicio->copy()->addMonth();

        Suscripcion::create([
            'restaurante_id' => $restaurante->id,
            'plan_id'        => $plan->id,
            'nombre_plan'    => $plan->nombre,
            'monto'          => $data['monto'],
            'intervalo'      => $data['intervalo'],
            'estado'         => 'pagado',
            'metodo_pago'    => $data['metodo_pago'],
            'referencia'     => 'MANUAL-'.strtoupper(uniqid()),
            'periodo_inicio' => $inicio->toDateString(),
            'periodo_fin'    => $fin->toDateString(),
        ]);

        $restaurante->update([
            'plan_id'              => $plan->id,
            'estado'              => 'activo',
            'activo'             => true,
            'subscription_ends_at' => $fin,
        ]);

        return back()->with('success', "Pago registrado. Suscripción activa hasta {$fin->format('d/m/Y')}.");
    }

    public function destroy(Restaurante $restaurante)
    {
        $restaurante->delete();
        return redirect()->route('superadmin.restaurantes.index')->with('success', 'Restaurante eliminado con todos sus datos.');
    }
}
