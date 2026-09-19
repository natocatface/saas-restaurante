<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $reservas = Reserva::with('mesa', 'cliente')
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderBy('fecha')->orderBy('hora')->paginate(12)->withQueryString();
        return view('modules.reservas.index', compact('reservas'));
    }

    public function create()
    {
        return $this->formView(new Reserva(['fecha' => now()->toDateString(), 'personas' => 2, 'estado' => 'pendiente']));
    }

    public function store(Request $request)
    {
        Reserva::create($this->validateData($request));
        return redirect()->route('reservas.index')->with('success', 'Reserva registrada.');
    }

    public function edit(Reserva $reserva)
    {
        return $this->formView($reserva);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $reserva->update($this->validateData($request));
        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada.');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return back()->with('success', 'Reserva eliminada.');
    }

    private function formView(Reserva $reserva)
    {
        return view('modules.reservas.form', [
            'reserva' => $reserva,
            'mesas' => Mesa::orderByRaw('CAST(numero AS UNSIGNED)')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'mesa_id' => 'nullable|exists:mesas,id',
            'nombre_cliente' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha' => 'required|date',
            'hora' => 'required',
            'personas' => 'required|integer|min:1',
            'estado' => 'required|in:pendiente,confirmada,cumplida,cancelada',
            'notas' => 'nullable|string',
        ]);
    }
}
