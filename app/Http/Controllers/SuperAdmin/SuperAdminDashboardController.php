<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
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
        $totalBusinesses  = User::where('role', 'admin')->count();
        $activeBusinesses = User::where('role', 'admin')->where('active', true)->count();
        $totalPlans       = Plan::count();

        return view('superadmin.dashboard', compact(
            'totalBusinesses',
            'activeBusinesses',
            'totalPlans',
        ));
    }
}
