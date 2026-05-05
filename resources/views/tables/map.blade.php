{{--
 | Bloques 8.1 y 8.3 — Mapa visual de mesas con drag & drop y formas configurables
 | interact.js para arrastrar/redimensionar mesas en el plano del restaurante.
 | @author AyrtonAlania
--}}
<x-app-layout>
<div
    class="flex flex-col h-screen bg-gray-100 dark:bg-gray-900"
    x-data="tableMap()"
    x-init="init()"
>

    {{-- ══════════════════════════════════════════════════════
         TOPBAR
    ══════════════════════════════════════════════════════ --}}
    <header class="flex-shrink-0 flex items-center justify-between
                   bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700
                   px-4 sm:px-6 py-3 shadow-sm">

        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Plano del restaurante</h1>

            {{-- Contador con colores y barra de progreso --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium transition-colors"
                      :class="{
                          'text-emerald-600 dark:text-emerald-400': tables.length / {{ $maxTables }} < 0.8,
                          'text-amber-600  dark:text-amber-400':    tables.length / {{ $maxTables }} >= 0.8 && tables.length < {{ $maxTables }},
                          'text-red-600    dark:text-red-400':      tables.length >= {{ $maxTables }}
                      }"
                      :aria-label="`${tables.length} de {{ $maxTables }} mesas usadas`">
                    <span x-text="tables.length"></span> de {{ $maxTables }} mesas usadas
                </span>
                <div class="w-32 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden"
                     role="progressbar"
                     :aria-valuenow="tables.length"
                     aria-valuemin="0"
                     aria-valuemax="{{ $maxTables }}">
                    <div class="h-full rounded-full transition-all duration-300"
                         :style="`width:${Math.min(tables.length / {{ $maxTables }} * 100, 100)}%`"
                         :class="{
                             'bg-emerald-500': tables.length / {{ $maxTables }} < 0.8,
                             'bg-amber-500':   tables.length / {{ $maxTables }} >= 0.8 && tables.length < {{ $maxTables }},
                             'bg-red-500':     tables.length >= {{ $maxTables }}
                         }">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Toast --}}
            <div x-show="toast.show"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0"
                 :class="toast.error ? 'bg-red-100 text-red-700 border-red-300' : 'bg-green-100 text-green-700 border-green-300'"
                 class="border rounded-lg px-3 py-1.5 text-sm font-medium"
                 x-text="toast.msg"
                 role="alert"
                 aria-live="polite">
            </div>

            <a href="{{ route('tables.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
                      bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200
                      hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Ver QR
            </a>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         BODY — Paleta + Canvas
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── PALETA LATERAL ───────────────────────────────── --}}
        <aside class="flex-shrink-0 w-44 bg-white dark:bg-gray-800
                      border-r border-gray-200 dark:border-gray-700
                      flex flex-col p-3 gap-4 overflow-y-auto">

            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                Arrastrar al plano
            </p>

            {{-- Cuadrada --}}
            <div class="palette-item group flex flex-col items-center gap-2 select-none transition-opacity"
                 :class="tables.length >= {{ $maxTables }} ? 'opacity-40 cursor-not-allowed' : 'cursor-grab active:cursor-grabbing'"
                 :title="tables.length >= {{ $maxTables }} ? 'Límite de mesas alcanzado' : 'Arrastrar al plano'"
                 data-shape="square" data-width="100" data-height="100">
                <div class="w-14 h-14 rounded-lg border-2 border-dashed border-indigo-400
                            bg-indigo-50 dark:bg-indigo-900/30
                            group-hover:border-indigo-600 group-hover:bg-indigo-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-6 h-6 text-indigo-500" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Cuadrada</span>
            </div>

            {{-- Redonda --}}
            <div class="palette-item group flex flex-col items-center gap-2 select-none transition-opacity"
                 :class="tables.length >= {{ $maxTables }} ? 'opacity-40 cursor-not-allowed' : 'cursor-grab active:cursor-grabbing'"
                 :title="tables.length >= {{ $maxTables }} ? 'Límite de mesas alcanzado' : 'Arrastrar al plano'"
                 data-shape="round" data-width="100" data-height="100">
                <div class="w-14 h-14 rounded-full border-2 border-dashed border-emerald-400
                            bg-emerald-50 dark:bg-emerald-900/30
                            group-hover:border-emerald-600 group-hover:bg-emerald-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Redonda</span>
            </div>

            {{-- Rectangular --}}
            <div class="palette-item group flex flex-col items-center gap-2 select-none transition-opacity"
                 :class="tables.length >= {{ $maxTables }} ? 'opacity-40 cursor-not-allowed' : 'cursor-grab active:cursor-grabbing'"
                 :title="tables.length >= {{ $maxTables }} ? 'Límite de mesas alcanzado' : 'Arrastrar al plano'"
                 data-shape="rectangle" data-width="150" data-height="90">
                <div class="w-14 h-9 rounded-lg border-2 border-dashed border-amber-400
                            bg-amber-50 dark:bg-amber-900/30
                            group-hover:border-amber-600 group-hover:bg-amber-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-6 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Rectangular</span>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                Arrastra una forma al plano para crear una mesa. Mueve y redimensiona desde los bordes.
            </p>

            <template x-if="tables.length >= {{ $maxTables }}">
                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-2 text-xs text-amber-700 dark:text-amber-400">
                    Límite de <strong>{{ $maxTables }}</strong> mesas alcanzado.
                </div>
            </template>
        </aside>

        {{-- ── CANVAS ───────────────────────────────────────── --}}
        <main id="main-content" class="flex-1 overflow-auto p-4">
            <div
                x-ref="canvas"
                class="relative bg-white dark:bg-gray-800 rounded-xl shadow-inner
                       border-2 border-dashed border-gray-200 dark:border-gray-700"
                style="width: 1200px; height: 800px; min-width: 100%;"
                aria-label="Plano del restaurante"
                @click="editingTableId = null"
            >
                {{-- Cuadrícula decorativa --}}
                <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-30 dark:opacity-10"
                     xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)"/>
                </svg>

                {{-- Mesas existentes --}}
                <template x-for="table in tables" :key="table.id">
                    <div
                        :data-table-id="table.id"
                        class="table-item absolute group select-none touch-none"
                        :style="`left:${table.position_x}px; top:${table.position_y}px;
                                 width:${table.width}px; height:${table.height}px;
                                 transform: rotate(${table.rotation ?? 0}deg);
                                 transform-origin: center;`"
                        :aria-label="`Mesa ${table.name}`"
                    >
                        {{-- Fondo de la mesa --}}
                        <div class="w-full h-full relative flex items-center justify-center
                                    bg-indigo-100 dark:bg-indigo-900/40
                                    border-2 border-indigo-300 dark:border-indigo-600
                                    shadow-md cursor-grab active:cursor-grabbing
                                    transition-shadow hover:shadow-lg"
                             :class="{
                                'rounded-full':  table.shape === 'round',
                                'rounded-xl':    table.shape === 'square',
                                'rounded-lg':    table.shape === 'rectangle',
                             }">

                            {{-- Nombre --}}
                            <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300
                                         text-center px-1 leading-tight pointer-events-none"
                                  x-text="table.name">
                            </span>

                            {{-- Badge estado --}}
                            <span class="absolute top-1 left-1 w-2 h-2 rounded-full"
                                  :class="table.status === 'occupied' ? 'bg-red-500' : 'bg-green-400'"
                                  :title="table.status === 'occupied' ? 'Ocupada' : 'Libre'">
                            </span>

                            {{-- Botón editar forma --}}
                            <button type="button"
                                    @click.stop="editingTableId = editingTableId === table.id ? null : table.id"
                                    class="absolute -top-2.5 -right-9
                                           w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                           shadow-md"
                                    :aria-label="`Editar forma de mesa ${table.name}`"
                                    :aria-expanded="editingTableId === table.id">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    @click.stop="deleteTable(table)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :aria-label="`Eliminar mesa ${table.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Panel de edición (solo forma) --}}
                            <div x-show="editingTableId === table.id"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 @click.stop
                                 class="absolute top-7 right-0 z-20
                                        bg-white dark:bg-gray-800 rounded-xl shadow-xl
                                        border border-gray-200 dark:border-gray-700
                                        p-3 min-w-max"
                                 role="dialog"
                                 :aria-label="`Forma de mesa ${table.name}`">

                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Forma</p>
                                <div class="flex gap-1.5" role="group" aria-label="Seleccionar forma">
                                    <button type="button"
                                            @click.stop="updateShape(table, 'square')"
                                            :class="table.shape === 'square'
                                                ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                            aria-label="Forma cuadrada">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
                                    </button>
                                    <button type="button"
                                            @click.stop="updateShape(table, 'round')"
                                            :class="table.shape === 'round'
                                                ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                            aria-label="Forma redonda">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
                                    </button>
                                    <button type="button"
                                            @click.stop="updateShape(table, 'rectangle')"
                                            :class="table.shape === 'rectangle'
                                                ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                            aria-label="Forma rectangular">
                                        <svg class="w-5 h-3" fill="currentColor" viewBox="0 0 24 14" aria-hidden="true"><rect x="0" y="0" width="24" height="14" rx="3"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Handle de rotación — arrastra para girar la mesa --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    opacity-0 group-hover:opacity-100 transition-opacity z-10"
                             @mousedown.stop.prevent="startRotation($event, table)"
                             role="button"
                             tabindex="0"
                             :aria-label="`Rotar mesa ${table.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-indigo-400 shadow-md
                                        flex items-center justify-center text-indigo-500
                                        cursor-grab active:cursor-grabbing
                                        hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-indigo-400"></div>
                        </div>

                        {{-- Handle de redimensionado (esquina inferior derecha) --}}
                        <div class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-indigo-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Indicador de zona de soltar --}}
                <div x-show="isDraggingFromPalette"
                     class="absolute inset-0 rounded-xl border-4 border-dashed border-indigo-400
                            bg-indigo-50/30 dark:bg-indigo-900/20 pointer-events-none
                            flex items-center justify-center">
                    <p class="text-indigo-500 font-semibold text-lg">Suelta aquí para crear la mesa</p>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Ghost element — sigue al cursor mientras se arrastra desde la paleta --}}
