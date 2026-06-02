<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $from   = $request->query('from');
        $to     = $request->query('to');

        if ($period === 'custom' && ($from !== null || $to !== null)) {
            $request->validate([
                'from' => ['required', 'date'],
                'to'   => ['required', 'date', 'after_or_equal:from'],
            ]);
        }

        [$start, $end] = match ($period) {
            'today'  => [now()->startOfDay(),   now()->endOfDay()],
            'week'   => [now()->startOfWeek(),  now()->endOfWeek()],
            'year'   => [now()->startOfYear(),  now()->endOfYear()],
            'custom' => [
                Carbon::parse($from ?? now()->startOfMonth())->startOfDay(),
                Carbon::parse($to   ?? now()->endOfMonth())->endOfDay(),
            ],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $ownerUserId = Auth::user()->ownerUserId();

        $base = Order::query()
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->where('tables.user_id', $ownerUserId)
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.updated_at', [$start, $end]);

        $nonSplitRaw = (clone $base)
            ->whereIn('orders.payment_method', ['cash', 'card'])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN orders.payment_method = 'cash' THEN orders.total ELSE 0 END), 0) as cash_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.total ELSE 0 END), 0) as card_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'cash' THEN orders.tip  ELSE 0 END), 0)  as cash_tip_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.tip  ELSE 0 END), 0)  as card_tip_revenue,
                SUM(CASE WHEN orders.payment_method = 'cash' THEN 1 ELSE 0 END)                         as cash_count,
                SUM(CASE WHEN orders.payment_method = 'card' THEN 1 ELSE 0 END)                         as card_count
            ")
            ->first();

        $splitRaw = DB::table('order_payments')
            ->join('orders', 'order_payments.order_id', '=', 'orders.id')
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->where('tables.user_id', $ownerUserId)
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN order_payments.method = 'cash' THEN order_payments.amount ELSE 0 END), 0) as split_cash_revenue,
                COALESCE(SUM(CASE WHEN order_payments.method = 'card' THEN order_payments.amount ELSE 0 END), 0) as split_card_revenue,
                COALESCE(SUM(CASE WHEN order_payments.method = 'cash' THEN order_payments.tip    ELSE 0 END), 0) as split_cash_tip_revenue,
                COALESCE(SUM(CASE WHEN order_payments.method = 'card' THEN order_payments.tip    ELSE 0 END), 0) as split_card_tip_revenue,
                COUNT(DISTINCT order_payments.order_id)                                                          as split_count
            ")
            ->first();

        $summary = (object) [
            'cash_revenue'           => (float) ($nonSplitRaw->cash_revenue ?? 0),
            'card_revenue'           => (float) ($nonSplitRaw->card_revenue ?? 0),
            'cash_tip_revenue'       => (float) ($nonSplitRaw->cash_tip_revenue ?? 0),
            'card_tip_revenue'       => (float) ($nonSplitRaw->card_tip_revenue ?? 0),
            'cash_count'             => (int)   ($nonSplitRaw->cash_count ?? 0),
            'card_count'             => (int)   ($nonSplitRaw->card_count ?? 0),
            'split_cash_revenue'     => (float) ($splitRaw->split_cash_revenue ?? 0),
            'split_card_revenue'     => (float) ($splitRaw->split_card_revenue ?? 0),
            'split_cash_tip_revenue' => (float) ($splitRaw->split_cash_tip_revenue ?? 0),
            'split_card_tip_revenue' => (float) ($splitRaw->split_card_tip_revenue ?? 0),
            'split_count'            => (int)   ($splitRaw->split_count ?? 0),
            'total_count'            => (int) ($nonSplitRaw->cash_count ?? 0)
                                      + (int) ($nonSplitRaw->card_count ?? 0)
                                      + (int) ($splitRaw->split_count ?? 0),
        ];

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

        return view('manager.income', compact('period', 'from', 'to', 'summary', 'orders'));
    }
}
