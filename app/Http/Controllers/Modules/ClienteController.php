<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::withCount('pedidos')
            ->when($request->q, fn ($q) => $q->where('nombre', 'like', "%{$request->q}%")->orWhere('documento', 'like', "%{$request->q}%"))
            ->latest()->paginate(12)->withQueryString();
        return view('modules.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('modules.clientes.form', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        Cliente::create($this->validateData($request));
        return redirect()->route('clientes.index')->with('success', 'Cliente registrado.');
    }

    public function edit(Cliente $cliente)
    {
        return view('modules.clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($this->validateData($request));
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return back()->with('success', 'Cliente eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'puntos' => 'nullable|integer|min:0',
        ]);
    }
}
