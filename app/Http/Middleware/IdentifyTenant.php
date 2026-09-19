<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifica el tenant (restaurante) del usuario autenticado y lo registra
 * en el contenedor para que el global scope BelongsToTenant filtre los datos.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->restaurante_id) {
            app()->instance('currentTenantId', $user->restaurante_id);

            if ($restaurante = $user->restaurante) {
                app()->instance('currentTenant', $restaurante);
            }
        }

        return $next($request);
    }
}
