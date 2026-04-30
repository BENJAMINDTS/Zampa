<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gestión de planes SaaS desde el panel del superadmin.
 *
 * @author BenjaminDTS
 */
class SuperAdminPlanController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        $plans = Plan::withCount('users')->paginate(15);

        return view('superadmin.plans.index', compact('plans'));
    }

    /**
     * @return View
     */
    public function create(): View
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        return view('superadmin.plans.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'name'       => 'required|string|max:255|unique:plans,name',
            'price'      => 'required|numeric|min:0',
            'max_tables' => 'required|integer|min:1|max:500',
        ]);

        Plan::create($request->only('name', 'price', 'max_tables'));

        return redirect()->route('superadmin.plans.index')
                         ->with('success', 'Plan creado correctamente.');
    }

    /**
     * @param Plan $plan
     * @return View
     */
    public function edit(Plan $plan): View
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        return view('superadmin.plans.edit', compact('plan'));
    }

    /**
     * @param Request $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'name'       => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'price'      => 'required|numeric|min:0',
            'max_tables' => 'required|integer|min:1|max:500',
        ]);

        $plan->update($request->only('name', 'price', 'max_tables'));

        return redirect()->route('superadmin.plans.index')
                         ->with('success', 'Plan actualizado correctamente.');
    }

    /**
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        abort_if(!Auth::user()->isSuperAdmin(), 403);

        if ($plan->hasActiveBusinesses()) {
            return redirect()->route('superadmin.plans.index')
                             ->with('error', 'No se puede eliminar un plan con negocios asignados.');
        }

        $plan->delete();

        return redirect()->route('superadmin.plans.index')
                         ->with('success', 'Plan eliminado correctamente.');
    }
}
