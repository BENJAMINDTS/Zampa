<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso al panel si el negocio está desactivado o su admin fue eliminado.
 *
 * - Admins inactivos (active = false): sesión cerrada, redirige a login.
 * - Staff cuyo admin fue soft-deleted o desactivado: sesión cerrada, redirige a login.
 * - Superadmins: no se ven afectados.
 *
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class EnsureBusinessIsActive
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isAdmin() && ! $user->isActive()) {
            return $this->forceLogout($request, 'Tu cuenta está desactivada. Contacta con el administrador.');
        }

        if ($user->isStaff()) {
            $admin = \App\Models\User::find($user->admin_id);

            if ($admin === null || ! $admin->isActive()) {
                return $this->forceLogout($request, 'El negocio al que perteneces ha sido desactivado o eliminado.');
            }
        }

        return $next($request);
    }

    /**
     * @param  Request  $request
     * @param  string   $message
     * @return Response
     */
    private function forceLogout(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['email' => $message]);
    }
}
