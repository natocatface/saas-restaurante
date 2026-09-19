<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Onboarding de un nuevo restaurante (tenant): crea el restaurante,
 * su usuario administrador y arranca un periodo de prueba.
 */
class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $planes = Plan::where('activo', true)->orderBy('orden')->get();
        $planSeleccionado = Plan::where('slug', $request->query('plan'))->first();

        return view('auth.register', compact('planes', 'planSeleccionado'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurante' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:30'],
            'plan' => ['nullable', 'string', 'exists:planes,slug'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plan = Plan::where('slug', $data['plan'] ?? null)->first()
            ?? Plan::where('activo', true)->orderBy('precio')->first();

        $user = DB::transaction(function () use ($data, $plan) {
            $restaurante = Restaurante::create([
                'nombre' => $data['restaurante'],
                'slug' => $this->slugUnico($data['restaurante']),
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'plan_id' => $plan?->id,
                'estado' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'activo' => true,
            ]);

            return User::create([
                'restaurante_id' => $restaurante->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin',
                'telefono' => $data['telefono'] ?? null,
                'activo' => true,
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', '¡Bienvenido! Tu restaurante fue creado y tienes 14 días de prueba gratis.');
    }

    private function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'restaurante';
        $slug = $base;
        $i = 1;
        while (Restaurante::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }
}
