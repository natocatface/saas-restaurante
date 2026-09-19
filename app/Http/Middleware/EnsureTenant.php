<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza que el usuario pertenece a un restaurante (tenant).
 * El super-admin global es redirigido a su propio panel.
 */
class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if (! $user || ! $user->restaurante_id) {
            abort(403, 'Tu cuenta no está asociada a ningún restaurante.');
        }

        return $next($request);
    }
}
