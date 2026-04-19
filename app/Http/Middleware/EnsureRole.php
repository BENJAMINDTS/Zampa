<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para restringir acceso por rol de usuario.
 *
 * Uso en rutas: ->middleware('role:kitchen,admin')
 *
 * @author AyrtonAlania
 */
class EnsureRole
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string   ...$roles  Roles permitidos (ej: 'kitchen', 'admin')
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array($request->user()?->role, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
