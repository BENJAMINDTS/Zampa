{{--
 | Bloques 8.1, 8.3 y 8.4 — Mapa visual del local con zonas, elementos especiales y mesas.
 | interact.js para arrastrar/redimensionar mesas, elementos especiales y zonas.
 | @author AyrtonAlania
 | @author SebastianBCF
--}}
<x-app-layout>
<div
    class="flex flex-col h-screen bg-gray-100 dark:bg-gray-900"
    x-data="tableMap()"
    x-init="init()"
    @mouseup.window="if(isRotating||isRotatingZone){}"
>

    {{-- ══════════════════════════════════════════════════════
         TOPBAR
    ══════════════════════════════════════════════════════ --}}
    <header class="flex-shrink-0 flex items-center justify-between
                   bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700
                   px-4 sm:px-6 py-3 shadow-sm">

        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Plano del restaurante</h1>

            {{-- Badge modo solo lectura --}}
            <span x-show="readonly"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                         bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                Solo lectura
            </span>

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

        <div class="flex items-center gap-3 flex-wrap">
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

            {{-- ── Control de tamaño del lienzo — solo admin ──────────────── --}}
            <div x-show="!readonly" class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 hidden sm:block"
                      aria-hidden="true">Plano:</span>

                {{-- Botones S / M / L / XL --}}
                <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600"
                     role="group"
                     aria-label="Tamaño del lienzo del plano">
                    <button type="button"
                            @click="setCanvasSize(1200, 800)"
                            :aria-pressed="floorWidth === 1200 && floorHeight === 800"
                            :class="floorWidth === 1200 && floorHeight === 800
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo pequeño: 1200 × 800 px">S</button>
                    <button type="button"
                            @click="setCanvasSize(1600, 1000)"
                            :aria-pressed="floorWidth === 1600 && floorHeight === 1000"
                            :class="floorWidth === 1600 && floorHeight === 1000
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo mediano: 1600 × 1000 px">M</button>
                    <button type="button"
                            @click="setCanvasSize(2000, 1200)"
                            :aria-pressed="floorWidth === 2000 && floorHeight === 1200"
                            :class="floorWidth === 2000 && floorHeight === 1200
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo grande: 2000 × 1200 px">L</button>
                    <button type="button"
                            @click="setCanvasSize(2400, 1500)"
                            :aria-pressed="floorWidth === 2400 && floorHeight === 1500"
                            :class="floorWidth === 2400 && floorHeight === 1500
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo extra grande: 2400 × 1500 px">XL</button>
                </div>

                {{-- Zoom slider (solo visual, sin persistencia en BD) --}}
                <div class="hidden sm:flex items-center gap-1.5">
                    <label for="canvas-zoom" class="sr-only">Zoom del plano</label>
                    <input id="canvas-zoom"
                           type="range"
                           min="0.5"
                           max="1"
                           step="0.05"
                           x-model.number="canvasZoom"
                           class="w-20 h-1.5 accent-indigo-600 cursor-pointer"
                           :aria-label="`Zoom del plano: ${Math.round(canvasZoom * 100)}%`">
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 w-9 text-right tabular-nums"
                          x-text="Math.round(canvasZoom * 100) + '%'"></span>
                    <button type="button"
                            :class="canvasZoom < 1 ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
                            @click="canvasZoom = 1"
                            class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400
                                   dark:hover:text-indigo-200 focus:outline-none focus:ring-1
                                   focus:ring-indigo-400 rounded px-1 font-medium transition-opacity duration-150"
                            aria-label="Restablecer zoom al 100%"
                            :aria-hidden="canvasZoom >= 1"
                            :tabindex="canvasZoom < 1 ? 0 : -1">1:1</button>
                </div>
            </div>

            <a href="{{ route('tables.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white
                      bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
               aria-label="Ir a la gestión de QR de mesas">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 18.75h.75v.75h-.75v-.75zM18.75 13.5h.75v.75h-.75v-.75zM18.75 18.75h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                </svg>
                Ver QR
            </a>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         BODY — Paleta + Canvas
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── PALETA LATERAL — solo visible para admin ─────── --}}
        <aside x-show="!readonly"
               class="flex-shrink-0 w-44 bg-white dark:bg-gray-800
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

            {{-- Elementos especiales --}}
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                Especiales
            </p>

            {{-- Barra del bar --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="bar" data-width="200" data-height="60"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-14 h-7 rounded border-2 border-dashed border-amber-700
                            bg-amber-100 dark:bg-amber-900/30
                            group-hover:border-amber-800 group-hover:bg-amber-200 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-5 h-3 text-amber-700" fill="currentColor" viewBox="0 0 24 12">
                        <rect x="0" y="0" width="24" height="12" rx="2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Barra</span>
            </div>

            {{-- Taburete --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="stool" data-width="50" data-height="50"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-10 h-10 rounded-full border-2 border-dashed border-amber-600
                            bg-amber-50 dark:bg-amber-900/30
                            group-hover:border-amber-700 group-hover:bg-amber-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="6"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Taburete</span>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Zonas --}}
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                Zonas
            </p>

            <div class="flex items-center gap-2">
                <label for="zone-color-picker" class="text-xs text-gray-500 dark:text-gray-400">Color</label>
                <input id="zone-color-picker"
                       type="color"
                       x-model="zoneColor"
                       class="w-8 h-8 rounded cursor-pointer border border-gray-200 dark:border-gray-600 p-0.5"
                       aria-label="Color de la nueva zona">
            </div>

            <div class="zone-palette-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing"
                 data-width="300" data-height="200"
                 title="Arrastrar al plano para crear una zona">
                <div class="w-14 h-9 rounded border-2 border-dashed flex items-center justify-center transition-colors"
                     :style="`border-color: ${zoneColor}; background-color: ${zoneColor}22;`">
                    <svg aria-hidden="true" class="w-6 h-4" :style="`color: ${zoneColor}`" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="12" x2="21" y2="12" stroke-dasharray="3 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Zona</span>
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
                :style="`width:${floorWidth}px; height:${floorHeight}px; min-width:100%;
                         transform:scale(${canvasZoom}); transform-origin:top left;`"
                aria-label="Plano del restaurante"
                @click="if (canvasZoom === 1) { editingTableId = null; editingZoneId = null; }"
            >
                {{-- Overlay de zoom: bloquea edición cuando canvasZoom < 1 --}}
                <div x-show="canvasZoom < 1"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="absolute inset-0 z-40 rounded-xl cursor-not-allowed select-none"
                     role="status"
                     aria-live="polite">
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2
                                bg-amber-50 dark:bg-amber-900/80
                                border border-amber-300 dark:border-amber-600
                                text-amber-800 dark:text-amber-300
                                text-sm font-medium px-4 py-2 rounded-full shadow-md
                                whitespace-nowrap pointer-events-none">
                        Ajusta el zoom al 100% para editar el plano
                    </div>
                </div>

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

                {{-- Zonas (capa de fondo, z-index 5) --}}
                <template x-for="zone in zones" :key="'z'+zone.id">
                    <div
                        :data-zone-id="zone.id"
                        class="zone-item absolute group select-none touch-none cursor-grab"
                        :style="`
                            left:${zone.position_x}px;
                            top:${zone.position_y}px;
                            width:${zone.width}px;
                            height:${zone.height}px;
                            background-color:${zone.color}22;
                            border:2px solid ${zone.color};
                            z-index:${editingZoneId === zone.id ? 20 : 5};
                            pointer-events:all;
                            transform:rotate(${zone.rotation ?? 0}deg);
                            transform-origin:center;
                        `"
                        :aria-label="`Zona ${zone.name}`"
                        @mousedown.prevent.self="startZoneDrag($event, zone)"
                    >
                        {{-- Etiqueta de zona --}}
                        <span class="absolute bottom-1 left-1 text-xs font-semibold px-1.5 py-0.5 rounded pointer-events-none"
                              :style="`color:${zone.color}; background:rgba(255,255,255,0.85);`"
                              x-text="zone.name">
                        </span>

                        {{-- Botón editar zona --}}
                        <button type="button"
                                @click.stop="editingZoneId = editingZoneId === zone.id ? null : zone.id"
                                class="absolute -top-2.5 -right-9
                                       w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                       flex items-center justify-center
                                       opacity-0 group-hover:opacity-100 transition-opacity
                                       hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                       shadow-md"
                                :aria-label="`Editar zona ${zone.name}`"
                                :aria-expanded="editingZoneId === zone.id">
                            <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                        </button>

                        {{-- Botón eliminar zona --}}
                        <button type="button"
                                @click.stop="deleteZone(zone)"
                                class="absolute -top-2.5 -right-2.5
                                       w-6 h-6 rounded-full bg-red-500 text-white
                                       flex items-center justify-center
                                       opacity-0 group-hover:opacity-100 transition-opacity
                                       hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                       shadow-md"
                                :aria-label="`Eliminar zona ${zone.name}`">
                            <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Panel de edición de zona --}}
                        <div x-show="editingZoneId === zone.id"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             @click.stop
                             class="absolute top-7 right-0 z-30
                                    bg-white dark:bg-gray-800 rounded-xl shadow-xl
                                    border border-gray-200 dark:border-gray-700
                                    p-3 min-w-max"
                             role="dialog"
                             :aria-label="`Editar zona ${zone.name}`">

                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                            <div class="flex gap-1 mb-3">
                                <input type="text"
                                       :value="zone.name"
                                       @keydown.enter.stop="updateZoneName(zone, $event.target.value); $event.target.blur()"
                                       @blur.stop="updateZoneName(zone, $event.target.value)"
                                       @click.stop
                                       maxlength="50"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                              px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       :aria-label="`Nombre de la zona ${zone.name}`">
                            </div>

                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Color</p>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="color"
                                       :value="zone.color"
                                       @change.stop="updateZoneColor(zone, $event.target.value)"
                                       @click.stop
                                       class="w-8 h-8 rounded cursor-pointer border border-gray-200 p-0.5"
                                       :aria-label="`Color de la zona ${zone.name}`">
                                <span class="text-xs text-gray-500 font-mono" x-text="zone.color"></span>
                            </div>
                        </div>

                        {{-- Handle de rotación de zona (color sincronizado con zone.color) --}}
                        <div class="absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    opacity-0 group-hover:opacity-100 transition-opacity z-10"
                             @mousedown.stop.prevent="startZoneRotation($event, zone)"
                             @mouseenter.stop="rotTooltip = { show: true, x: $event.clientX, y: $event.clientY, deg: zone.rotation ?? 0 }"
                             @mousemove.stop="rotTooltip.x = $event.clientX; rotTooltip.y = $event.clientY; rotTooltip.deg = zone.rotation ?? 0"
                             @mouseleave.stop="if (!isRotating) rotTooltip.show = false"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar zona ${zone.name} (arrastra para girar)`">
                            <div x-data="{ hov: false }"
                                 class="w-6 h-6 rounded-full shadow-md
                                        flex items-center justify-center
                                        transition-all duration-150 hover:scale-110"
                                 :style="hov
                                     ? `border:2px solid ${zone.color}; background-color:${zone.color}; color:white;`
                                     : `border:2px solid ${zone.color}; background-color:${zone.color}22; color:${zone.color};`"
                                 @mouseenter.stop="hov = true"
                                 @mouseleave.stop="hov = false">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3" :style="`background-color:${zone.color};`"></div>
                        </div>

                        {{-- Handle de redimensionado de zona --}}
                        <div class="zone-resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startZoneResize($event, zone)"
                             role="button"
                             :aria-label="`Redimensionar zona ${zone.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full" :style="`color:${zone.color}`">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Barras especiales: usa table-item para interact.js, idéntico al sistema de mesas --}}
                <template x-for="bar in elements.filter(e => e.shape === 'bar')" :key="'b'+bar.id">
                    <div
                        :data-table-id="bar.id"
                        class="table-item absolute group select-none touch-none"
                        :style="`left:${bar.position_x}px; top:${bar.position_y}px;
                                 width:${bar.width}px; height:${bar.height}px;
                                 transform:rotate(${bar.rotation ?? 0}deg);
                                 transform-origin:center;
                                 z-index:10;`"
                        :aria-label="`Barra: ${bar.name}`"
                    >
                        <div class="w-full h-full relative flex items-center justify-center
                                    rounded-lg
                                    bg-amber-100 dark:bg-amber-900/40
                                    border-2 border-amber-400 dark:border-amber-600
                                    shadow-md cursor-grab active:cursor-grabbing
                                    transition-shadow hover:shadow-lg">

                            <span class="text-xs font-semibold text-amber-800 dark:text-amber-300
                                         text-center px-1 leading-tight pointer-events-none"
                                  x-text="bar.name">
                            </span>

                            <span class="absolute top-1 left-1 text-xs text-amber-600 dark:text-amber-400 pointer-events-none leading-none">🍺</span>

                            {{-- Botón editar nombre --}}
                            <button type="button"
                                    @click.stop="editingTableId = editingTableId === bar.id ? null : bar.id"
                                    class="absolute -top-2.5 -right-9
                                           w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                           shadow-md"
                                    :aria-label="`Editar ${bar.name}`"
                                    :aria-expanded="editingTableId === bar.id">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    @click.stop="deleteElement(bar)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :aria-label="`Eliminar ${bar.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Panel de edición de nombre --}}
                            <div x-show="editingTableId === bar.id"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 @click.stop
                                 class="absolute top-7 right-0 z-20
                                        bg-white dark:bg-gray-800 rounded-xl shadow-xl
                                        border border-gray-200 dark:border-gray-700
                                        p-3 min-w-max"
                                 role="dialog"
                                 :aria-label="`Editar ${bar.name}`">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                                <div class="flex gap-1">
                                    <input type="text"
                                           :value="bar.name"
                                           @keydown.enter.stop="updateName(bar, $event.target.value); $event.target.blur()"
                                           @blur.stop="updateName(bar, $event.target.value)"
                                           @click.stop
                                           maxlength="50"
                                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                                  px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                           :aria-label="`Nombre de ${bar.name}`">
                                </div>
                            </div>
                        </div>

                        {{-- Handle de rotación --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    opacity-0 group-hover:opacity-100 transition-opacity z-10"
                             @mousedown.stop.prevent="startRotation($event, bar)"
                             @mouseenter.stop="rotTooltip = { show: true, x: $event.clientX, y: $event.clientY, deg: bar.rotation ?? 0 }"
                             @mousemove.stop="rotTooltip.x = $event.clientX; rotTooltip.y = $event.clientY; rotTooltip.deg = bar.rotation ?? 0"
                             @mouseleave.stop="if (!isRotating) rotTooltip.show = false"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar ${bar.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-amber-400 shadow-md
                                        flex items-center justify-center text-amber-500
                                        transition-all duration-150
                                        hover:bg-amber-500 hover:border-amber-600 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-amber-400"></div>
                        </div>

                        {{-- Handle de redimensionado --}}
                        <div class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startResize($event, bar)"
                             role="button"
                             :aria-label="`Redimensionar ${bar.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-amber-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Taburetes: drag Alpine-nativo --}}
                <template x-for="stool in elements.filter(e => e.shape === 'stool')" :key="'s'+stool.id">
                    <div
                        :data-table-id="stool.id"
                        class="element-item absolute group select-none touch-none"
                        :style="`left:${stool.position_x}px; top:${stool.position_y}px;
                                 width:${stool.width}px; height:${stool.height}px;
                                 transform:rotate(${stool.rotation ?? 0}deg);
                                 transform-origin:center;
                                 z-index:10;`"
                        :aria-label="`Taburete: ${stool.name}`"
                    >
                        <div class="w-full h-full relative flex items-center justify-center
                                    rounded-full
                                    bg-amber-100 dark:bg-amber-900/40
                                    border-2 border-amber-400 dark:border-amber-600
                                    shadow-md cursor-grab active:cursor-grabbing
                                    transition-shadow hover:shadow-lg"
                             @mousedown.prevent="startElementDrag($event, stool)">

                            <span class="text-xs font-semibold text-amber-800 dark:text-amber-300
                                         text-center px-1 leading-tight pointer-events-none"
                                  x-text="stool.name">
                            </span>

                            <span class="absolute top-1 left-1 text-xs text-amber-600 dark:text-amber-400 pointer-events-none leading-none">●</span>

                            {{-- Botón editar nombre --}}
                            <button type="button"
                                    @mousedown.stop
                                    @click.stop="editingTableId = editingTableId === stool.id ? null : stool.id"
                                    class="absolute -top-2.5 -right-9
                                           w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                           shadow-md"
                                    :aria-label="`Editar ${stool.name}`"
                                    :aria-expanded="editingTableId === stool.id">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    @mousedown.stop
                                    @click.stop="deleteElement(stool)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :aria-label="`Eliminar ${stool.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Panel de edición de nombre --}}
                            <div x-show="editingTableId === stool.id"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 @click.stop
                                 class="absolute top-7 right-0 z-20
                                        bg-white dark:bg-gray-800 rounded-xl shadow-xl
                                        border border-gray-200 dark:border-gray-700
                                        p-3 min-w-max"
                                 role="dialog"
                                 :aria-label="`Editar ${stool.name}`">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                                <div class="flex gap-1">
                                    <input type="text"
                                           :value="stool.name"
                                           @keydown.enter.stop="updateName(stool, $event.target.value); $event.target.blur()"
                                           @blur.stop="updateName(stool, $event.target.value)"
                                           @click.stop
                                           maxlength="50"
                                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                                  px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                           :aria-label="`Nombre de ${stool.name}`">
                                </div>
                            </div>
                        </div>

                        {{-- Handle de rotación --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    opacity-0 group-hover:opacity-100 transition-opacity z-10"
                             @mousedown.stop.prevent="startRotation($event, stool)"
                             @mouseenter.stop="rotTooltip = { show: true, x: $event.clientX, y: $event.clientY, deg: stool.rotation ?? 0 }"
                             @mousemove.stop="rotTooltip.x = $event.clientX; rotTooltip.y = $event.clientY; rotTooltip.deg = stool.rotation ?? 0"
                             @mouseleave.stop="if (!isRotating) rotTooltip.show = false"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar ${stool.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-amber-400 shadow-md
                                        flex items-center justify-center text-amber-500
                                        transition-all duration-150
                                        hover:bg-amber-500 hover:border-amber-600 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-amber-400"></div>
                        </div>

                        {{-- Handle de redimensionado --}}
                        <div class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startResize($event, stool)"
                             role="button"
                             :aria-label="`Redimensionar ${stool.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-amber-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Mesas existentes --}}
                <template x-for="table in tables" :key="table.id">
                    <div
                        :data-table-id="table.id"
                        class="table-item absolute group select-none touch-none"
                        :style="`left:${table.position_x}px; top:${table.position_y}px;
                                 width:${table.width}px; height:${table.height}px;
                                 transform: rotate(${table.rotation ?? 0}deg);
                                 transform-origin: center;
                                 z-index: 10;`"
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

                            {{-- Badge estado: libre/ocupada --}}
                            <span class="absolute top-1 left-1 w-2 h-2 rounded-full"
                                  :class="table.status === 'occupied' ? 'bg-red-500' : 'bg-green-400'"
                                  :title="table.status === 'occupied' ? 'Ocupada' : 'Libre'">
                            </span>

                            {{-- Badge solicitud de cuenta en efectivo --}}
                            <span x-show="table.bill_requested && table.requested_payment_method === 'cash'"
                                  class="absolute bottom-1 right-1
                                         flex items-center justify-center
                                         w-5 h-5 rounded-full
                                         bg-amber-500 text-white text-xs font-bold
                                         shadow-md animate-pulse"
                                  title="Solicita cuenta en efectivo"
                                  aria-label="Mesa solicita cuenta en efectivo">
                                €
                            </span>

                            {{-- Badge solicitud de cuenta con tarjeta --}}
                            <span x-show="table.bill_requested && table.requested_payment_method === 'card'"
                                  class="absolute bottom-1 right-1
                                         flex items-center justify-center
                                         w-5 h-5 rounded-full
                                         bg-emerald-500 text-white text-xs font-bold
                                         shadow-md animate-pulse"
                                  title="Solicita cuenta con tarjeta"
                                  aria-label="Mesa solicita cuenta con tarjeta">
                                ♦
                            </span>

                            {{-- Botón editar forma — solo admin --}}
                            <button type="button"
                                    x-show="!readonly"
                                    @click.stop="editingTableId = editingTableId === table.id ? null : table.id"
                                    class="absolute -top-2.5 -right-16
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

                            {{-- Botón QR --}}
                            <button type="button"
                                    @click.stop="$store.qrModal.open(table)"
                                    class="absolute -top-2.5 -right-9
                                           w-6 h-6 rounded-full bg-indigo-500 text-white
                                           flex items-center justify-center
                                           opacity-0 group-hover:opacity-100 transition-opacity
                                           hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           shadow-md"
                                    :aria-label="`Ver QR de la mesa ${table.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm9-2h7v7h-7V3zm2 2v3h3V5h-3zM3 14h7v7H3v-7zm2 2v3h3v-3H5zm11.5-2a.5.5 0 01.5.5v1h1.5a.5.5 0 010 1H17v1.5a.5.5 0 01-1 0V17h-1.5a.5.5 0 010-1H16v-1.5a.5.5 0 01.5-.5zm3 3a.5.5 0 01.5.5V21h-2.5a.5.5 0 010-1H21v-1.5a.5.5 0 01.5-.5z"/>
                                </svg>
                            </button>

                            {{-- Botón eliminar — solo admin --}}
                            <button type="button"
                                    x-show="!readonly"
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

                                {{-- Editar nombre --}}
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                                <div class="flex gap-1 mb-3">
                                    <input type="text"
                                           :value="table.name"
                                           @keydown.enter.stop="updateName(table, $event.target.value); $event.target.blur()"
                                           @blur.stop="updateName(table, $event.target.value)"
                                           @click.stop
                                           maxlength="50"
                                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                                  px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           :aria-label="`Nombre de la mesa ${table.name}`">
                                </div>

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

                        {{-- Handle de rotación — solo admin --}}
                        <div x-show="!readonly" class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    opacity-0 group-hover:opacity-100 transition-opacity z-10"
                             @mousedown.stop.prevent="startRotation($event, table)"
                             @mouseenter.stop="rotTooltip = { show: true, x: $event.clientX, y: $event.clientY, deg: table.rotation ?? 0 }"
                             @mousemove.stop="rotTooltip.x = $event.clientX; rotTooltip.y = $event.clientY; rotTooltip.deg = table.rotation ?? 0"
                             @mouseleave.stop="if (!isRotating) rotTooltip.show = false"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar mesa ${table.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-indigo-400 shadow-md
                                        flex items-center justify-center text-indigo-500
                                        transition-all duration-150
                                        hover:bg-indigo-600 hover:border-indigo-700 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-indigo-400"></div>
                        </div>

                        {{-- Handle de redimensionado — solo admin --}}
                        <div x-show="!readonly"
                             class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startResize($event, table)"
                             role="button"
                             :aria-label="`Redimensionar mesa ${table.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-indigo-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Indicador de grados de rotación (sigue al cursor) --}}
                <div x-show="rotTooltip.show"
                     x-transition:enter="transition ease-out duration-75"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="fixed pointer-events-none z-[200] select-none"
                     :style="`left:${rotTooltip.x + 18}px; top:${rotTooltip.y - 10}px`"
                     aria-hidden="true">
                    <span class="inline-flex items-center gap-0.5
                                 bg-gray-900/90 text-white
                                 text-xs font-mono font-semibold
                                 px-1.5 py-0.5 rounded-md shadow-lg ring-1 ring-white/10">
                        <span x-text="rotTooltip.deg > 180 ? rotTooltip.deg - 360 : rotTooltip.deg"></span>°
                    </span>
                </div>

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

{{-- Modal de QR de mesa --}}
<div x-data
     x-show="$store.qrModal.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     aria-modal="true"
     role="dialog"
     aria-labelledby="qr-modal-title"
     @keydown.escape.window="$store.qrModal.close()">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.stop>

        <div class="flex items-center justify-between mb-4">
            <h2 id="qr-modal-title"
                class="text-lg font-bold text-gray-900 dark:text-white"
                x-text="`QR — ${$store.qrModal.table?.name ?? ''}`">
            </h2>
            <button type="button"
                    @click="$store.qrModal.close()"
                    class="w-8 h-8 rounded-full flex items-center justify-center
                           text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors"
                    aria-label="Cerrar modal de QR">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- QR SVG inline --}}
        <div class="flex justify-center mb-4 p-3 bg-white rounded-xl border border-gray-200 dark:border-gray-700">
            <img :src="`/mesas/${$store.qrModal.table?.id}/qr`"
                 :alt="`Código QR de la mesa ${$store.qrModal.table?.name}`"
                 class="w-48 h-48"
                 x-show="$store.qrModal.table">
        </div>

        {{-- URL de la carta --}}
        <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-1 font-medium uppercase tracking-wide">
            Enlace de la carta
        </p>
        <p class="text-xs text-center text-indigo-600 dark:text-indigo-400 break-all mb-4 font-mono"
           x-text="`${window.location.origin}/carta/${$store.qrModal.table?.unique_hash ?? ''}`">
        </p>

        <div class="flex gap-3">
            <button type="button"
                    @click="$store.qrModal.close()"
                    class="flex-1 px-4 py-2 rounded-xl text-sm font-medium
                           text-gray-700 dark:text-gray-200
                           bg-gray-100 dark:bg-gray-700
                           hover:bg-gray-200 dark:hover:bg-gray-600
                           focus:outline-none focus:ring-2 focus:ring-gray-400
                           transition-colors">
                Cerrar
            </button>
            <a :href="`/mesas/${$store.qrModal.table?.id}/qr/descargar`"
               class="flex-1 px-4 py-2 rounded-xl text-sm font-medium text-center text-white
                      bg-indigo-600 hover:bg-indigo-700
                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                      transition-colors inline-flex items-center justify-center gap-1.5"
               aria-label="Descargar QR en SVG">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Descargar SVG
            </a>
        </div>
    </div>
</div>

{{-- Modal de confirmación de eliminación --}}
<div x-data
     x-show="$store.deleteModal.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     aria-modal="true"
     role="alertdialog"
     aria-labelledby="delete-modal-title"
     aria-describedby="delete-modal-desc"
     @keydown.escape.window="$store.deleteModal.resolve(false)">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.stop>

        {{-- Icono de advertencia --}}
        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4
                    rounded-full bg-red-100 dark:bg-red-900/30">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>

        <h2 id="delete-modal-title"
            class="text-lg font-bold text-center text-gray-900 dark:text-white mb-2">
            Eliminar mesa
        </h2>

        <p id="delete-modal-desc"
           class="text-sm text-center text-gray-500 dark:text-gray-400 mb-6">
            ¿Eliminar <span class="font-semibold text-gray-700 dark:text-gray-200"
                            x-text="`&quot;${$store.deleteModal.table?.name}&quot;`"></span>?
            Esta acción no se puede deshacer.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    @click="$store.deleteModal.resolve(false)"
                    class="flex-1 px-4 py-2 rounded-xl text-sm font-medium
                           text-gray-700 dark:text-gray-200
                           bg-gray-100 dark:bg-gray-700
                           hover:bg-gray-200 dark:hover:bg-gray-600
                           focus:outline-none focus:ring-2 focus:ring-gray-400
                           transition-colors">
                Cancelar
            </button>
            <button type="button"
                    @click="$store.deleteModal.resolve(true)"
                    x-init="$watch('$store.deleteModal.show', v => v && $nextTick(() => $el.focus()))"
                    class="flex-1 px-4 py-2 rounded-xl text-sm font-medium text-white
                           bg-red-600 hover:bg-red-700
                           focus:outline-none focus:ring-2 focus:ring-red-400
                           transition-colors">
                Eliminar
            </button>
        </div>
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

    // ── Store para el modal de QR de mesa ────────────────────────────────────
    Alpine.store('qrModal', {
        show:  false,
        table: null,

        open(table) {
            this.table = table;
            this.show  = true;
        },

        close() {
            this.show  = false;
            this.table = null;
        },
    });

    // ── Store para el modal de confirmación de borrado ────────────────────────
    Alpine.store('deleteModal', {
        show:     false,
        table:    null,
        _resolve: null,

        prompt(table) {
            this.table = table;
            this.show  = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        resolve(confirmed) {
            this.show = false;
            this._resolve?.(confirmed);
            this.table    = null;
            this._resolve = null;
        },
    });

    // ── Componente principal del mapa ─────────────────────────────────────────
    Alpine.data('tableMap', () => ({
        tables:                @json($tables),
        elements:              @json($elements),
        zones:                 @json($zones),
        floorWidth:            {{ $floorWidth }},
        floorHeight:           {{ $floorHeight }},
        readonly:              {{ $readonly ? 'true' : 'false' }},
        canvasZoom:            1,
        isDraggingFromPalette: false,
        isDraggingZone:        false,
        editingTableId:        null,
        editingZoneId:         null,
        isRotating:            false,
        isRotatingZone:        false,
        zoneColor:             '#6366f1',
        toast:                 { show: false, msg: '', error: false, _timer: null },
        rotTooltip:            { show: false, x: 0, y: 0, deg: 0 },

        init() {
            // El tamaño del canvas viene de BD (floorWidth/floorHeight).
            // Solo el zoom es local (visual), keyed por usuario para evitar mezcla entre cuentas.
            const lsZoom   = 'zampa:mapZoom:user:{{ auth()->id() }}';
            const savedZoom = parseFloat(localStorage.getItem(lsZoom));
            if (!isNaN(savedZoom) && savedZoom >= 0.5 && savedZoom <= 1) {
                this.canvasZoom = savedZoom;
            }
            this.$watch('canvasZoom', val => localStorage.setItem(lsZoom, val));

            this.$nextTick(() => {
                if (!this.readonly) {
                    this.initTableInteract();
                    this.initZoneInteract();
                    this.initPaletteInteract();
                }
            });

            // Polling de estados: actualiza ocupación y solicitudes de cuenta cada 5 s.
            this.pollStatuses();
            setInterval(() => this.pollStatuses(), 5000);
        },

        async pollStatuses() {
            try {
                const res  = await fetch('{{ route("tables.map.statuses") }}', {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                data.forEach(s => {
                    const t = this.tables.find(t => t.id === s.id);
                    if (t) {
                        t.status                   = s.status;
                        t.bill_requested           = s.bill_requested;
                        t.requested_payment_method = s.requested_payment_method;
                    }
                });
            } catch {}
        },

        // ── Cambiar tamaño del lienzo y persistir en BD ───────────────────────
        async setCanvasSize(w, h) {
            const prev = { w: this.floorWidth, h: this.floorHeight };
            this.floorWidth  = w;
            this.floorHeight = h;

            try {
                const res = await fetch('{{ route("tables.canvas.update") }}', {
                    method:  'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ floor_width: w, floor_height: h }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    this.floorWidth  = prev.w;
                    this.floorHeight = prev.h;
                    this.showToast(json.message ?? 'Error al guardar el tamaño.', true);
                    return;
                }

                this.showToast(`Plano ${w} × ${h} px guardado.`);
            } catch {
                this.floorWidth  = prev.w;
                this.floorHeight = prev.h;
                this.showToast('Error de red al guardar el tamaño.', true);
            }
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
                    ignoreFrom:  '.rotation-handle, .resize-handle',
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
                            const el   = event.target;
                            const x    = (parseFloat(el.style.left) || 0) + event.dx;
                            const y    = (parseFloat(el.style.top)  || 0) + event.dy;
                            el.style.left = `${x}px`;
                            el.style.top  = `${y}px`;
                            // Sync Alpine data during drag so `:style` re-renders never overwrite interact.js
                            const id   = parseInt(el.dataset.tableId);
                            const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
                            if (item) {
                                item.position_x = Math.round(x);
                                item.position_y = Math.round(y);
                            }
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
                ;
        },

        // ── Reinicializar interact después de añadir una mesa ─────────────────
        reinitInteract() {
            this.$nextTick(() => this.initTableInteract());
        },

        // ── Interactividad de zonas (drag nativo Alpine para mover) ──────────
        initZoneInteract() {
            interact('.zone-item').unset();
        },

        reinitZoneInteract() {
            this.$nextTick(() => this.initZoneInteract());
        },

        // ── Drag nativo de zona: actualiza zone.position_x/y reactivamente ───
        startZoneDrag(event, zone) {
            const canvasEl  = this.$refs.canvas;
            const startMX   = event.clientX;
            const startMY   = event.clientY;
            const startPx   = zone.position_x;
            const startPy   = zone.position_y;

            document.body.style.cursor = 'grabbing';

            const onMove = (e) => {
                zone.position_x = Math.max(0, Math.round(startPx + (e.clientX - startMX)));
                zone.position_y = Math.max(0, Math.round(startPy + (e.clientY - startMY)));
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                document.body.style.cursor = '';
                await this.persistZonePosition(zone.id, zone.position_x, zone.position_y);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── Rotación libre de zona arrastrando el handle ─────────────────────
        startZoneRotation(event, zone) {
            const canvasRect = this.$refs.canvas.getBoundingClientRect();
            const centerX    = canvasRect.left + zone.position_x + zone.width  / 2;
            const centerY    = canvasRect.top  + zone.position_y + zone.height / 2;

            this.isRotating            = true;
            this.rotTooltip.show       = true;
            document.body.style.cursor = "url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grabbing";

            const onMove = (e) => {
                const dx    = e.clientX - centerX;
                const dy    = e.clientY - centerY;
                let   angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
                angle = ((angle % 360) + 360) % 360;
                zone.rotation       = Math.round(angle);
                this.rotTooltip.x   = e.clientX;
                this.rotTooltip.y   = e.clientY;
                this.rotTooltip.deg = zone.rotation;
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                this.isRotating            = false;
                this.rotTooltip.show       = false;
                document.body.style.cursor = '';
                await this.persistZoneRotation(zone.id, zone.rotation ?? 0);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── Drag nativo de elemento especial (barra/taburete) ────────────────
        startElementDrag(event, element) {
            const startPx = element.position_x;
            const startPy = element.position_y;
            const startMX = event.clientX;
            const startMY = event.clientY;

            document.body.style.cursor = 'grabbing';

            const onMove = (e) => {
                const canvas = this.$refs.canvas;
                const maxX = Math.max(0, canvas.offsetWidth  - element.width);
                const maxY = Math.max(0, canvas.offsetHeight - element.height);
                element.position_x = Math.max(0, Math.min(maxX, Math.round(startPx + (e.clientX - startMX))));
                element.position_y = Math.max(0, Math.min(maxY, Math.round(startPy + (e.clientY - startMY))));
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                document.body.style.cursor = '';
                await this.persistPosition(element.id, element.position_x, element.position_y, element.width, element.height);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── Paleta: drag-to-create (mesas, especiales y zonas) ────────────────
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
                        if (this.canvasZoom < 1) {
                            event.interaction.stop();
                            this.showToast('Ajusta el zoom al 100% para añadir mesas.', true);
                            return;
                        }

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

                        // getBoundingClientRect devuelve coords visuales (post-scale), hay que dividir por zoom
                        dropX = Math.max(0, Math.round((cx - canvasRect.left - dropW / 2) / this.canvasZoom));
                        dropY = Math.max(0, Math.round((cy - canvasRect.top  - dropH / 2) / this.canvasZoom));
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

            // ── Paleta de elementos especiales (barra, taburete) ─────────────
            interact('.special-item').draggable({
                inertia: false,
                listeners: {
                    start: (event) => {
                        if (this.canvasZoom < 1) {
                            event.interaction.stop();
                            this.showToast('Ajusta el zoom al 100% para añadir elementos.', true);
                            return;
                        }

                        const shape = event.target.dataset.shape;
                        const dropW = parseInt(event.target.dataset.width)  || 80;
                        const dropH = parseInt(event.target.dataset.height) || 50;

                        ghost.style.width        = `${dropW}px`;
                        ghost.style.height       = `${dropH}px`;
                        ghost.style.borderRadius = shape === 'stool' ? '9999px' : '8px';
                        ghost.style.borderColor  = '#d97706';
                        ghost.style.background   = 'rgba(251,191,36,0.3)';
                        ghost.classList.remove('hidden');
                        ghost.classList.add('flex');
                        this.isDraggingFromPalette = true;
                    },
                    move: (event) => {
                        const w = parseInt(event.target.dataset.width)  || 80;
                        const h = parseInt(event.target.dataset.height) || 50;
                        ghost.style.left = `${event.clientX - w / 2}px`;
                        ghost.style.top  = `${event.clientY - h / 2}px`;
                        const rect = canvasEl.getBoundingClientRect();
                        dropX = Math.max(0, Math.round((event.clientX - rect.left - w / 2) / this.canvasZoom));
                        dropY = Math.max(0, Math.round((event.clientY - rect.top  - h / 2) / this.canvasZoom));
                        dropShape = event.target.dataset.shape;
                        dropW     = w;
                        dropH     = h;
                    },
                    end: async (event) => {
                        ghost.classList.add('hidden');
                        ghost.classList.remove('flex');
                        ghost.style.borderColor = '';
                        ghost.style.background  = '';
                        this.isDraggingFromPalette = false;

                        const rect = canvasEl.getBoundingClientRect();
                        const over = event.clientX >= rect.left && event.clientX <= rect.right &&
                                     event.clientY >= rect.top  && event.clientY <= rect.bottom;
                        if (!over) return;

                        const name = await Alpine.store('tableModal').prompt();
                        if (!name) return;

                        await this.createSpecialElement(name, dropShape, dropX, dropY, dropW, dropH);
                    },
                },
            });

            // ── Paleta de zonas ───────────────────────────────────────────────
            interact('.zone-palette-item').draggable({
                inertia: false,
                listeners: {
                    start: (event) => {
                        if (this.canvasZoom < 1) {
                            event.interaction.stop();
                            this.showToast('Ajusta el zoom al 100% para añadir zonas.', true);
                            return;
                        }

                        const w = parseInt(event.target.dataset.width)  || 300;
                        const h = parseInt(event.target.dataset.height) || 200;
                        ghost.style.width        = `${w}px`;
                        ghost.style.height       = `${h}px`;
                        ghost.style.borderRadius = '8px';
                        ghost.style.borderColor  = this.zoneColor;
                        ghost.style.background   = this.zoneColor + '33';
                        ghost.classList.remove('hidden');
                        ghost.classList.add('flex');
                        this.isDraggingZone = true;
                    },
                    move: (event) => {
                        const w = parseInt(event.target.dataset.width)  || 300;
                        const h = parseInt(event.target.dataset.height) || 200;
                        ghost.style.left = `${event.clientX - w / 2}px`;
                        ghost.style.top  = `${event.clientY - h / 2}px`;
                        const rect = canvasEl.getBoundingClientRect();
                        dropX = Math.max(0, Math.round((event.clientX - rect.left - w / 2) / this.canvasZoom));
                        dropY = Math.max(0, Math.round((event.clientY - rect.top  - h / 2) / this.canvasZoom));
                        dropW = w;
                        dropH = h;
                    },
                    end: async (event) => {
                        ghost.classList.add('hidden');
                        ghost.classList.remove('flex');
                        ghost.style.borderColor = '';
                        ghost.style.background  = '';
                        this.isDraggingZone = false;

                        const rect = canvasEl.getBoundingClientRect();
                        const over = event.clientX >= rect.left && event.clientX <= rect.right &&
                                     event.clientY >= rect.top  && event.clientY <= rect.bottom;
                        if (!over) return;

                        const name = await Alpine.store('tableModal').prompt();
                        if (!name) return;

                        await this.createZone(name, this.zoneColor, dropX, dropY, dropW, dropH);
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
                        position_x:       x,
                        position_y:       y,
                        width:            w,
                        height:           h,
                        is_service_point: true,
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

        // ── AJAX: crear elemento especial (barra, taburete) ──────────────────
        async createSpecialElement(name, shape, x, y, w, h) {
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
                        position_x:       x,
                        position_y:       y,
                        width:            w,
                        height:           h,
                        is_service_point: false,
                    }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    this.showToast(json.message ?? 'Error al crear el elemento.', true);
                    return;
                }

                this.elements.push(json.data);
                this.reinitInteract();
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red al crear el elemento.', true);
            }
        },

        // ── AJAX: crear zona ──────────────────────────────────────────────────
        async createZone(name, color, x, y, w, h) {
            try {
                const res = await fetch('{{ route("zones.store") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ name, color, position_x: x, position_y: y, width: w, height: h }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    this.showToast(json.message ?? 'Error al crear la zona.', true);
                    return;
                }

                this.zones.push(json.data);
                this.reinitZoneInteract();
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red al crear la zona.', true);
            }
        },

        // ── AJAX: actualizar nombre de zona ───────────────────────────────────
        async updateZoneName(zone, name) {
            name = name.trim();
            if (!name || name === zone.name) return;
            const prev = zone.name;
            zone.name  = name;

            try {
                const res = await fetch(`/zonas/${zone.id}`, {
                    method:  'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ name }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) { zone.name = prev; this.showToast(json.message ?? 'Error.', true); return; }
                this.showToast(json.message);
            } catch {
                zone.name = prev;
                this.showToast('Error de red.', true);
            }
        },

        // ── AJAX: actualizar color de zona ────────────────────────────────────
        async updateZoneColor(zone, color) {
            const prev = zone.color;
            zone.color = color;

            try {
                const res = await fetch(`/zonas/${zone.id}`, {
                    method:  'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ color }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) { zone.color = prev; this.showToast(json.message ?? 'Error.', true); }
            } catch {
                zone.color = prev;
                this.showToast('Error de red.', true);
            }
        },

        // ── AJAX: persistir posición de zona ──────────────────────────────────
        async persistZonePosition(id, x, y) {
            try {
                const res = await fetch(`/zonas/${id}`, {
                    method:  'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ position_x: x, position_y: y }),
                });
                if (!res.ok) this.showToast('Error al guardar la posición de la zona.', true);
            } catch {
                this.showToast('Error de red.', true);
            }
        },

        // ── AJAX: persistir rotación de zona ──────────────────────────────────
        async persistZoneRotation(id, rotation) {
            try {
                const res = await fetch(`/zonas/${id}`, {
                    method:  'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ rotation }),
                });
                if (!res.ok) this.showToast('Error al guardar la rotación de la zona.', true);
            } catch {
                this.showToast('Error de red.', true);
            }
        },

        // ── Resize de zona ────────────────────────────────────────────────────
        startZoneResize(event, zone) {
            const startMX = event.clientX;
            const startMY = event.clientY;
            const startW  = zone.width;
            const startH  = zone.height;
            document.body.style.cursor = 'se-resize';

            const onMove = (e) => {
                zone.width  = Math.min(2000, Math.max(80,  startW + (e.clientX - startMX)));
                zone.height = Math.min(1500, Math.max(60,  startH + (e.clientY - startMY)));
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                document.body.style.cursor = '';
                try {
                    await fetch(`/zonas/${zone.id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ position_x: zone.position_x, position_y: zone.position_y, width: zone.width, height: zone.height }),
                    });
                } catch {
                    this.showToast('Error al guardar dimensiones.', true);
                }
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── AJAX: eliminar zona ───────────────────────────────────────────────
        async deleteZone(zone) {
            const confirmed = await Alpine.store('deleteModal').prompt({ name: `Zona "${zone.name}"` });
            if (!confirmed) return;

            try {
                const res = await fetch(`/zonas/${zone.id}`, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar.', true); return; }
                this.zones = this.zones.filter(z => z.id !== zone.id);
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red.', true);
            }
        },

        // ── AJAX: eliminar elemento especial ──────────────────────────────────
        async deleteElement(element) {
            const confirmed = await Alpine.store('deleteModal').prompt(element);
            if (!confirmed) return;

            try {
                const res = await fetch(`/mesas/${element.id}`, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar.', true); return; }
                this.elements = this.elements.filter(e => e.id !== element.id);
                this.showToast(json.message);
            } catch {
                this.showToast('Error de red.', true);
            }
        },

        // ── Resize libre en espacio local del elemento rotado ────────────────
        startResize(event, table) {
            const θRad    = (table.rotation ?? 0) * Math.PI / 180;
            const cosθ    = Math.cos(θRad);
            const sinθ    = Math.sin(θRad);
            const startMX = event.clientX;
            const startMY = event.clientY;
            const startW  = table.width;
            const startH  = table.height;
            const startPx = table.position_x;
            const startPy = table.position_y;

            document.body.style.cursor = 'se-resize';

            const onMove = (e) => {
                const dx = e.clientX - startMX;
                const dy = e.clientY - startMY;

                // Proyectar delta de pantalla al espacio local del elemento (rotación inversa)
                const localDX =  dx * cosθ + dy * sinθ;
                const localDY = -dx * sinθ + dy * cosθ;

                const newW = Math.min(800, Math.max(40, startW + localDX));
                const newH = Math.min(800, Math.max(40, startH + localDY));
                const dW   = newW - startW;
                const dH   = newH - startH;

                table.width      = Math.round(newW);
                table.height     = Math.round(newH);
                // Corregir posición CSS para que la esquina visual superior-izquierda no se mueva
                table.position_x = Math.max(0, Math.round(startPx + dW / 2 * (cosθ - 1) - dH / 2 * sinθ));
                table.position_y = Math.max(0, Math.round(startPy + dW / 2 * sinθ + dH / 2 * (cosθ - 1)));
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                document.body.style.cursor = '';
                await this.persistPosition(table.id, table.position_x, table.position_y, table.width, table.height);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── AJAX: persistir posición, dimensiones y rotación ─────────────────
        async persistPosition(id, x, y, w, h) {
            const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
            if (item) {
                item.position_x = x;
                item.position_y = y;
                item.width      = w;
                item.height     = h;
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
                        rotation:   item?.rotation ?? 0,
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
            table.shape = shape;
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

        // ── AJAX: actualizar nombre de mesa existente ─────────────────────────
        async updateName(table, name) {
            name = name.trim();
            if (!name || name === table.name) return;

            const prev  = table.name;
            table.name  = name;

            try {
                const res = await fetch(`/mesas/${table.id}/nombre`, {
                    method:  'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ name }),
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    table.name = prev;
                    this.showToast(json.message ?? 'Error al renombrar la mesa.', true);
                    return;
                }

                this.showToast(json.message);
            } catch {
                table.name = prev;
                this.showToast('Error de red al renombrar la mesa.', true);
            }
        },

        // ── Rotación libre arrastrando el handle (estilo Canva) ───────────────
        startRotation(event, table) {
            const canvasRect = this.$refs.canvas.getBoundingClientRect();
            const centerX    = canvasRect.left + table.position_x + table.width  / 2;
            const centerY    = canvasRect.top  + table.position_y + table.height / 2;

            this.isRotating            = true;
            this.rotTooltip.show       = true;
            document.body.style.cursor = "url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grabbing";

            const onMove = (e) => {
                const dx    = e.clientX - centerX;
                const dy    = e.clientY - centerY;
                let   angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
                angle = ((angle % 360) + 360) % 360;
                table.rotation     = Math.round(angle);
                this.rotTooltip.x  = e.clientX;
                this.rotTooltip.y  = e.clientY;
                this.rotTooltip.deg = table.rotation;
            };

            const onUp = async () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
                this.isRotating            = false;
                this.rotTooltip.show       = false;
                document.body.style.cursor = '';
                await this.persistPosition(table.id, table.position_x, table.position_y, table.width, table.height);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        },

        // ── AJAX: eliminar mesa ───────────────────────────────────────────────
        async deleteTable(table) {
            const confirmed = await Alpine.store('deleteModal').prompt(table);
            if (!confirmed) return;

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
