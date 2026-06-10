{{-- @author SebastianBCF --}}
{{-- @author AyrtonAlania --}}
<x-app-layout>

  {{-- Llamadas al camarero: polling cada 15 segundos --}}
  <div
    x-data="waiterCallPolling()"
    x-init="init()"
    class="max-w-6xl mx-auto px-4 sm:px-6 pt-4"
    role="region"
    aria-label="Llamadas al camarero"
  >
    <template x-for="order in waiterOrders" :key="order.id">
      <div
        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700
               rounded-xl px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200 font-medium text-sm min-w-0">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
              <circle cx="12" cy="2" r="1" fill="currentColor" stroke="none"/>
            </svg>
            <strong x-text="order.table"></strong>
            <span class="font-normal opacity-80">solicita al camarero</span>
          </div>
          <button
            @click="dismiss(order.id)"
            class="shrink-0 inline-flex items-center gap-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-colors cursor-pointer"
            :aria-label="'Confirmar atención a: ' + order.table"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
            Atendido
          </button>
        </div>
      </div>
    </template>
  </div>

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
        class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-300 dark:border-indigo-700
               rounded-xl px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="flex items-center gap-2 min-w-0 flex-wrap">
            <div class="flex items-center gap-1.5 text-indigo-800 dark:text-indigo-200 font-medium text-sm">
              <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <strong x-text="order.table"></strong>
              <span class="font-normal opacity-80">solicita la cuenta</span>
            </div>
            <span
              x-show="order.payment_method === 'cash'"
              class="inline-flex items-center gap-1 text-xs font-semibold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full"
              aria-label="Paga en efectivo"
            >
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              Efectivo
            </span>
            <span
              x-show="order.payment_method === 'card'"
              class="inline-flex items-center gap-1 text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full"
              aria-label="Paga con tarjeta"
            >
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              Tarjeta
            </span>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <template x-if="order.payment_method === 'cash'">
              <button
                @click="cashPayment(order)"
                :disabled="order.paying"
                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors cursor-pointer"
                :aria-label="'Cobrar en efectivo: ' + order.table"
              >
                <template x-if="!order.paying">
                  <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Cobrar efectivo
                  </span>
                </template>
                <template x-if="order.paying">
                  <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </template>
              </button>
            </template>
            <template x-if="order.payment_method === 'card'">
              <button
                disabled
                class="inline-flex items-center gap-1.5 bg-indigo-200 dark:bg-indigo-800 text-indigo-500 dark:text-indigo-400 text-xs font-semibold px-3 py-1.5 rounded-lg cursor-not-allowed"
                :aria-label="'Cobro con tarjeta pendiente: ' + order.table"
              >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Cobrar con tarjeta
              </button>
            </template>
            <button
              @click="dismiss(order.id)"
              class="bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition-colors cursor-pointer"
              :aria-label="'Ignorar solicitud de cuenta: ' + order.table"
            >
              Ignorar
            </button>
          </div>
        </div>

        {{-- Desglose de importe solo para efectivo --}}
        <template x-if="order.payment_method === 'cash'">
          <div class="mt-2 pt-2 border-t border-indigo-200 dark:border-indigo-800 flex flex-wrap items-center gap-3 text-sm">
            <span class="text-gray-600 dark:text-gray-400">
              Subtotal: <strong class="text-gray-800 dark:text-gray-200 tabular-nums" x-text="'€' + order.subtotal.toFixed(2)"></strong>
            </span>
            <template x-if="order.tip > 0">
              <span class="text-amber-700 dark:text-amber-400">
                + Propina: <strong class="tabular-nums" x-text="'€' + order.tip.toFixed(2)"></strong>
              </span>
            </template>
            <span class="font-bold text-green-700 dark:text-green-400 text-base tabular-nums">
              Total: <span x-text="'€' + order.amount_to_collect.toFixed(2)"></span>
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
        class="bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-800
               rounded-xl px-4 py-3 mb-2 shadow-sm"
        role="alert"
      >
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-green-800 dark:text-green-300 font-medium text-sm min-w-0">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
            <strong x-text="order.table"></strong>
            <span class="font-normal opacity-80 truncate">lista para servir</span>
          </div>
          <button
            @click="dismiss(order.id)"
            class="shrink-0 inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors cursor-pointer"
            :aria-label="'Confirmar comanda lista: ' + order.table"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
            Confirmado
          </button>
        </div>
        <template x-if="order.items && order.items.length > 0">
          <ul class="mt-2 flex flex-wrap gap-1.5">
            <template x-for="(item, i) in order.items" :key="i">
              <li
                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full"
                :class="item.is_tapa
                  ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300'
                  : 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300'"
              >
                <span x-text="'×' + item.quantity"></span>
                <span x-text="item.name"></span>
                <template x-if="item.is_tapa">
                  <svg class="w-3 h-3 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="5"/></svg>
                </template>
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
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Panel de Barra</h2>
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
                    class="shrink-0 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-semibold px-3 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 transition-colors cursor-pointer"
                    :aria-label="'Marcar como servido: ' + item.product_name"
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
      'waiterCalls'                  => route('notifications.waiter.calls'),
      'waiterDismissTemplate'        => route('notifications.waiter.dismiss', ['order' => '__ID__']),
      'payments'                     => url('/payments'),
      'barItems'                     => url('/bar/items'),
    ];
  @endphp
  <script id="bar-urls" type="application/json">@json($barUrls)</script>
  <script id="bar-ready-orders" type="application/json">@json($readyOrders)</script>
  <script id="bar-init" type="application/json">@json($ordersForJs)</script>
  @endpush

</x-app-layout>
