<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

/**
 * Landing page pública de Zampa.
 *
 * @author BenjaminDTS
 */
class LandingController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        $plans = Plan::orderBy('price_monthly')->get();

        return view('welcome', compact('plans'));
    }
}
