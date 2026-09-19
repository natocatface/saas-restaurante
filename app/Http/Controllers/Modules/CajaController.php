<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $caja = Caja::abiertaActual();
        $caja?->load(['movimientos.usuario', 'usuario']);

        $resumen = $caja ? $caja->resumen() : null;

        $historial = Caja::with('usuario', 'cerradaPor')
            ->where('estado', 'cerrada')
            ->latest('cerrada_at')
            ->limit(10)
            ->get();

        return view('modules.caja.index', compact('caja', 'resumen', 'historial'));
    }

    public function abrir(Request $request)
    {
        if (Caja::abiertaActual()) {
            return back()->with('error', 'Ya existe una caja abierta. Ciérrala antes de abrir otra.');
        }

        $data = $request->validate([
            'monto_inicial'  => 'required|numeric|min:0',
            'notas_apertura' => 'nullable|string|max:500',
        ]);

        Caja::create([
            'user_id'        => $request->user()->id,
            'estado'         => 'abierta',
            'monto_inicial'  => $data['monto_inicial'],
            'notas_apertura' => $data['notas_apertura'] ?? null,
            'abierta_at'     => now(),
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja abierta correctamente.');
    }

    public function movimiento(Request $request)
    {
        $caja = Caja::abiertaActual();
        if (! $caja) {
            return back()->with('error', 'No hay una caja abierta.');
        }

        $data = $request->validate([
            'tipo'        => 'required|in:ingreso,egreso',
            'concepto'    => 'required|string|max:255',
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,yape,plin,transferencia',
        ]);

        $caja->movimientos()->create([
            'user_id'     => $request->user()->id,
            'tipo'        => $data['tipo'],
            'concepto'    => $data['concepto'],
            'monto'       => $data['monto'],
            'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
        ]);

        return back()->with('success', 'Movimiento registrado.');
    }

    public function cerrar(Request $request)
    {
        $caja = Caja::abiertaActual();
        if (! $caja) {
            return back()->with('error', 'No hay una caja abierta.');
        }

        $data = $request->validate([
            'monto_contado' => 'required|numeric|min:0',
            'notas_cierre'  => 'nullable|string|max:500',
        ]);

        $resumen  = $caja->resumen();
        $esperado = $resumen['efectivo_esperado'];

        $caja->update([
            'estado'            => 'cerrada',
            'cerrada_por'       => $request->user()->id,
            'efectivo_esperado' => $esperado,
            'monto_contado'     => $data['monto_contado'],
            'diferencia'        => round($data['monto_contado'] - $esperado, 2),
            'notas_cierre'      => $data['notas_cierre'] ?? null,
            'cerrada_at'        => now(),
        ]);

        return redirect()->route('caja.show', $caja)->with('success', 'Caja cerrada. Aquí está el arqueo.');
    }

    public function show(Caja $caja)
    {
        $caja->load(['movimientos.usuario', 'usuario', 'cerradaPor']);
        $resumen = $caja->resumen();

        return view('modules.caja.show', compact('caja', 'resumen'));
    }
}
