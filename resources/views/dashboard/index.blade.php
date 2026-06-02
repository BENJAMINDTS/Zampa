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

    </x-slot>

    {{-- Rango personalizado — fuera del topbar para no romper su altura fija --}}
    @if($period === 'custom')
        <div class="border-b border-gray-200 dark:border-gray-800
                    bg-white dark:bg-gray-900 px-4 sm:px-6 py-3">
            <form method="GET" action="{{ route('dashboard') }}"
                  class="flex flex-wrap gap-3 items-end"
                  aria-label="Seleccionar rango de fechas">
                <input type="hidden" name="period" value="custom">
                <div class="flex flex-col gap-1">
                    <label for="from" class="text-xs font-medium text-gray-600 dark:text-gray-400">Desde</label>
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
                    <label for="to" class="text-xs font-medium text-gray-600 dark:text-gray-400">Hasta</label>
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
        </div>
    @endif

    <main id="main-content" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ─── Uso del plan ───────────────────────────────────────────────────── --}}
            <x-plan-usage :planUsage="$planUsage" />

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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                </svg>
                            </div>
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
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ number_format($summary->split_cash_revenue, 2, ',', '.') }}&nbsp;€</span>
                                <span class="mx-1 opacity-40">/</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ number_format($summary->split_card_revenue, 2, ',', '.') }}&nbsp;€</span>
                            </p>
                        @endif
                    </div>

                    {{-- Total global --}}
                    <div role="region" aria-label="Total global cobrado en el período"
                         class="bg-indigo-600 dark:bg-indigo-700 rounded-2xl shadow-sm p-5
                                hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                </svg>
                            </div>
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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                Efectivo
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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                Tarjeta
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
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-amber-500 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
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
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
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
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                </svg>
                            </div>
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
