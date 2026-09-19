<?php

namespace App\Http\Middleware;

use App\Models\Restaurante;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso a la app del tenant si su suscripción no está vigente
 * (trial vencido, suspendido o cancelado), redirigiendo a la página de suscripción.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurante = Restaurante::actual();

        if ($restaurante && ! $restaurante->vigente()) {
            return redirect()->route('suscripcion.index')
                ->with('error', 'Tu suscripción no está activa. Renueva tu plan para seguir usando el sistema.');
        }

        return $next($request);
    }
}