<div id="palette-ghost"
     class="fixed pointer-events-none z-50 hidden opacity-70
            bg-indigo-200 border-2 border-indigo-500 shadow-xl flex items-center justify-center">
    <span class="text-xs font-semibold text-indigo-700">Nueva mesa</span>
</div>

{{-- Modal: nombre de la nueva mesa --}}
<div x-data
     x-show="$store.tableModal.open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
     aria-modal="true"
     role="dialog"
     aria-labelledby="modal-title"
     @keydown.escape.window="$store.tableModal.cancel()">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4"
         @click.stop>

        <h2 id="modal-title" class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            Nueva mesa
        </h2>

        <div class="mb-4">
            <label for="new-table-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nombre de la mesa
            </label>
            <input id="new-table-name"
                   type="text"
                   x-model="$store.tableModal.name"
                   @keydown.enter="$store.tableModal.confirm()"
                   maxlength="50"
                   placeholder="Ej: Mesa 1, Terraza A..."
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                          px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   aria-required="true"
                   x-init="$watch('$store.tableModal.open', v => v && $nextTick(() => $el.focus()))">
        </div>

        <div class="flex gap-2 justify-end">
            <button type="button"
                    @click="$store.tableModal.cancel()"
                    class="px-4 py-2 rounded-lg text-sm font-medium
                           text-gray-600 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700
                           hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </button>
            <button type="button"
                    @click="$store.tableModal.confirm()"
                    :disabled="!$store.tableModal.name.trim()"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white
                           bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50
                           disabled:cursor-not-allowed transition-colors">
                Crear
            </button>
        </div>
    </div>
