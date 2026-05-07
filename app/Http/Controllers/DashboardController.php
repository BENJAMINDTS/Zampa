<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($period === 'custom') {
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
            ->first();

        return view('dashboard.index', compact('period', 'from', 'to', 'summary'));
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
