{{-- @author SebastianBCF --}}
{{-- @author AyrtonAlania --}}
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
        class="bg-indigo-50 border border-indigo-400 rounded-lg px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
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

        {{-- Desglose de importe solo para efectivo --}}
        <template x-if="order.payment_method === 'cash'">
          <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
            <span class="text-gray-600">
              Subtotal: <strong class="text-gray-800" x-text="'€' + order.subtotal.toFixed(2)"></strong>
            </span>
            <template x-if="order.tip > 0">
              <span class="text-amber-700">
                + Propina: <strong x-text="'€' + order.tip.toFixed(2)"></strong>
              </span>
            </template>
            <span class="font-bold text-green-800 text-base">
              &#128181; Total a cobrar: <span x-text="'€' + order.amount_to_collect.toFixed(2)"></span>
            </span>
          </div>
        </template>
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
        class="bg-green-50 border border-green-400 rounded-lg px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex items-center justify-between">
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
        <template x-if="order.items && order.items.length > 0">
          <ul class="mt-2 flex flex-wrap gap-2">
            <template x-for="(item, i) in order.items" :key="i">
              <li
                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full"
                :class="item.is_tapa ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800'"
              >
                <span x-text="'×' + item.quantity"></span>
                <span x-text="item.name"></span>
                <span x-show="item.is_tapa" class="text-amber-600 font-bold">&#127798;</span>
              </li>
            </template>
          </ul>
        </template>
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
        <span x-text="polling ? 'En vivo' : 'Sin conexión'" class="text-gray-600 dark:text-gray-400"></span>
      </div>
    </div>

    {{-- Sin ítems pendientes --}}
    <div x-show="orders.length === 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-10 text-center text-gray-400 dark:text-gray-500">
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
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">

          {{-- Cabecera de la comanda --}}
          <div class="flex items-center justify-between px-4 py-3 bg-amber-500 text-white">
            <span class="font-bold text-base" x-text="order.table_name"></span>
            <span class="text-xs bg-white/20 rounded px-2 py-1" x-text="order.created_at"></span>
          </div>

          {{-- Ítems pendientes --}}
          <ul class="divide-y divide-gray-100 dark:divide-gray-700" role="list">
            <template x-for="item in order.items" :key="item.id">
              <li class="px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                      <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-bold mr-1"
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

  {{-- Sección de disponibilidad de productos de barra --}}
  <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-10">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">

      {{-- Cabecera de la sección --}}
      <div class="flex items-center justify-between px-4 py-3 bg-amber-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
        <div>
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Disponibilidad de productos</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Desactiva los que se agoten durante el servicio — desaparecen de la carta al instante.</p>
        </div>
        @php $unavailableBarCount = $barProducts->where('is_available', false)->count(); @endphp
        @if($unavailableBarCount > 0)
          <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
            {{ $unavailableBarCount }} agotado{{ $unavailableBarCount > 1 ? 's' : '' }}
          </span>
        @endif
      </div>

      @if($barProducts->isEmpty())
        <p class="px-4 py-6 text-sm text-center text-gray-400 dark:text-gray-500">No hay productos de barra activos.</p>
      @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700" role="list" aria-label="Productos de barra">
          @foreach($barProducts as $product)
          <li
            class="flex items-center justify-between gap-3 px-4 py-3 transition-colors"
            x-data="productAvailability({
              available: {{ $product->is_available ? 'true' : 'false' }},
              toggleUrl: '{{ url('/bar/productos/' . $product->id . '/disponibilidad') }}'
            })"
            :class="available ? '' : 'bg-red-50 dark:bg-red-900/20'"
          >
            <span
              class="text-sm font-medium transition-colors"
              :class="available ? 'text-gray-800 dark:text-gray-200' : 'text-red-500 dark:text-red-400 line-through'"
            >{{ $product->name }}</span>

            <button
              @click="toggle()"
              :disabled="loading"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50"
              :class="available ? 'bg-amber-500' : 'bg-gray-300 dark:bg-gray-600'"
              role="switch"
              :aria-checked="available.toString()"
              :aria-label="'{{ $product->name }}: ' + (available ? 'disponible, pulsa para marcar agotado' : 'agotado, pulsa para restaurar')"
            >
              <span
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="available ? 'translate-x-5' : 'translate-x-0'"
              ></span>
            </button>
          </li>
          @endforeach
        </ul>
      @endif

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
        'id'           => $i->id,
        'product_name' => $i->product->name,
        'quantity'     => $i->quantity,
        'is_daily_menu'=> (bool) $i->is_daily_menu,
        'marking'      => false,
      ])->values(),
    ])->values();

    $barUrls = [
      'barPending'                   => route('bar.pending'),
      'billRequests'                 => route('notifications.bill.requests'),
      'billDismissTemplate'          => route('notifications.bill.dismiss', ['order' => '__ID__']),
      'notificationsDismissTemplate' => route('notifications.dismiss', ['order' => '__ID__']),
      'notificationsReady'           => route('notifications.ready'),
      'payments'                     => url('/payments'),
      'barItems'                     => url('/bar/items'),
    ];
  @endphp
  <script id="bar-urls" type="application/json">@json($barUrls)</script>
  <script id="bar-ready-orders" type="application/json">@json($readyOrders)</script>
  <script id="bar-init" type="application/json">@json($ordersForJs)</script>
  @endpush

</x-app-layout>
