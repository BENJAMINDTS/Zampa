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
          <div class="flex items-center justify-between px-4 py-3 bg-indigo-600 text-white">
            <span class="font-bold text-base" x-text="order.table_name"></span>
            <span class="text-xs bg-white/20 rounded px-2 py-1" x-text="order.created_at"></span>
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
                      <ul class="mt-1 space-y-0.5" aria-label="Modificaciones del cliente">
                        <template x-for="mod in item.modifications" :key="mod.ingredient">
                          <li class="flex items-center gap-1 text-xs">
                            <span
                              :class="mod.action === 'remove'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-green-100 text-green-700'"
                              class="inline-block px-1.5 py-0.5 rounded font-medium"
                              x-text="mod.action === 'remove' ? 'Sin' : 'Extra'"
                            ></span>
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
                    class="shrink-0 bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 transition"
                    :aria-label="'Marcar como listo: ' + item.product_name"
                  >
                    <span x-show="!item.marking">Listo ✓</span>
                    <span x-show="item.marking">...</span>
                  </button>

                </div>
              </li>
            </template>
          </ul>

          {{-- Footer: pedido completo --}}
          <div
            x-show="order.all_ready"
            class="px-4 py-3 bg-green-50 border-t border-green-200 flex items-center justify-between gap-3"
            role="status"
          >
            <span class="text-green-700 text-xs font-semibold">¡Pedido completo! Listo para servir.</span>
            <button
              @click="markServed(order)"
              :disabled="order.serving"
              class="text-xs font-semibold px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
              aria-label="Marcar pedido como servido"
            >
              <span x-show="!order.serving">Servido ✓</span>
              <span x-show="order.serving">...</span>
            </button>
          </div>

        </div>
      </template>
    </div>

  </div>

  @push('scripts')
  @php
    $ordersForJs = $orders->map(fn($o) => [
      'id'         => $o->id,
      'table_name' => $o->table->name,
      'created_at' => $o->created_at->format('H:i'),
      'status'     => $o->status,
      'all_ready'  => false,
      'serving'    => false,
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
  <script id="kitchen-urls" type="application/json">@json(['pending' => route('kitchen.pending')])</script>
  @endpush

</x-app-layout>
