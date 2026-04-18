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
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Panel de Cocina</h2>
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
        <span x-text="polling ? 'En vivo' : 'Sin conexión'" class="text-gray-600"></span>
      </div>
    </div>

    {{-- Sin comandas --}}
    <div x-show="orders.length === 0" class="bg-white shadow rounded-lg p-10 text-center text-gray-400">
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
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">

          {{-- Cabecera de la comanda --}}
          <div class="flex items-center justify-between px-4 py-3 bg-indigo-600 text-white">
            <span class="font-bold text-base" x-text="order.table_name"></span>
            <span class="text-xs bg-white/20 rounded px-2 py-1" x-text="order.created_at"></span>
          </div>

          {{-- Ítems pendientes --}}
          <ul class="divide-y divide-gray-100" role="list">
            <template x-for="item in order.items" :key="item.id">
              <li class="px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                  <div class="flex-1 min-w-0">

                    {{-- Producto + cantidad --}}
                    <p class="font-semibold text-gray-800 text-sm">
                      <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold mr-1"
                        x-text="'×' + item.quantity"
                        aria-label="Cantidad"
                      ></span>
                      <span x-text="item.product_name"></span>
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
                            <span class="text-gray-600" x-text="mod.ingredient"></span>
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
            class="px-4 py-2 bg-green-50 border-t border-green-200 text-green-700 text-xs font-semibold text-center"
            role="status"
          >
            ¡Pedido completo! Listo para servir.
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
      'items'      => $o->items->map(fn($i) => [
        'id'            => $i->id,
        'product_name'  => $i->product->name,
        'quantity'      => $i->quantity,
        'marking'       => false,
        'modifications' => $i->modifications->map(fn($m) => [
          'action'     => $m->action,
          'ingredient' => $m->ingredient->name,
        ])->values(),
      ])->values(),
    ])->values();
  @endphp
  <script>
    function kitchenPanel() {
      return {
        orders: @json($ordersForJs),

        polling: true,
        lastUpdated: '--:--',
        pollInterval: null,

        init() {
          this.tick();
          this.pollInterval = setInterval(() => this.tick(), 5000);
        },

        async tick() {
          try {
            const res  = await fetch('{{ route('kitchen.pending') }}', {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            // Conservar estado 'marking' de ítems que ya están en UI
            const markingIds = {};
            this.orders.forEach(o => o.items.forEach(i => { if (i.marking) markingIds[i.id] = true; }));

            this.orders = data.orders.map(o => ({
              ...o,
              all_ready: false,
              items: o.items.map(i => ({ ...i, marking: !!markingIds[i.id] })),
            }));

            this.polling    = true;
            this.lastUpdated = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
          } catch {
            this.polling = false;
          }
        },

        async markReady(item, order) {
          item.marking = true;
          try {
            const res  = await fetch(`/cocina/items/${item.id}/listo`, {
              method:  'POST',
              headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
              },
            });
            const data = await res.json();

            // Eliminar ítem marcado
            order.items = order.items.filter(i => i.id !== item.id);

            if (data.all_ready) {
              order.all_ready = true;
              // Retirar la comanda completa tras 2 s
              setTimeout(() => {
                this.orders = this.orders.filter(o => o.id !== order.id);
              }, 2000);
            }
          } catch {
            item.marking = false;
          }
        },
      };
    }
  </script>
  @endpush

</x-app-layout>
