<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe acceso exclusivamente a usuarios con rol superadmin.
 *
 * @author BenjaminDTS
 */
class EnsureSuperAdmin
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Acceso restringido al superadmin.');
        }

        return $next($request);
    }
}
