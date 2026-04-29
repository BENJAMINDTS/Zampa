<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Dashboard principal del panel de superadministración.
 *
 * @author BenjaminDTS
 */
class SuperAdminDashboardController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view('superadmin.dashboard');
    }
}
