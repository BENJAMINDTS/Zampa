<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * Gestión de negocios registrados en la plataforma (stub — implementado en Bloque 13.4).
 *
 * @author BenjaminDTS
 */
class SuperAdminBusinessController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        abort(501, 'Bloque 13.4 pendiente de implementación.');
    }

    /**
     * @return Response
     */
    public function create(): Response
    {
        abort(501, 'Bloque 13.4 pendiente de implementación.');
    }

    /**
     * @return Response
     */
    public function store(): Response
    {
        abort(501, 'Bloque 13.4 pendiente de implementación.');
    }

    /**
     * @param  User  $business
     * @return Response
     */
    public function destroy(User $business): Response
    {
        abort(501, 'Bloque 13.4 pendiente de implementación.');
    }

    /**
     * @param  User  $business
     * @return Response
     */
    public function toggle(User $business): Response
    {
        abort(501, 'Bloque 13.4 pendiente de implementación.');
    }
}
