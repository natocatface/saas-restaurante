<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::orderByRaw('CAST(numero AS UNSIGNED)')->get()->groupBy('zona');
        $resumen = [
            'libre' => Mesa::where('estado', 'libre')->count(),
            'ocupada' => Mesa::where('estado', 'ocupada')->count(),
            'reservada' => Mesa::where('estado', 'reservada')->count(),
            'cuenta' => Mesa::where('estado', 'cuenta')->count(),
        ];
        return view('modules.mesas.index', compact('mesas', 'resumen'));
    }

    public function create()
    {
        return view('modules.mesas.form', ['mesa' => new Mesa(['capacidad' => 4, 'estado' => 'libre'])]);
    }

    public function store(Request $request)
    {
        $rest = \App\Models\Restaurante::actual();
        if ($rest && $rest->limiteAlcanzado('mesas', Mesa::count())) {
            return back()->withInput()->with('error', 'Alcanzaste el límite de mesas de tu plan. Mejora tu plan en Suscripción para agregar más.');
        }
        Mesa::create($this->validateData($request));
        return redirect()->route('mesas.index')->with('success', 'Mesa creada correctamente.');
    }

    public function edit(Mesa $mesa)
    {
        return view('modules.mesas.form', compact('mesa'));
    }

    public function update(Request $request, Mesa $mesa)
    {
        $mesa->update($this->validateData($request));
        return redirect()->route('mesas.index')->with('success', 'Mesa actualizada.');
    }

    public function destroy(Mesa $mesa)
    {
        $mesa->delete();
        return back()->with('success', 'Mesa eliminada.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'numero' => 'required|string|max:20',
            'nombre' => 'nullable|string|max:50',
            'capacidad' => 'required|integer|min:1|max:50',
            'zona' => 'required|string|max:50',
            'estado' => 'required|in:libre,ocupada,reservada,cuenta',
        ]);
    }
}
