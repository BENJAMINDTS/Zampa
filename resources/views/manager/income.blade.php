<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                Desglose de ingresos
            </h1>

            {{-- Filtros de período --}}
            <nav aria-label="Filtro de período"
                 class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1 self-start sm:self-auto">
                @foreach(['today' => 'Hoy', 'week' => 'Esta semana', 'month' => 'Este mes'] as $value => $label)
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
            </nav>
        </div>
    </x-slot>

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
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💵</span>
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
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💳</span>
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
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🔀</span>
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
                                💵 {{ number_format($summary->split_cash_revenue, 2, ',', '.') }}&nbsp;€
                                · 💳 {{ number_format($summary->split_card_revenue, 2, ',', '.') }}&nbsp;€
                            </p>
                        @endif
                    </div>

                    {{-- Total cobrado --}}
                    <div class="bg-indigo-600 dark:bg-indigo-700 rounded-2xl shadow-sm p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">💰</span>
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
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🎁</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                <span aria-hidden="true">💵</span> Efectivo
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-400">
                            {{ number_format($summary->cash_tip_revenue + $summary->split_cash_tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Propinas recibidas en mano</p>
                    </div>

                    {{-- Propina en tarjeta --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🎁</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                <span aria-hidden="true">💳</span> Tarjeta
                            </span>
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
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
                                                                 text-xs font-medium
                                                                 bg-indigo-100 dark:bg-indigo-900/40
                                                                 text-indigo-700 dark:text-indigo-300">
                                                        <span aria-hidden="true">💳</span> Tarjeta
                                                    </span>
                                                @elseif($order->payment_method === 'split')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
                                                                 text-xs font-medium
                                                                 bg-purple-100 dark:bg-purple-900/40
                                                                 text-purple-700 dark:text-purple-300">
                                                        <span aria-hidden="true">🔀</span> Partido
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
                                                                 text-xs font-medium
                                                                 bg-green-100 dark:bg-green-900/40
                                                                 text-green-700 dark:text-green-300">
                                                        <span aria-hidden="true">💵</span> Efectivo
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
