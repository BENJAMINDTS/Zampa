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

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

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

                    {{-- Propinas --}}
                    <div role="region" aria-label="Total propinas en tarjeta"
                         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                                border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">🎁</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Propinas</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ number_format($summary->tip_revenue, 2, ',', '.') }}&nbsp;€
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Solo pagos con tarjeta</p>
                    </div>

                    {{-- Total global --}}
                    @php $grand = $summary->cash_revenue + $summary->card_revenue + $summary->tip_revenue; @endphp
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

            {{-- ─── Bloque 9.3: Platos más pedidos (pendiente) ─────────────────── --}}
            {{-- Se implementará en el Bloque 9.3 --}}

            {{-- ─── Bloque 9.4: Horas punta y ticket medio (pendiente) ────────── --}}
            {{-- Se implementará en el Bloque 9.4 --}}

        </div>
    </main>
</x-app-layout>
