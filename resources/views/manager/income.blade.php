<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                Desglose de ingresos
            </h1>

            {{-- Filtros de período --}}
            <nav aria-label="Filtro de período"
                 class="flex flex-wrap gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1 self-start sm:self-auto">
                @foreach(['today' => 'Hoy', 'week' => 'Esta semana', 'month' => 'Este mes', 'year' => 'Este año'] as $value => $label)
                    <a href="{{ route('manager.income', ['period' => $value]) }}"
                       aria-current="{{ $period === $value ? 'page' : 'false' }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                              {{ $period === $value
                                  ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm'
                                  : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('manager.income', ['period' => 'custom']) }}"
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
            <form method="GET" action="{{ route('manager.income') }}"
                  class="flex flex-wrap gap-3 items-end"
                  aria-label="Seleccionar rango de fechas">
                <input type="hidden" name="period" value="custom">
                <div class="flex flex-col gap-1">
                    <label for="income-from" class="text-xs font-medium text-gray-600 dark:text-gray-400">Desde</label>
                    <input id="income-from" type="date" name="from"
                           value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           aria-required="true">
                </div>
                <div class="flex flex-col gap-1">
                    <label for="income-to" class="text-xs font-medium text-gray-600 dark:text-gray-400">Hasta</label>
                    <input id="income-to" type="date" name="to"
                           value="{{ $to ?? now()->endOfMonth()->format('Y-m-d') }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           aria-required="true">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Tarjetas de resumen --}}
            <section aria-labelledby="summary-heading">
                <h2 id="summary-heading" class="sr-only">Resumen de ingresos</h2>
                @php
                    $grand = $summary->cash_revenue + $summary->card_revenue
                           + $summary->cash_tip_revenue + $summary->card_tip_revenue
                           + $summary->split_cash_revenue + $summary->split_card_revenue
                           + $summary->split_cash_tip_revenue + $summary->split_card_tip_revenue;
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- Efectivo --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Efectivo</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($summary->cash_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $summary->cash_count }}
                            pedido{{ $summary->cash_count != 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Tarjeta --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tarjeta</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($summary->card_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $summary->card_count }}
                            pedido{{ $summary->card_count != 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Cobro partido --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Cobro partido</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
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

                    {{-- Total cobrado --}}
                    <div class="bg-indigo-600 dark:bg-indigo-700 rounded-2xl shadow-sm p-5
                                hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-indigo-200">Total cobrado</span>
                        </div>
                        <p class="text-2xl font-bold text-white">
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Efectivo</span>
                        </div>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">
                            {{ number_format($summary->cash_tip_revenue + $summary->split_cash_tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Propinas recibidas en mano</p>
                    </div>

                    {{-- Propina en tarjeta --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5
                                hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">Tarjeta</span>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ number_format($summary->card_tip_revenue + $summary->split_card_tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Propinas cobradas con Stripe</p>
                    </div>

                </div>
            </section>

            {{-- Tabla de pedidos cobrados --}}
            <section aria-labelledby="orders-heading">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                            border border-gray-200 dark:border-gray-700">

                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 id="orders-heading"
                            class="text-base font-semibold text-gray-900 dark:text-white">
                            Pedidos cobrados
                        </h2>
                    </div>

                    @if($orders->isEmpty())
                        <div class="px-5 py-14 text-center">
                            <p class="text-gray-400 dark:text-gray-500 text-sm">
                                No hay pedidos cobrados en este período.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                                   aria-label="Listado de pedidos cobrados">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col"
                                            class="px-5 py-3 text-left text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Fecha y hora
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-left text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Mesa
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-left text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Método
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Pedido
                                        </th>
                                        <th scope="col"
                                            class="hidden sm:table-cell px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Propina
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total cobrado
                                        </th>
                                        <th scope="col"
                                            class="px-5 py-3 text-right text-xs font-medium
                                                   text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Ticket
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($orders as $order)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                <time datetime="{{ $order->updated_at->toIso8601String() }}">
                                                    {{ $order->updated_at->format('d/m/Y H:i') }}
                                                </time>
                                            </td>
                                            <td class="px-5 py-4 text-sm font-medium
                                                       text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $order->table_name }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                @if($order->payment_method === 'card')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                        Tarjeta
                                                    </span>
                                                @elseif($order->payment_method === 'split')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                                        Partido
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                        Efectivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-sm text-right
                                                       text-gray-700 dark:text-gray-300 whitespace-nowrap tabular-nums">
                                                {{ number_format($order->total, 2, ',', '.') }}&nbsp;€
                                            </td>
                                            <td class="hidden sm:table-cell px-5 py-4 text-sm text-right
                                                       whitespace-nowrap tabular-nums">
                                                @if($order->tip > 0)
                                                    @if($order->payment_method === 'cash')
                                                        <span class="text-green-600 dark:text-green-400">
                                                            +{{ number_format($order->tip, 2, ',', '.') }}&nbsp;€
                                                        </span>
                                                    @else
                                                        {{-- card y split: la propina registrada es siempre de tarjeta --}}
                                                        <span class="text-indigo-600 dark:text-indigo-400">
                                                            +{{ number_format($order->tip, 2, ',', '.') }}&nbsp;€
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600"
                                                          aria-label="Sin propina">—</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-sm text-right font-semibold
                                                       text-gray-900 dark:text-white whitespace-nowrap tabular-nums">
                                                {{ number_format($order->total + $order->tip, 2, ',', '.') }}&nbsp;€
                                            </td>
                                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                                <a href="{{ route('ticket.reprint', $order->id) }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg
                                                          text-xs font-medium
                                                          bg-gray-100 dark:bg-gray-700
                                                          text-gray-700 dark:text-gray-300
                                                          hover:bg-indigo-100 dark:hover:bg-indigo-900/40
                                                          hover:text-indigo-700 dark:hover:text-indigo-300
                                                          transition-colors focus:outline-none
                                                          focus:ring-2 focus:ring-indigo-500"
                                                   aria-label="Reimprimir ticket del pedido #{{ $order->id }}">
                                                    <svg aria-hidden="true" class="w-3.5 h-3.5 shrink-0"
                                                         fill="none" stroke="currentColor"
                                                         stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 16v-8m0 8l-3-3m3 3l3-3M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                                                    </svg>
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($orders->hasPages())
                            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                                <nav aria-label="Paginación de pedidos cobrados">
                                    {{ $orders->links() }}
                                </nav>
                            </div>
                        @endif
                    @endif
                </div>
            </section>

        </div>
    </main>
</x-app-layout>
