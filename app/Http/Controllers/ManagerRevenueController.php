<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Desglose de ingresos para el gerente.
 * Muestra totales por método de pago filtrados por período.
 *
 * @author SebastianBCF
 */
class ManagerRevenueController extends Controller
{
    /**
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        $period = $request->query('period', 'month');

        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(),  now()->endOfDay()],
            'week'  => [now()->startOfWeek(), now()->endOfWeek()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        // JOIN en lugar de whereHas para evitar N+1 y garantizar multitenancy en una sola query
        $base = Order::query()
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->where('tables.user_id', Auth::user()->ownerUserId())
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.updated_at', [$start, $end]);

        $summary = (clone $base)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN orders.payment_method = 'cash' THEN orders.total ELSE 0 END), 0) as cash_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.total ELSE 0 END), 0) as card_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'cash' THEN orders.tip  ELSE 0 END), 0)  as cash_tip_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.tip  ELSE 0 END), 0)  as card_tip_revenue,
                SUM(CASE WHEN orders.payment_method = 'cash' THEN 1 ELSE 0 END)                         as cash_count,
                SUM(CASE WHEN orders.payment_method = 'card' THEN 1 ELSE 0 END)                         as card_count,
                COUNT(*)                                                                                 as total_count
            ")
            ->first();

        $orders = (clone $base)
            ->select(
                'orders.id',
                'orders.total',
                'orders.tip',
                'orders.payment_method',
                'orders.updated_at',
                'tables.name as table_name',
            )
            ->orderByDesc('orders.updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('manager.income', compact('period', 'summary', 'orders'));
    }
}
