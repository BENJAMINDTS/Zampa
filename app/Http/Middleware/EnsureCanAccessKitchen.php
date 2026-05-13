<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso al panel de cocina a usuarios sin el permiso correspondiente.
 *
 * Delega en User::canAccessKitchen() como única fuente de verdad:
 * cubre tanto el rol nativo 'kitchen' como el admin con is_kitchen=true.
 *
 * @author SebastianBCF
 */
class EnsureCanAccessKitchen
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canAccessKitchen()) {
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
