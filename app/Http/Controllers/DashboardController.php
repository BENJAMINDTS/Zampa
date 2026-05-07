<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel principal del gerente con desglose de ingresos y métricas del negocio.
 *
 * @author BenjaminDTS
 */
class DashboardController extends Controller
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

        [$start, $end] = $this->resolveDateRange($period, $from, $to);

        $ownerUserId = Auth::user()->ownerUserId();

        $base = Order::query()
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->where('tables.user_id', $ownerUserId)
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.updated_at', [$start, $end]);

        $summary = (clone $base)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN orders.payment_method = 'cash' THEN orders.total ELSE 0 END), 0) as cash_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.total ELSE 0 END), 0) as card_revenue,
                COALESCE(SUM(CASE WHEN orders.payment_method = 'card' THEN orders.tip  ELSE 0 END), 0)  as tip_revenue,
                SUM(CASE WHEN orders.payment_method = 'cash' THEN 1 ELSE 0 END)                         as cash_count,
                SUM(CASE WHEN orders.payment_method = 'card' THEN 1 ELSE 0 END)                         as card_count,
                COUNT(*)                                                                                 as total_count
            ")
            ->first() ?? (object) [
                'cash_revenue' => 0, 'card_revenue' => 0, 'tip_revenue'  => 0,
                'cash_count'   => 0, 'card_count'   => 0, 'total_count'  => 0,
            ];

        $topTable = (clone $base)
            ->select('tables.name as table_name')
            ->selectRaw('SUM(orders.total) as table_revenue, COUNT(*) as table_order_count')
            ->groupBy('tables.id', 'tables.name')
            ->orderByDesc('table_revenue')
            ->limit(1)
            ->first();

        $topProducts = (clone $base)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('categories.name', '!=', 'Tapas')
            ->select('products.id', 'products.name as product_name')
            ->selectRaw('SUM(order_items.quantity) as times_ordered, SUM(order_items.quantity * order_items.price) as product_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('times_ordered')
            ->limit(10)
            ->get();

        $hourExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', orders.updated_at) AS INTEGER)"
            : 'HOUR(orders.updated_at)';

        $peakHours = (clone $base)
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as order_count")
            ->groupByRaw($hourExpr)
            ->orderByDesc('order_count')
            ->limit(3)
            ->get();

        $grand     = (float) $summary->cash_revenue + (float) $summary->card_revenue + (float) $summary->tip_revenue;
        $avgTicket = $summary->total_count > 0
            ? round($grand / $summary->total_count, 2)
            : 0.0;

        return view('dashboard.index', compact('period', 'from', 'to', 'summary', 'topTable', 'topProducts', 'peakHours', 'avgTicket', 'grand'));
    }

    /**
     * @param  string       $period
     * @param  string|null  $from
     * @param  string|null  $to
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(string $period, ?string $from, ?string $to): array
    {
        return match ($period) {
            'today'  => [now()->startOfDay(),   now()->endOfDay()],
            'week'   => [now()->startOfWeek(),  now()->endOfWeek()],
            'year'   => [now()->startOfYear(),  now()->endOfYear()],
            'custom' => [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
