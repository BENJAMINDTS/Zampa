<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso al panel de barra a usuarios sin el permiso correspondiente.
 *
 * Delega en User::canAccessBar() como única fuente de verdad:
 * cubre tanto el rol nativo 'waiter' como el admin con is_waiter=true.
 *
 * @author SebastianBCF
 */
class EnsureCanAccessBar
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canAccessBar()) {
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
