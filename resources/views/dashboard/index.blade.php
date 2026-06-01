<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                Dashboard
            </h1>

            {{-- Selector de período --}}
            <nav aria-label="Filtro de período"
                 class="flex flex-wrap gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1 self-start sm:self-auto">
                @foreach(['today' => 'Hoy', 'week' => 'Esta semana', 'month' => 'Este mes', 'year' => 'Este año'] as $value => $label)
                    <a href="{{ route('dashboard', ['period' => $value]) }}"
                       aria-current="{{ $period === $value ? 'page' : 'false' }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                              {{ $period === $value
                                  ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm'
                                  : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('dashboard', ['period' => 'custom']) }}"
                   aria-current="{{ $period === 'custom' ? 'page' : 'false' }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          {{ $period === 'custom'
                              ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm'
                              : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                    Personalizado
                </a>
            </nav>
        </div>

        {{-- Rango personalizado --}}
        @if($period === 'custom')
            <form method="GET" action="{{ route('dashboard') }}"
                  class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-end"
                  aria-label="Seleccionar rango de fechas">
                <input type="hidden" name="period" value="custom">
                <div class="flex flex-col gap-1">
                    <label for="from" class="text-sm font-medium text-gray-700 dark:text-gray-300">Desde</label>
                    <input id="from" type="date" name="from"
                           value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           aria-required="true">
                    @error('from')
                        <p role="alert" class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label for="to" class="text-sm font-medium text-gray-700 dark:text-gray-300">Hasta</label>
                    <input id="to" type="date" name="to"
                           value="{{ $to ?? now()->endOfMonth()->format('Y-m-d') }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           aria-required="true">
                    @error('to')
                        <p role="alert" class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium
                               rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Aplicar
                </button>
            </form>
        @endif
    </x-slot>

    <main id="main-content" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ─── Uso del plan ───────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <x-plan-usage :planUsage="$planUsage" />
                </div>
            </div>

            {{-- ─── Bloque 9.1: Desglose de ingresos ─────────────────────────────── --}}
            <section aria-labelledby="income-heading">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="income-heading"
                        class="text-base font-semibold text-gray-900 dark:text-white">
                        Ingresos del período
                    </h2>
                    <a href="{{ route('manager.income', ['period' => $period] + ($period === 'custom' ? ['from' => $from, 'to' => $to] : [])) }}"
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                        Ver detalle →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- Efectivo --}}
                    <div role="region" aria-label="Total ingresos en efectivo"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💵</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Efectivo</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ number_format($summary->cash_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $summary->cash_count }}
                            pedido{{ $summary->cash_count != 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Tarjeta --}}
                    <div role="region" aria-label="Total ingresos en tarjeta"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💳</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tarjeta</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ number_format($summary->card_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $summary->card_count }}
                            pedido{{ $summary->card_count != 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Cobro partido --}}
                    <div role="region" aria-label="Total ingresos por cobro partido"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🔀</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Cobro partido</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ number_format($summary->split_cash_revenue + $summary->split_card_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $summary->split_count }}
                            pedido{{ $summary->split_count != 1 ? 's' : '' }}
                        </p>
                        @if($summary->split_count > 0)
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                💵 {{ number_format($summary->split_cash_revenue, 2, ',', '.') }}&nbsp;€
                                · 💳 {{ number_format($summary->split_card_revenue, 2, ',', '.') }}&nbsp;€
                            </p>
                        @endif
                    </div>

                    {{-- Total global --}}
                    <div role="region" aria-label="Total global cobrado en el período"
                         class="bg-indigo-600 dark:bg-indigo-700 rounded-2xl shadow-sm p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💰</span>
                            <span class="text-sm font-medium text-indigo-200">Total cobrado</span>
                        </div>
                        <p class="text-2xl font-bold text-white tabular-nums">
                            {{ number_format($grand, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-indigo-300">
                            {{ $summary->total_count }}
                            pedido{{ $summary->total_count != 1 ? 's' : '' }}
                        </p>
                    </div>

                </div>

                {{-- Propinas desglosadas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">

                    {{-- Propina en efectivo --}}
                    <div role="region" aria-label="Total propinas en efectivo"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🎁</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                <span aria-hidden="true">💵</span> Efectivo
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400 tabular-nums">
                            {{ number_format($summary->cash_tip_revenue + $summary->split_cash_tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Propinas recibidas en mano</p>
                    </div>

                    {{-- Propina en tarjeta --}}
                    <div role="region" aria-label="Total propinas en tarjeta"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🎁</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                <span aria-hidden="true">💳</span> Tarjeta
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                            {{ number_format($summary->card_tip_revenue + $summary->split_card_tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Propinas cobradas con Stripe</p>
                    </div>

                </div>
            </section>

            {{-- ─── Bloque 9.2: Mesa que más ingresos genera ────────────────────── --}}
            <section aria-labelledby="top-table-heading">
                <h2 id="top-table-heading"
                    class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                    Mesa más rentable del período
                </h2>

                @if($topTable)
                    <div role="region" aria-label="Mesa con mayor ingreso del período"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-6
                                flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div class="flex items-center gap-4">
                            <span class="text-4xl" aria-hidden="true">🏆</span>
                            <div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $topTable->table_name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $topTable->table_order_count }}
                                    pedido{{ $topTable->table_order_count != 1 ? 's' : '' }} cobrado{{ $topTable->table_order_count != 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                {{ number_format($topTable->table_revenue, 2, ',', '.') }}&nbsp;€
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">ingresos totales</p>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 px-6 py-10 text-center">
                        <p class="text-gray-400 dark:text-gray-500 text-sm">
                            Sin pedidos cobrados en este período.
                        </p>
                    </div>
                @endif
            </section>

            {{-- ─── Bloque 9.3: Platos más pedidos ────────────────────────────── --}}
            <section aria-labelledby="top-products-heading">
                <h2 id="top-products-heading"
                    class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                    Platos más pedidos del período
                </h2>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                            border border-gray-200 dark:border-gray-700">

                    @if($topProducts->isEmpty())
                        <div class="px-6 py-10 text-center">
                            <p class="text-gray-400 dark:text-gray-500 text-sm">
                                Sin pedidos cobrados en este período.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                                   aria-label="Ranking de platos más pedidos">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col"
                                            class="px-5 py-3 text-left text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10">
                                            #
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-left text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Plato
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Veces pedido
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Ingresos
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($topProducts as $i => $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                            <td class="px-5 py-3 text-sm font-medium text-gray-400 dark:text-gray-500 tabular-nums">
                                                {{ $i + 1 }}
                                            </td>
                                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $product->product_name }}
                                            </td>
                                            <td class="px-5 py-3 text-sm text-right text-gray-700 dark:text-gray-300 tabular-nums">
                                                {{ $product->times_ordered }}
                                            </td>
                                            <td class="px-5 py-3 text-sm text-right font-semibold
                                                       text-gray-900 dark:text-white tabular-nums">
                                                {{ number_format($product->product_revenue, 2, ',', '.') }}&nbsp;€
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ─── Bloque 9.4: Horas punta y ticket medio ────────────────────── --}}
            <section aria-labelledby="peak-hours-heading">
                <h2 id="peak-hours-heading"
                    class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                    Horas punta y ticket medio
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    {{-- Ticket medio --}}
                    <div role="region" aria-label="Ticket medio del período"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🧾</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket medio</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ number_format($avgTicket, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Por pedido cobrado</p>
                    </div>

                    {{-- Horas punta --}}
                    <div role="region" aria-label="Horas punta del período"
                         class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xl" aria-hidden="true">⏰</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horas punta (Top 3)</span>
                        </div>

                        @if($peakHours->isEmpty())
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                Sin pedidos cobrados en este período.
                            </p>
                        @else
                            @php $maxCount = $peakHours->max('order_count'); @endphp
                            <div class="space-y-3">
                                @foreach($peakHours as $slot)
                                    @php
                                        $h    = (int) $slot->hour;
                                        $next = $h === 23 ? '00' : str_pad($h + 1, 2, '0', STR_PAD_LEFT);
                                        $label = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00 – ' . $next . ':00';
                                        $pct   = $maxCount > 0 ? round(($slot->order_count / $maxCount) * 100) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $label }}
                                            </span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums">
                                                {{ $slot->order_count }}
                                                pedido{{ $slot->order_count != 1 ? 's' : '' }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2"
                                             role="progressbar"
                                             aria-valuenow="{{ $slot->order_count }}"
                                             aria-valuemin="0"
                                             aria-valuemax="{{ $maxCount }}"
                                             aria-label="{{ $label }}: {{ $slot->order_count }} pedidos">
                                            <div class="bg-indigo-500 dark:bg-indigo-400 h-2 rounded-full transition-all"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </section>

        </div>
    </main>
</x-app-layout>
