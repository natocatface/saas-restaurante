<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = User::when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%"))
            ->latest()->paginate(12)->withQueryString();
        return view('modules.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('modules.usuarios.form', ['usuario' => new User(['role' => 'mesero', 'activo' => true])]);
    }

    public function store(Request $request)
    {
        $rest = \App\Models\Restaurante::actual();
        if ($rest && $rest->limiteAlcanzado('usuarios', User::count())) {
            return back()->withInput()->with('error', 'Alcanzaste el límite de usuarios de tu plan. Mejora tu plan en Suscripción para agregar más.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'role' => 'required|in:admin,cajero,mesero,cocina',
            'telefono' => 'nullable|string|max:30',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = $request->boolean('activo');
        User::create($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $usuario)
    {
        return view('modules.usuarios.form', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'role' => 'required|in:admin,cajero,mesero,cocina',
            'telefono' => 'nullable|string|max:30',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['activo'] = $request->boolean('activo');
        $usuario->update($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $usuario->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}
