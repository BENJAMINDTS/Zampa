{{-- @author AyrtonAlania --}}
<x-app-layout>
  <div
    class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10"
    x-data="kitchenPanel()"
    x-init="init()"
  >

    {{-- Cabecera --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Panel de Cocina</h2>
        <p class="text-sm text-gray-500 mt-1">
          Actualización automática cada 5 segundos &mdash;
          <span x-text="lastUpdated" class="font-medium text-indigo-600"></span>
        </p>
      </div>

      {{-- Indicador de conexión --}}
      <div class="flex items-center gap-2 text-sm">
        <span
          class="inline-block w-3 h-3 rounded-full"
          :class="polling ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
          aria-hidden="true"
        ></span>
        <span x-text="polling ? 'En vivo' : 'Sin conexión'" class="text-gray-600 dark:text-gray-400"></span>
      </div>
    </div>

    {{-- Sin comandas --}}
    <div x-show="orders.length === 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-10 text-center text-gray-400 dark:text-gray-500">
      <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <p class="text-lg font-medium">No hay comandas pendientes</p>
      <p class="text-sm mt-1">Las nuevas comandas aparecerán aquí automáticamente.</p>
    </div>

    {{-- Grid de comandas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <template x-for="order in orders" :key="order.id">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">

          {{-- Cabecera de la comanda --}}
          <div
            class="flex items-center justify-between px-4 py-3 text-white"
            :class="order.is_daily_menu ? 'bg-amber-600 dark:bg-amber-700' : 'bg-red-600 dark:bg-red-700'"
          >
            <div class="flex items-center gap-2">
                <template x-if="order.is_daily_menu">
                    <svg class="w-4 h-4 text-amber-200 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </template>
                <template x-if="!order.is_daily_menu">
                    <svg class="w-4 h-4 text-red-200 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </template>
                <span class="font-bold text-base" x-text="order.table_name"></span>
                <span
                    x-show="order.is_daily_menu"
                    class="text-xs bg-white/25 rounded px-1.5 py-0.5 font-semibold tracking-wide"
                >Menú del Día</span>
            </div>
            <span class="text-xs bg-white/20 rounded-md px-2 py-1 tabular-nums" x-text="order.created_at"></span>
          </div>

          {{-- Ítems pendientes --}}
          <ul class="divide-y divide-gray-100 dark:divide-gray-700" role="list">
            <template x-for="item in order.items" :key="item.id">
              <li class="px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                  <div class="flex-1 min-w-0">

                    {{-- Producto + cantidad --}}
                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                      <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold mr-1"
                        x-text="'×' + item.quantity"
                        aria-label="Cantidad"
                      ></span>
                      <span x-text="item.product_name"></span>
                      <span
                        x-show="item.is_daily_menu"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 ml-1"
                        aria-label="Ítem del menú del día"
                      >Menú del Día</span>
                    </p>

                    {{-- Modificaciones del cliente --}}
                    <template x-if="item.modifications && item.modifications.length > 0">
                      <ul class="mt-1.5 space-y-1" aria-label="Modificaciones del cliente">
                        <template x-for="mod in item.modifications" :key="mod.ingredient">
                          <li class="flex items-center gap-1.5 text-xs">
                            <span
                              :class="mod.action === 'remove'
                                ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800'
                                : 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800'"
                              class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md font-semibold leading-none"
                            >
                              <svg class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true">
                                <template x-if="mod.action === 'remove'">
                                  <path d="M18 6L6 18M6 6l12 12"/>
                                </template>
                                <template x-if="mod.action !== 'remove'">
                                  <path d="M12 5v14M5 12h14"/>
                                </template>
                              </svg>
                              <span x-text="mod.action === 'remove' ? 'Sin' : 'Extra'"></span>
                            </span>
                            <span class="text-gray-600 dark:text-gray-400" x-text="mod.ingredient"></span>
                          </li>
                        </template>
                      </ul>
                    </template>

                  </div>

                  {{-- Botón "Listo" --}}
                  <button
                    @click="markReady(item, order)"
                    :disabled="item.marking"
                    class="shrink-0 inline-flex items-center gap-1.5 bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 transition-colors cursor-pointer"
                    :aria-label="'Marcar como listo: ' + item.product_name"
                  >
                    <template x-if="!item.marking">
                      <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                        Listo
                      </span>
                    </template>
                    <template x-if="item.marking">
                      <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    </template>
                  </button>

                </div>
              </li>
            </template>
          </ul>

          {{-- Footer: pedido completo --}}
          <div
            x-show="order.all_ready"
            class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border-t border-green-200 dark:border-green-800 flex items-center justify-between gap-3"
            role="status"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
              <span class="text-green-700 dark:text-green-300 text-xs font-semibold">Pedido completo</span>
            </div>
            <button
              @click="markServed(order)"
              :disabled="order.serving"
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition-colors cursor-pointer"
              aria-label="Marcar pedido como servido"
            >
              <template x-if="!order.serving">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                  Servido
                </span>
              </template>
              <template x-if="order.serving">
                <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
              </template>
            </button>
          </div>

        </div>
      </template>
    </div>

  </div>

  @push('scripts')
  @php
    $ordersForJs = $orders->map(fn($o) => [
      'id'            => $o->id,
      'table_name'    => $o->table->name,
      'created_at'    => $o->created_at->format('H:i'),
      'status'        => $o->status,
      'is_daily_menu' => $o->items->isNotEmpty() && $o->items->every(fn($i) => $i->is_daily_menu),
      'all_ready'     => false,
      'serving'       => false,
      'items'      => $o->items->map(fn($i) => [
        'id'            => $i->id,
        'product_name'  => $i->product->name . ($i->variant_name ? ' — ' . $i->variant_name : ''),
        'quantity'      => $i->quantity,
        'is_daily_menu' => (bool) $i->is_daily_menu,
        'marking'       => false,
        'modifications' => $i->modifications->map(fn($m) => [
          'action'     => $m->action,
          'ingredient' => $m->ingredient->name,
        ])->values(),
      ])->values(),
    ])->values();
  @endphp
  <script id="kitchen-init" type="application/json">@json($ordersForJs)</script>
  <script id="kitchen-urls" type="application/json">@json([
    'pending'              => route('kitchen.pending'),
    'toggleAvailability'   => url('/cocina/productos/__ID__/disponibilidad'),
  ])</script>
  @endpush

</x-app-layout>