</div>

{{-- interact.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {

    // ── Store para el modal de nombre de nueva mesa ───────────────────────────
    Alpine.store('tableModal', {
        open:       false,
        name:       '',
        _resolve:   null,

        prompt() {
            this.name = '';
            this.open = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        confirm() {
            if (!this.name.trim()) return;
            const name = this.name.trim();
            this.open = false;
            this._resolve?.(name);
        },

        cancel() {
            this.open = false;
            this._resolve?.(null);
        },
    });

    // ── Componente principal del mapa ─────────────────────────────────────────
    Alpine.data('tableMap', () => ({
        tables:                @json($tables),
        isDraggingFromPalette: false,
        editingTableId:        null,
        isRotating:            false,
        toast:                 { show: false, msg: '', error: false, _timer: null },

        init() {
            this.$nextTick(() => {
                this.initTableInteract();
                this.initPaletteInteract();
            });
        },

        // ── Toast ─────────────────────────────────────────────────────────────
        showToast(msg, error = false) {
            clearTimeout(this.toast._timer);
            this.toast = { show: true, msg, error, _timer: null };
            this.toast._timer = setTimeout(() => { this.toast.show = false; }, 3000);
        },

        // ── Interactividad de mesas existentes ────────────────────────────────
        initTableInteract() {
            interact('.table-item').unset();

            interact('.table-item')
                .draggable({
                    ignoreFrom:  '.rotation-handle',
                    inertia:    false,
                    autoScroll: true,
                    modifiers: [
                        interact.modifiers.restrictRect({
                            restriction: this.$refs.canvas,
                            endOnly:     false,
                        }),
                    ],
                    listeners: {
                        move: (event) => {
                            const el = event.target;
                            const x  = (parseFloat(el.style.left) || 0) + event.dx;
                            const y  = (parseFloat(el.style.top)  || 0) + event.dy;
                            el.style.left = `${x}px`;
                            el.style.top  = `${y}px`;
                        },
                        end: (event) => {
                            const el  = event.target;
                            const id  = parseInt(el.dataset.tableId);
                            const x   = Math.round(parseFloat(el.style.left) || 0);
                            const y   = Math.round(parseFloat(el.style.top)  || 0);
                            const w   = Math.round(parseFloat(el.style.width)  || 100);
                            const h   = Math.round(parseFloat(el.style.height) || 100);
                            this.persistPosition(id, x, y, w, h);
                        },
                    },
                })
                .resizable({
                    ignoreFrom: '.rotation-handle',
                    edges:   { left: false, right: true, bottom: true, top: false },
                    inertia: false,
                    modifiers: [
                        interact.modifiers.restrictSize({
                            min: { width: 60, height: 60 },
                            max: { width: 400, height: 400 },
                        }),
                    ],
                    listeners: {
                        move: (event) => {
                            const el = event.target;
                            el.style.width  = `${event.rect.width}px`;
                            el.style.height = `${event.rect.height}px`;
                        },
                        end: (event) => {
                            const el = event.target;
                            const id = parseInt(el.dataset.tableId);
                            const x  = Math.round(parseFloat(el.style.left) || 0);
                            const y  = Math.round(parseFloat(el.style.top)  || 0);
                            const w  = Math.round(parseFloat(el.style.width)  || 100);
                            const h  = Math.round(parseFloat(el.style.height) || 100);
                            this.persistPosition(id, x, y, w, h);
                        },
                    },
                });
        },

        // ── Reinicializar interact después de añadir una mesa ─────────────────
        reinitInteract() {
            this.$nextTick(() => this.initTableInteract());
        },

        // ── Paleta: drag-to-create ────────────────────────────────────────────
        initPaletteInteract() {
            const ghost     = document.getElementById('palette-ghost');
            const canvasEl  = this.$refs.canvas;
            let   dropShape = null;
            let   dropW     = 100;
            let   dropH     = 100;
            let   dropX     = 0;
            let   dropY     = 0;

            interact('.palette-item').draggable({
                inertia: false,
                listeners: {
                    start: (event) => {
                        if (this.tables.length >= {{ $maxTables }}) {
                            this.showToast(`Límite de {{ $maxTables }} mesas alcanzado.`, true);
                            return;
                        }

                        dropShape = event.target.dataset.shape;
                        dropW     = parseInt(event.target.dataset.width)  || 100;
                        dropH     = parseInt(event.target.dataset.height) || 100;

                        ghost.style.width   = `${dropW}px`;
                        ghost.style.height  = `${dropH}px`;
                        ghost.style.borderRadius =
                            dropShape === 'round' ? '9999px' :
                            dropShape === 'square' ? '12px' : '8px';
                        ghost.classList.remove('hidden');
                        ghost.classList.add('flex');

                        this.isDraggingFromPalette = true;
                    },

                    move: (event) => {
                        const canvasRect = canvasEl.getBoundingClientRect();
                        const cx         = event.clientX;
                        const cy         = event.clientY;

                        ghost.style.left = `${cx - dropW / 2}px`;
                        ghost.style.top  = `${cy - dropH / 2}px`;

                        dropX = Math.max(0, Math.round(cx - canvasRect.left - dropW / 2));
                        dropY = Math.max(0, Math.round(cy - canvasRect.top  - dropH / 2));
                    },

                    end: async (event) => {
                        ghost.classList.add('hidden');
                        ghost.classList.remove('flex');
                        this.isDraggingFromPalette = false;

                        const canvasRect = canvasEl.getBoundingClientRect();
                        const cx         = event.clientX;
                        const cy         = event.clientY;

                        const overCanvas =
                            cx >= canvasRect.left && cx <= canvasRect.right &&
                            cy >= canvasRect.top  && cy <= canvasRect.bottom;

                        if (!overCanvas) return;

                        const name = await Alpine.store('tableModal').prompt();
                        if (!name) return;

                        await this.createTable(name, dropShape, dropX, dropY, dropW, dropH);
                    },
                },
            });
        },

        // ── AJAX: crear mesa ──────────────────────────────────────────────────
        async createTable(name, shape, x, y, w, h) {
            try {
                const res = await fetch('{{ route("tables.store") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        shape,
                        position_x: x,
                        position_y: y,
                        width:      w,
                        height:     h,
                    }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    this.showToast(json.message ?? 'Error al crear la mesa.', true);
                    return;
                }

                this.tables.push(json.data);
                this.reinitInteract();
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red al crear la mesa.', true);
            }
        },

        // ── AJAX: persistir posición, dimensiones y rotación ─────────────────
        async persistPosition(id, x, y, w, h) {
            const table = this.tables.find(t => t.id === id);
            if (table) {
                table.position_x = x;
                table.position_y = y;
                table.width      = w;
                table.height     = h;
            }

            try {
                const res = await fetch(`/mesas/${id}/posicion`, {
                    method:  'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        position_x: x,
                        position_y: y,
                        width:      w,
                        height:     h,
                        rotation:   table?.rotation ?? 0,
                    }),
                });

                if (!res.ok) {
                    this.showToast('Error al guardar la posición.', true);
                }
            } catch {
                this.showToast('Error de red al guardar posición.', true);
            }
        },

        // ── AJAX: actualizar forma de mesa existente ──────────────────────────
        async updateShape(table, shape) {
            const prev = table.shape;
            table.shape       = shape;
            this.editingTableId = null;

            try {
                const res = await fetch(`/mesas/${table.id}/forma`, {
                    method:  'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ shape }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    table.shape = prev;
                    this.showToast(json.message ?? 'Error al cambiar la forma.', true);
                    return;
                }

                this.showToast(json.message);
            } catch {
                table.shape = prev;
                this.showToast('Error de red al cambiar la forma.', true);
            }
        },

        // ── Rotación libre arrastrando el handle (estilo Canva) ───────────────
        startRotation(event, table) {
            const canvasRect = this.$refs.canvas.getBoundingClientRect();
            const centerX    = canvasRect.left + table.position_x + table.width  / 2;
            const centerY    = canvasRect.top  + table.position_y + table.height / 2;

            this.isRotating            = true;
            document.body.style.cursor = 'grabbing';

            const onMove = (e) => {
                const dx    = e.clientX - centerX;
                const dy    = e.clientY - centerY;
                let   angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
                angle = ((angle % 360) + 360) % 360;
                table.rotation = Math.round(angle);
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                this.isRotating            = false;
                document.body.style.cursor = '';
                await this.persistPosition(table.id, table.position_x, table.position_y, table.width, table.height);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── AJAX: eliminar mesa ───────────────────────────────────────────────
        async deleteTable(table) {
            if (!confirm(`¿Eliminar la mesa "${table.name}"? Esta acción no se puede deshacer.`)) return;

            try {
                const res = await fetch(`/mesas/${table.id}`, {
                    method:  'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    this.showToast(json.message ?? 'Error al eliminar la mesa.', true);
                    return;
                }

                this.tables = this.tables.filter(t => t.id !== table.id);
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red al eliminar la mesa.', true);
            }
        },
    }));
});
</script>
</x-app-layout>
