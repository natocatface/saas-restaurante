<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! $request->user()->hasRole($roles)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
