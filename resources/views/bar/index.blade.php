{{-- @author SebastianBCF --}}
<x-app-layout>

  {{-- Solicitudes de cuenta: polling cada 15 segundos --}}
  <div
    x-data="billRequestPolling()"
    x-init="init()"
    class="max-w-6xl mx-auto px-4 sm:px-6 pt-4"
    role="region"
    aria-label="Solicitudes de cuenta"
  >
    <template x-for="order in billOrders" :key="order.id">
      <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-indigo-50 border border-indigo-400 rounded-lg px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex items-center gap-2 min-w-0">
          <span class="text-indigo-800 font-medium text-sm">
            &#128179; <strong x-text="order.table"></strong> &mdash; solicita la cuenta
          </span>
          <span
            x-show="order.payment_method === 'cash'"
            class="inline-flex items-center gap-1 text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
            aria-label="Paga en efectivo"
          >&#128181; Efectivo</span>
          <span
            x-show="order.payment_method === 'card'"
            class="inline-flex items-center gap-1 text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"
            aria-label="Paga con tarjeta"
          >&#128179; Tarjeta</span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <template x-if="order.payment_method === 'cash'">
            <button
              @click="cashPayment(order)"
              :disabled="order.paying"
              class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
              :aria-label="'Cobrar en efectivo: ' + order.table"
            >
              <span x-show="!order.paying">&#128181; Cobrar en efectivo</span>
              <span x-show="order.paying">...</span>
            </button>
          </template>
          <template x-if="order.payment_method === 'card'">
            <button
              disabled
              class="bg-indigo-200 text-indigo-500 text-xs font-semibold px-3 py-1.5 rounded cursor-not-allowed"
              :aria-label="'Cobro con tarjeta pendiente: ' + order.table"
            >&#128179; Cobrar con tarjeta</button>
          </template>
          <button
            @click="dismiss(order.id)"
            class="bg-white hover:bg-indigo-100 text-indigo-700 border border-indigo-300 text-xs font-semibold px-3 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition"
            :aria-label="'Ignorar solicitud de cuenta: ' + order.table"
          >
            Ignorar
          </button>
        </div>
      </div>
    </template>
  </div>

  {{-- Notificaciones al camarero: polling cada 20 segundos --}}
  <div
    x-data="notificationPolling()"
    x-init="init()"
    class="max-w-6xl mx-auto px-4 sm:px-6 pt-2"
    role="region"
    aria-label="Notificaciones de comandas listas"
  >
    <template x-for="order in readyOrders" :key="order.id">
      <div
        class="flex items-center justify-between bg-green-50 border border-green-400 rounded-lg px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <span class="text-green-800 font-medium text-sm">
          &#128276; <strong x-text="order.table"></strong> &mdash; lista para servir
        </span>
        <button
          @click="dismiss(order.id)"
          class="ml-4 shrink-0 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
          :aria-label="'Confirmar comanda lista: ' + order.table"
        >
          &#10003; Confirmado
        </button>
      </div>
    </template>
  </div>

  <div
    class="max-w-6xl mx-auto px-4 sm:px-6 py-6"
    x-data="barPanel()"
    x-init="init()"
  >

    {{-- Cabecera --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Panel de Barra</h2>
        <p class="text-sm text-gray-500 mt-1">
          Actualización automática cada 5 segundos &mdash;
          <span x-text="lastUpdated" class="font-medium text-amber-600"></span>
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

    {{-- Sin ítems pendientes --}}
    <div x-show="orders.length === 0" class="bg-white shadow rounded-lg p-10 text-center text-gray-400">
      <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <p class="text-lg font-medium">No hay bebidas pendientes</p>
      <p class="text-sm mt-1">Las nuevas comandas aparecerán aquí automáticamente.</p>
    </div>

    {{-- Grid de comandas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <template x-for="order in orders" :key="order.id">
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">

          {{-- Cabecera de la comanda --}}
          <div class="flex items-center justify-between px-4 py-3 bg-amber-500 text-white">
            <span class="font-bold text-base" x-text="order.table_name"></span>
            <span class="text-xs bg-white/20 rounded px-2 py-1" x-text="order.created_at"></span>
          </div>

          {{-- Ítems pendientes --}}
          <ul class="divide-y divide-gray-100" role="list">
            <template x-for="item in order.items" :key="item.id">
              <li class="px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">
                      <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-bold mr-1"
                        x-text="'×' + item.quantity"
                        aria-label="Cantidad"
                      ></span>
                      <span x-text="item.product_name"></span>
                    </p>
                  </div>

                  {{-- Botón "Servido" --}}
                  <button
                    @click="markReady(item, order)"
                    :disabled="item.marking"
                    class="shrink-0 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 transition"
                    :aria-label="'Marcar como servido: ' + item.product_name"
                  >
                    <span x-show="!item.marking">Listo ✓</span>
                    <span x-show="item.marking">...</span>
                  </button>

                </div>
              </li>
            </template>
          </ul>

          {{-- Footer: comanda completa --}}
          <div
            x-show="order.all_ready"
            class="px-4 py-3 bg-green-50 border-t border-green-200"
            role="status"
          >
            <span class="text-green-700 text-xs font-semibold">¡Pedido completo! Listo para servir.</span>
          </div>

        </div>
      </template>
    </div>

  </div>

  @push('scripts')
  <script>
    function billRequestPolling() {
      return {
        billOrders: [],

        init() {
          this.poll();
          setInterval(() => this.poll(), 15000);
        },

        async poll() {
          try {
            const res  = await fetch('{{ route('notifications.bill.requests') }}', {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            const payingIds = {};
            this.billOrders.forEach(o => { if (o.paying) payingIds[o.id] = true; });
            this.billOrders = data.orders.map(o => ({ ...o, paying: !!payingIds[o.id] }));
          } catch {
            // sin conexión — silencioso
          }
        },

        async dismiss(id) {
          const url = '{{ route('notifications.bill.dismiss', ['order' => '__ID__']) }}'.replace('__ID__', id);
          await fetch(url, {
            method:  'PATCH',
            headers: {
              'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
              'X-Requested-With': 'XMLHttpRequest',
            },
          });
          this.billOrders = this.billOrders.filter(o => o.id !== id);
        },

        async cashPayment(order) {
          order.paying = true;
          try {
            const url = '{{ url('/payments') }}/' + order.id + '/cash';
            const res = await fetch(url, {
              method:  'POST',
              headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
              },
            });
            if (res.ok) {
              this.billOrders = this.billOrders.filter(o => o.id !== order.id);
            }
          } catch {
            order.paying = false;
          }
        },
      };
    }
  </script>
  <script>
    function notificationPolling() {
      return {
        readyOrders: [],

        init() {
          this.poll();
          setInterval(() => this.poll(), 20000);
        },

        async poll() {
          try {
            const res  = await fetch('{{ route('notifications.ready') }}', {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.readyOrders = data.orders;
          } catch {
            // sin conexión — silencioso, el barPanel ya gestiona el indicador
          }
        },

        async dismiss(id) {
          const url = '{{ route('notifications.dismiss', ['order' => '__ID__']) }}'.replace('__ID__', id);
          await fetch(url, {
            method:  'PATCH',
            headers: {
              'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
              'X-Requested-With': 'XMLHttpRequest',
            },
          });
          this.readyOrders = this.readyOrders.filter(o => o.id !== id);
        },
      };
    }
  </script>
  @php
    $ordersForJs = $orders->map(fn($o) => [
      'id'         => $o->id,
      'table_name' => $o->table->name,
      'created_at' => $o->created_at->format('H:i'),
      'status'     => $o->status,
      'all_ready'  => false,
      'items'      => $o->items->map(fn($i) => [
        'id'           => $i->id,
        'product_name' => $i->product->name,
        'quantity'     => $i->quantity,
        'marking'      => false,
      ])->values(),
    ])->values();
  @endphp
  <script>
    function barPanel() {
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
            const res  = await fetch('{{ route('bar.pending') }}', {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            const markingIds = {};
            this.orders.forEach(o => o.items.forEach(i => { if (i.marking) markingIds[i.id] = true; }));

            this.orders = data.orders.map(o => ({
              ...o,
              all_ready: false,
              items: o.items.map(i => ({ ...i, marking: !!markingIds[i.id] })),
            }));

            this.polling     = true;
            this.lastUpdated = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
          } catch {
            this.polling = false;
          }
        },

        async markReady(item, order) {
          item.marking = true;
          try {
            const res = await fetch(`{{ url('/bar/items') }}/${item.id}`, {
              method:  'PATCH',
              headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
              },
            });
            const data = await res.json();

            order.items = order.items.filter(i => i.id !== item.id);

            if (data.all_ready) {
              order.all_ready = true;
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
