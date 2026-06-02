{{--
 | Bloques 8.1, 8.3 y 8.4 — Mapa visual del local con zonas, elementos especiales y mesas.
 | interact.js para arrastrar/redimensionar mesas, elementos especiales y zonas.
 | @author AyrtonAlania
 | @author SebastianBCF
--}}

<x-app-layout>

{{-- ══════════════════════════════════════════════════════
     OVERLAY — Girar dispositivo (portrait mobile/tablet)
══════════════════════════════════════════════════════ --}}
<style>
    @keyframes zampa-rotate-phone {
        0%   { transform: rotate(0deg); }
        35%  { transform: rotate(90deg); }
        65%  { transform: rotate(90deg); }
        100% { transform: rotate(0deg); }
    }
    #rotate-device-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: rgba(17, 24, 39, 0.97);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        padding: 2rem;
        text-align: center;
    }
    @media screen and (orientation: portrait) and (max-width: 1023px) {
        #rotate-device-overlay { display: flex; }
    }
    #rotate-device-overlay .zampa-phone-icon {
        animation: zampa-rotate-phone 2.5s ease-in-out infinite;
    }
</style>

<div id="rotate-device-overlay"
     role="alertdialog"
     aria-modal="true"
     aria-label="Gira tu dispositivo para usar el mapa de mesas">
    <div class="zampa-phone-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72"
             fill="none" stroke="#818cf8" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="5" y="2" width="14" height="20" rx="2"/>
            <circle cx="12" cy="17" r="1" fill="#818cf8" stroke="none"/>
        </svg>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
         fill="none" stroke="#6366f1" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
         aria-hidden="true">
        <path d="M21 2v6h-6"/>
        <path d="M3 12a9 9 0 0 1 15-6.7L21 8"/>
    </svg>
    <div>
        <p style="font-size:1.25rem; font-weight:700; color:white; margin:0 0 0.5rem;">
            Gira tu dispositivo
        </p>
        <p style="font-size:0.875rem; color:#9ca3af; margin:0; max-width:22rem; line-height:1.5;">
            El mapa de mesas funciona mejor en horizontal.<br>
            Por favor gira tu dispositivo.
        </p>
    </div>
</div>

<div
    class="flex flex-col h-screen bg-gray-100 dark:bg-gray-900"
    x-data="tableMap()"
    x-init="init()"
    @mouseup.window="if(isRotating||isRotatingZone){}"
    @keydown.ctrl.z.window.prevent="undo()"
    @keydown.ctrl.y.window.prevent="redo()"
    @keydown.window="handleKb($event)"
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

        <div class="flex items-center gap-3">
            {{-- ── Leyenda de estados de mesas ──────────────────────────────── --}}
            <ul class="hidden sm:flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 list-none"
                aria-label="Leyenda de estados de mesas">
                <li class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block" aria-hidden="true"></span> Libre
                </li>
                <li class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block" aria-hidden="true"></span> Ocupada
                </li>
                <li class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-600 inline-block" aria-hidden="true"></span> Lista
                </li>
                <li class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block" aria-hidden="true"></span> Cobro
                </li>
            </ul>

            {{-- ── Control de tamaño del lienzo — solo admin ──────────────── --}}
            <div x-show="!readonly && editMode" class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 hidden sm:block"
                      aria-hidden="true">Plano:</span>

                {{-- Botones S / M / L / XL — ocultos en vista general (tamaño automático) --}}
                <div x-show="!(floorsEnabled && currentView === 'general')"
                     class="hidden sm:flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600"
                     role="group"
                     aria-label="Tamaño del lienzo del plano">
                    <button type="button"
                            @click="requestCanvasSize(1200, 800, 'S')"
                            :aria-pressed="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 1200 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 800"
                            :class="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 1200 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 800
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo pequeño: 1200 × 800 px">S</button>
                    <button type="button"
                            @click="requestCanvasSize(1600, 1000, 'M')"
                            :aria-pressed="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 1600 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1000"
                            :class="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 1600 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1000
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo mediano: 1600 × 1000 px">M</button>
                    <button type="button"
                            @click="requestCanvasSize(2000, 1200, 'L')"
                            :aria-pressed="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 2000 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1200"
                            :class="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 2000 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1200
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold border-r border-gray-200 dark:border-gray-600
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo grande: 2000 × 1200 px">L</button>
                    <button type="button"
                            @click="requestCanvasSize(2400, 1500, 'XL')"
                            :aria-pressed="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 2400 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1500"
                            :class="(floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).width === 2400 && (floorCanvasSizes[currentFloor] ?? {width:floorWidth,height:floorHeight}).height === 1500
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-xs font-semibold transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                            aria-label="Lienzo extra grande: 2400 × 1500 px">XL</button>
                </div>

                {{-- Botones Deshacer / Rehacer --}}
                <div class="flex items-center gap-1">
                    <button type="button"
                            @click="undo()"
                            :disabled="undoStack.length === 0"
                            :class="undoStack.length === 0
                                ? 'opacity-40 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-600'"
                            class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            aria-label="Deshacer último cambio (Ctrl+Z)"
                            title="Deshacer (Ctrl+Z)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                        </svg>
                    </button>
                    <button type="button"
                            @click="redo()"
                            :disabled="redoStack.length === 0"
                            :class="redoStack.length === 0
                                ? 'opacity-40 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-600'"
                            class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            aria-label="Rehacer cambio (Ctrl+Y)"
                            title="Rehacer (Ctrl+Y)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3"/>
                        </svg>
                    </button>
                </div>

                {{-- Zoom slider (solo visual, sin persistencia en BD) --}}
                <div class="hidden sm:flex items-center gap-1.5">
                    <label for="canvas-zoom" class="sr-only">Zoom del plano</label>
                    <input id="canvas-zoom"
                           type="range"
                           min="0.5"
                           @mousedown="pushUndo()"
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

            {{-- ── Toggle Plantas — solo en modo edición ──────────────────── --}}
            <template x-if="!readonly && editMode">
                <label class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer select-none"
                       id="floors-toggle-desc">
                    <button type="button"
                            role="switch"
                            :aria-checked="floorsEnabled"
                            @click="toggleFloorsEnabled(!floorsEnabled)"
                            aria-describedby="floors-toggle-desc"
                            :class="floorsEnabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex w-9 h-5 rounded-full transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1">
                        <span :class="floorsEnabled ? 'translate-x-4' : 'translate-x-0.5'"
                              class="inline-block w-4 h-4 mt-0.5 bg-white rounded-full shadow transition-transform">
                        </span>
                    </button>
                    <span class="hidden sm:inline">Plantas</span>
                </label>
            </template>

            {{-- Botón Editar / Confirmar — solo propietario --}}
            <template x-if="!readonly">
                <div class="flex items-center gap-2">
                    <button type="button"
                            x-show="!editMode"
                            @click="Alpine.store('viewPanel').close(); editMode = true; switchFloor(currentFloor)"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white
                                   bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 transition-colors"
                            aria-label="Entrar en modo edición del mapa">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                        Editar mapa
                    </button>
                    <button type="button"
                            x-show="editMode"
                            @click="exitEditMode()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white
                                   bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                            aria-label="Confirmar cambios y salir del modo edición">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </template>

            {{-- Botón de ayuda de atajos de teclado --}}
            <button type="button"
                    @click="$store.helpModal.show = true"
                    class="flex items-center justify-center w-7 h-7 rounded-full border-2
                           border-gray-300 dark:border-gray-500
                           text-gray-500 dark:text-gray-400 text-sm font-bold
                           hover:border-indigo-500 hover:text-indigo-600 dark:hover:border-indigo-400 dark:hover:text-indigo-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    aria-label="Ver atajos de teclado"
                    title="Atajos de teclado (?)">
                ?
            </button>

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
         BARRA DE PLANTAS — aparece bajo el topbar cuando floorsEnabled
    ══════════════════════════════════════════════════════ --}}
    <nav x-show="floorsEnabled"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="flex-shrink-0 flex items-center gap-1.5 px-4 sm:px-6 py-1.5
                bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700"
         aria-label="Selector de planta">

        <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider pr-1"
              aria-hidden="true">Planta</span>

        {{-- Botón por cada planta --}}
        <template x-for="n in floorCount" :key="n">
            <button type="button"
                    @click="switchFloor(n)"
                    :aria-pressed="currentView === 'floor' && currentFloor === n"
                    :aria-label="`Ir a Planta ${n}`"
                    :class="currentView === 'floor' && currentFloor === n
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                    class="px-2.5 py-1 rounded text-xs font-semibold border transition-colors
                           focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                    x-text="`P${n}`">
            </button>
        </template>

        {{-- Vista General — solo cuando hay más de una planta --}}
        <button type="button"
                x-show="floorCount > 1"
                @click="switchView('general')"
                :aria-pressed="currentView === 'general'"
                :class="currentView === 'general'
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                class="px-2.5 py-1 rounded text-xs font-semibold border transition-colors
                       focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                aria-label="Vista general de todas las plantas">
            General
        </button>

        {{-- Separador visual antes de acciones --}}
        <span x-show="editMode"
              class="w-px h-4 bg-gray-300 dark:bg-gray-600 mx-0.5"
              aria-hidden="true"></span>

        {{-- Añadir planta — solo en modo edición --}}
        <button type="button"
                x-show="editMode && floorCount < 5"
                @click="addFloor()"
                aria-label="Añadir planta"
                class="px-2 py-1 rounded text-xs font-semibold
                       bg-green-500 hover:bg-green-600 text-white border border-green-500
                       transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-400">
            + P
        </button>

        {{-- Eliminar planta — solo en modo edición --}}
        <button type="button"
                x-show="editMode && floorCount > 1"
                @click="confirmDeleteFloor(floorCount)"
                :aria-label="`Eliminar Planta ${floorCount}`"
                class="px-2 py-1 rounded text-xs font-semibold
                       bg-red-500 hover:bg-red-600 text-white border border-red-500
                       transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-400">
            − P<span x-text="floorCount"></span>
        </button>
    </nav>

    {{-- ══════════════════════════════════════════════════════
         BODY — Paleta + Canvas
    ══════════════════════════════════════════════════════ --}}
    <div class="relative flex flex-1 overflow-hidden" x-data="{ paletteOpen: window.innerWidth >= 768 }">

        {{-- ── PALETA LATERAL — solo visible para admin ─────── --}}
        {{-- Backdrop móvil — cierra la paleta al tocar fuera --}}
        <div x-show="!readonly && editMode && paletteOpen"
             @click="paletteOpen = false"
             class="sm:hidden absolute inset-0 z-20 bg-black/40"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             aria-hidden="true">
        </div>

        <aside x-show="!readonly && editMode && paletteOpen"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full opacity-0"
               x-transition:enter-end="translate-x-0 opacity-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0 opacity-100"
               x-transition:leave-end="-translate-x-full opacity-0"
               class="absolute sm:static top-0 left-0 h-full sm:h-auto
                      flex-shrink-0 w-44 bg-white dark:bg-gray-800
                      border-r border-gray-200 dark:border-gray-700
                      flex flex-col p-3 gap-4 overflow-y-auto
                      z-30 sm:z-auto shadow-xl sm:shadow-none">

            {{-- Botón cerrar paleta — solo mobile --}}
            <button type="button"
                    @click="paletteOpen = false"
                    class="sm:hidden self-end p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 -mt-1 -mr-1"
                    aria-label="Cerrar paleta de elementos">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

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
                <div class="w-10 h-10 rounded-full border-2 border-dashed border-green-500
                            bg-green-50 dark:bg-green-900/30
                            group-hover:border-green-600 group-hover:bg-green-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="6"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Taburete</span>
            </div>

            {{-- Silla --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="chair" data-width="50" data-height="60"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-10 h-12 rounded border-2 border-dashed border-green-500
                            bg-green-50 dark:bg-green-900/30
                            group-hover:border-green-600 group-hover:bg-green-100 transition-colors
                            flex items-center justify-center relative">
                    <svg aria-hidden="true" class="w-6 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 32">
                        <rect x="3" y="0" width="18" height="8" rx="2"/>
                        <rect x="3" y="10" width="18" height="14" rx="2"/>
                        <line x1="5" y1="24" x2="5" y2="32"/>
                        <line x1="19" y1="24" x2="19" y2="32"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Silla</span>
            </div>

            {{-- Chimenea --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="fireplace" data-width="80" data-height="80"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-12 h-12 rounded border-2 border-dashed border-red-700
                            bg-red-50 dark:bg-red-900/30
                            group-hover:border-red-800 group-hover:bg-red-100 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-7 h-7 text-red-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="10" width="18" height="11" rx="1"/>
                        <path d="M7 10V7a5 5 0 0110 0v3"/>
                        <line x1="9" y1="21" x2="9" y2="17"/>
                        <line x1="15" y1="21" x2="15" y2="17"/>
                        <path d="M12 3 C10 5 13 7 11 9" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Chimenea</span>
            </div>

            {{-- Pilar --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="pillar" data-width="40" data-height="40"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-10 h-10 rounded-full border-2 border-dashed border-gray-500
                            bg-gray-100 dark:bg-gray-700/40
                            group-hover:border-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <ellipse cx="12" cy="4" rx="6" ry="2"/>
                        <rect x="9" y="4" width="6" height="16"/>
                        <ellipse cx="12" cy="20" rx="6" ry="2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Pilar</span>
            </div>

            {{-- Columna --}}
            <div class="special-item group flex flex-col items-center gap-2 select-none cursor-grab active:cursor-grabbing transition-opacity"
                 data-shape="column" data-width="35" data-height="35"
                 title="Arrastrar al plano (no genera QR)">
                <div class="w-9 h-9 rounded border-2 border-dashed border-gray-500
                            bg-gray-100 dark:bg-gray-700/40
                            group-hover:border-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors
                            flex items-center justify-center">
                    <svg aria-hidden="true" class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="3" rx="1"/>
                        <rect x="8" y="5" width="8" height="14"/>
                        <rect x="4" y="19" width="16" height="3" rx="1"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Columna</span>
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
        <main id="main-content"
              class="flex-1 p-4 relative"
              :class="(editMode && currentView !== 'general') ? 'overflow-auto' : 'overflow-hidden flex items-center justify-center'">

            {{-- Toggle paleta — solo mobile, solo en modo edición --}}
            <button type="button"
                    x-show="!readonly && editMode && !paletteOpen"
                    @click="paletteOpen = true"
                    class="sm:hidden absolute top-3 left-3 z-10
                           inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium
                           bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                           text-gray-600 dark:text-gray-300 shadow-md
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors"
                    aria-label="Abrir paleta de elementos">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                Formas
            </button>

            {{-- Mensaje inferior modo vista — fuera del canvas para no escalar con él --}}
            <div x-show="!readonly && !editMode"
                 class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                             bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300
                             border border-amber-300 dark:border-amber-600 shadow-sm whitespace-nowrap">
                    Pulsa "Editar mapa" para mover o modificar estructuras
                </span>
            </div>

            {{-- Banner vista general — fuera del canvas para no escalar con él --}}
            <div x-show="floorsEnabled && currentView === 'general'"
                 aria-live="polite"
                 class="absolute top-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                             bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300
                             border border-indigo-200 dark:border-indigo-700 shadow-sm whitespace-nowrap">
                    Vista general — todas las plantas visibles · interacción desactivada
                </span>
            </div>

            {{-- Badge planta activa — fuera del canvas para no escalar con él --}}
            <div x-show="floorsEnabled && currentView === 'floor'"
                 aria-live="polite"
                 class="absolute top-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                             bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-gray-300
                             border border-gray-200 dark:border-gray-600 shadow-sm whitespace-nowrap">
                    <svg aria-hidden="true" class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11M8 10v11M16 10v11M12 10v11"/>
                    </svg>
                    Planta <span x-text="currentFloor"></span>
                    <template x-if="!editMode">
                        <span class="text-gray-400 dark:text-gray-500">· solo vista</span>
                    </template>
                </span>
            </div>

            <div
                x-ref="canvas"
                class="relative bg-white dark:bg-gray-800 rounded-xl shadow-inner
                       border-2 border-dashed border-gray-200 dark:border-gray-700"
                :style="(editMode && currentView !== 'general')
                    ? `width:${floorWidth}px; height:${floorHeight}px; transform:scale(${canvasZoom}); transform-origin:top left; margin-bottom:${-floorHeight*(1-canvasZoom)}px; margin-right:${-floorWidth*(1-canvasZoom)}px;`
                    : `width:${floorWidth}px; height:${floorHeight}px; transform:scale(${canvasZoom}); transform-origin:center center; flex-shrink:0;`"
                role="application"
                aria-label="Plano interactivo del restaurante. En modo edición: Tab navega entre elementos, Flechas mueven (Mayús = 1 px), Alt+Flechas redimensiona, R rota izquierda (Mayús = 1°), E rota derecha (Mayús = 1°), Supr elimina, Ctrl+Z deshace, Ctrl+Y rehace. En vértices: Tab navega, Flechas mueven el vértice activo."
                :class="(editMode && currentView !== 'general' && !isPanning) ? 'cursor-grab' : ''"
                @mousedown.self="startPan($event)"
                @click="editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null; selectedId = null; closeContextMenu(); Alpine.store('viewPanel').close();"
                @contextmenu.prevent="closeContextMenu()"
            >

                {{-- Overlay modo visualización: bloquea drag pero deja pasar clics a las mesas --}}
                <div x-show="!readonly && !editMode"
                     class="absolute inset-0 z-[90] rounded-xl select-none pointer-events-none"
                     aria-hidden="true">
                </div>

                {{-- Overlay vista general: bloquea toda interacción con estructuras --}}
                <div x-show="floorsEnabled && currentView === 'general'"
                     class="absolute inset-0 z-[100] rounded-xl cursor-default select-none"
                     aria-hidden="true">
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
                <template x-for="zone in visibleZones()" :key="'z'+zone.id">
                    <div
                        :data-zone-id="zone.id"
                        class="zone-item absolute group select-none touch-none cursor-grab"
                        :class="{'zampa-selected': isActive(zone.id) && (!zone.vertices || zone.vertices.length < 3)}"
                        tabindex="0"
                        @focus="selectedId = zone.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        @keydown.enter.space.prevent.stop="selectedId = zone.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        :style="`
                            left:${zone.position_x}px;
                            top:${zone.position_y}px;
                            width:${zone.width}px;
                            height:${zone.height}px;
                            background-color:${zone.vertices && zone.vertices.length >= 3 ? 'transparent' : zone.color+'22'};
                            border:${zone.vertices && zone.vertices.length >= 3 ? 'none' : '2px solid '+zone.color};
                            z-index:${hoveredId === zone.id || selectedId === zone.id || editingZoneId === zone.id ? 8 : 2};
                            pointer-events:all;
                            transform:rotate(${zone.rotation ?? 0}deg);
                            transform-origin:center;
                        `"
                        :aria-label="`Zona ${zone.name}`"
                        @mouseenter="if (!(zone.vertices && zone.vertices.length >= 3)) hoveredId = zone.id"
                        @mouseleave="if (!(zone.vertices && zone.vertices.length >= 3)) hoveredId = null"
                        @click.stop="if (!(zone.vertices && zone.vertices.length >= 3)) { selectedId = zone.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null; }"
                        @mousedown.prevent.self="if (!(zone.vertices && zone.vertices.length >= 3)) startZoneDrag($event, zone)"
                        @contextmenu.prevent.stop="openContextMenu($event, zone, 'zone')"
                        @keydown.stop="if ($event.key === 'ContextMenu' || ($event.shiftKey && $event.key === 'F10')) openContextMenu($event, zone, 'zone')"
                    >
                        {{-- Polígono SVG: en modo polígono recibe eventos y maneja drag/select --}}
                        <svg x-show="zone.vertices && zone.vertices.length >= 3"
                             :class="zone.vertices && zone.vertices.length >= 3 ? 'absolute inset-0 overflow-visible' : 'absolute inset-0 pointer-events-none overflow-visible'"
                             :style="`cursor:${zone.vertices && zone.vertices.length >= 3 ? 'grab' : 'default'};`"
                             :width="zone.width"
                             :height="zone.height"
                             aria-hidden="true"
                             @mouseenter.stop="if (zone.vertices && zone.vertices.length >= 3) hoveredId = zone.id"
                             @mouseleave.stop="if (zone.vertices && zone.vertices.length >= 3) hoveredId = null"
                             @click.stop="if (zone.vertices && zone.vertices.length >= 3) { selectedId = zone.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null; }"
                             @mousedown.prevent.stop="if (zone.vertices && zone.vertices.length >= 3) startZoneDrag($event, zone)"
                             @contextmenu.prevent.stop="if (zone.vertices && zone.vertices.length >= 3) openContextMenu($event, zone, 'zone')">
                            <polygon :points="vertexPoints(zone)"
                                     :fill="`${zone.color}22`"
                                     :stroke="isActive(zone.id) ? '#6366f1' : zone.color"
                                     :stroke-width="isActive(zone.id) ? '3' : '2'"
                                     :stroke-dasharray="isActive(zone.id) ? '8 4' : 'none'"
                                     fill-rule="evenodd"
                                     style="pointer-events:painted;"/>
                        </svg>

                        {{-- Handles de vértices (edición en modo edit + zona seleccionada) --}}
                        <template x-if="!readonly && editMode && selectedId === zone.id && zone.vertices && zone.vertices.length >= 3">
                            <div class="absolute inset-0 pointer-events-none">
                                <template x-for="(v, idx) in zone.vertices" :key="idx">
                                    <div class="absolute" :style="`left:${v.x - 6}px; top:${v.y - 6}px;`">
                                        <div class="w-3 h-3 rounded-full border-2 bg-white pointer-events-auto cursor-move z-10 shadow focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                             :style="`border-color:${zone.color};`"
                                             tabindex="0"
                                             @focus="focusedVertexIdx = idx"
                                             @blur="if (focusedVertexIdx === idx) focusedVertexIdx = null"
                                             @mousedown.stop.prevent="startVertexDrag($event, zone, idx)"
                                             :aria-label="`Vértice ${idx + 1} de la zona`">
                                        </div>
                                        <div x-show="zone.vertices.length > 3"
                                             class="absolute -top-2 -right-2 z-[11] w-3.5 h-3.5 rounded-full bg-red-500 text-white flex items-center justify-center leading-none pointer-events-auto cursor-pointer shadow text-[9px] font-bold select-none focus:outline-none focus:ring-2 focus:ring-red-400"
                                             role="button"
                                             tabindex="0"
                                             @click.stop.prevent="removeZoneVertex(zone, idx)"
                                             @keydown.enter.space.stop.prevent="removeZoneVertex(zone, idx)"
                                             :aria-label="`Eliminar vértice ${idx + 1} de la zona`">×</div>
                                    </div>
                                </template>
                                {{-- Botones "+" en el punto medio de cada arista para añadir vértices --}}
                                <template x-for="(v, idx) in zone.vertices" :key="`e${idx}`">
                                    <div class="absolute w-4 h-4 rounded-full bg-white border pointer-events-auto cursor-pointer z-9 shadow flex items-center justify-center text-xs font-bold leading-none select-none focus:outline-none focus:ring-2"
                                         :style="`left:${((v.x + zone.vertices[(idx+1)%zone.vertices.length].x)/2)-8}px; top:${((v.y + zone.vertices[(idx+1)%zone.vertices.length].y)/2)-8}px; border-color:${zone.color}; color:${zone.color};`"
                                         role="button"
                                         tabindex="0"
                                         @click.stop.prevent="addZoneVertex(zone, idx)"
                                         @keydown.enter.space.stop.prevent="addZoneVertex(zone, idx)"
                                         :aria-label="`Añadir vértice en arista ${idx + 1}`">+</div>
                                </template>
                            </div>
                        </template>

                        {{-- Etiqueta de zona --}}
                        <span class="absolute bottom-1 left-1 text-xs font-semibold px-1.5 py-0.5 rounded pointer-events-none"
                              :style="`color:${zone.color}; background:rgba(255,255,255,0.85);`"
                              x-text="zone.name">
                        </span>

                        {{-- Botón "Convertir a polígono" (solo en modo edit, sin vértices aún) --}}
                        <button type="button"
                                x-show="!readonly && editMode && selectedId === zone.id && (!zone.vertices || zone.vertices.length < 3)"
                                @click.stop="initPolygonVertices(zone)"
                                class="absolute top-1 left-1/2 -translate-x-1/2
                                       px-2 py-0.5 text-xs font-semibold rounded
                                       bg-white/90 shadow border pointer-events-auto
                                       hover:bg-white transition-colors"
                                :style="`color:${zone.color}; border-color:${zone.color};`"
                                :aria-label="`Convertir zona ${zone.name} a polígono personalizado`">
                            ⬡ Polígono
                        </button>

                        {{-- Botón editar zona --}}
                        <button type="button"
                                @click.stop="closeContextMenu(); if (editingZoneId === zone.id) { editingZoneId = null; editingZone = null; _zoneBtnEl = null; } else { editingZoneId = zone.id; editingZone = zone; _zoneBtnEl = $el; editZonePanelPos = panelPosFromBtn($el, 220); editingTableId = null; editingTable = null; _editBtnEl = null; }"
                                class="absolute -top-2.5 -right-9
                                       w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                       flex items-center justify-center transition-opacity
                                       hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                       shadow-md"
                                :class="selectedId === zone.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                :tabindex="selectedId === zone.id || hoveredId === zone.id ? 0 : -1"
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
                                       flex items-center justify-center transition-opacity
                                       hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                       shadow-md"
                                :class="selectedId === zone.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                :tabindex="selectedId === zone.id || hoveredId === zone.id ? 0 : -1"
                                :aria-label="`Eliminar zona ${zone.name}`">
                            <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>


                        {{-- Handle de rotación de zona (color sincronizado con zone.color) --}}
                        <div class="absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    transition-opacity z-10"
                             :class="selectedId === zone.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startZoneRotation($event, zone)"
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

                        {{-- Handle de redimensionado de zona — solo edit mode, solo rect --}}
                        <div x-show="!readonly && editMode && (!zone.vertices || zone.vertices.length < 3)"
                             class="zone-resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize transition-opacity"
                             :class="selectedId === zone.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
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
                <template x-for="bar in visibleElements().filter(e => e.shape === 'bar')" :key="'b'+bar.id">
                    <div
                        :data-table-id="bar.id"
                        class="table-item absolute group select-none touch-none"
                        tabindex="0"
                        @focus="selectedId = bar.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        @keydown.enter.space.prevent.stop="selectedId = bar.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        :style="`left:${bar.position_x}px; top:${bar.position_y}px;
                                 width:${bar.width}px; height:${bar.height}px;
                                 transform:rotate(${bar.rotation ?? 0}deg);
                                 transform-origin:center;
                                 z-index:${hoveredId === bar.id || selectedId === bar.id || rotatingId === bar.id ? 30 : 10};`"
                        @mouseenter="hoveredId = bar.id"
                        @mouseleave="hoveredId = null"
                        @click.stop="selectedId = bar.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null;"
                        @contextmenu.prevent.stop="openContextMenu($event, bar, 'bar')"
                        @keydown.stop="if ($event.key === 'ContextMenu' || ($event.shiftKey && $event.key === 'F10')) openContextMenu($event, bar, 'bar')"
                        :aria-label="`Barra: ${bar.name}`"
                    >
                        <div class="w-full h-full relative flex items-center justify-center cursor-grab active:cursor-grabbing transition-shadow"
                             :class="{
                                 'rounded-lg bg-amber-100 dark:bg-amber-900 border-2 border-amber-400 dark:border-amber-600 shadow-md hover:shadow-lg': !(bar.vertices && bar.vertices.length >= 3),
                                 'zampa-selected': isActive(bar.id)
                             }">

                            {{-- Polígono SVG de la barra — en modo polígono reemplaza al rect --}}
                            <svg x-show="bar.vertices && bar.vertices.length >= 3"
                                 class="absolute inset-0 overflow-visible pointer-events-none"
                                 :width="bar.width"
                                 :height="bar.height"
                                 aria-hidden="true">
                                <polygon :points="vertexPoints(bar)"
                                         fill="rgba(251,191,36,0.45)"
                                         :stroke="isActive(bar.id) ? '#6366f1' : '#d97706'"
                                         :stroke-width="isActive(bar.id) ? '3' : '2'"
                                         :stroke-dasharray="isActive(bar.id) ? '8 4' : 'none'"
                                         fill-rule="evenodd"/>
                            </svg>

                            {{-- Handles de vértices de la barra --}}
                            <template x-if="!readonly && editMode && selectedId === bar.id && bar.vertices && bar.vertices.length >= 3">
                                <div class="absolute inset-0 pointer-events-none">
                                    <template x-for="(v, idx) in bar.vertices" :key="idx">
                                        <div class="absolute" :style="`left:${v.x - 6}px; top:${v.y - 6}px;`">
                                            <div class="bar-vertex-handle w-3 h-3 rounded-full border-2 border-amber-500 bg-white pointer-events-auto cursor-move z-10 shadow focus:outline-none focus:ring-2 focus:ring-amber-400"
                                                 tabindex="0"
                                                 @focus="focusedVertexIdx = idx"
                                                 @blur="if (focusedVertexIdx === idx) focusedVertexIdx = null"
                                                 @pointerdown.stop
                                                 @mousedown.stop.prevent="startBarVertexDrag($event, bar, idx)"
                                                 :aria-label="`Vértice ${idx + 1} de la barra`">
                                            </div>
                                            <div x-show="bar.vertices.length > 3"
                                                 class="bar-vertex-handle absolute -top-2 -right-2 z-[11] w-3.5 h-3.5 rounded-full bg-red-500 text-white flex items-center justify-center leading-none pointer-events-auto cursor-pointer shadow text-[9px] font-bold select-none focus:outline-none focus:ring-2 focus:ring-red-400"
                                                 role="button"
                                                 tabindex="0"
                                                 @pointerdown.stop
                                                 @click.stop.prevent="removeBarVertex(bar, idx)"
                                                 @keydown.enter.space.stop.prevent="removeBarVertex(bar, idx)"
                                                 :aria-label="`Eliminar vértice ${idx + 1} de la barra`">×</div>
                                        </div>
                                    </template>
                                    {{-- Botones "+" en el punto medio de cada arista para añadir vértices --}}
                                    <template x-for="(v, idx) in bar.vertices" :key="`e${idx}`">
                                        <div class="bar-vertex-handle absolute w-4 h-4 rounded-full bg-white border border-amber-500 pointer-events-auto cursor-pointer z-9 shadow flex items-center justify-center text-xs font-bold leading-none select-none text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400"
                                             :style="`left:${((v.x + bar.vertices[(idx+1)%bar.vertices.length].x)/2)-8}px; top:${((v.y + bar.vertices[(idx+1)%bar.vertices.length].y)/2)-8}px;`"
                                             role="button"
                                             tabindex="0"
                                             @pointerdown.stop
                                             @click.stop.prevent="addBarVertex(bar, idx)"
                                             @keydown.enter.space.stop.prevent="addBarVertex(bar, idx)"
                                             :aria-label="`Añadir vértice en arista ${idx + 1}`">+</div>
                                    </template>
                                </div>
                            </template>

                            {{-- Botón "Convertir a polígono" para la barra --}}
                            <button type="button"
                                    x-show="!readonly && editMode && selectedId === bar.id && (!bar.vertices || bar.vertices.length < 3)"
                                    @click.stop="initBarPolygonVertices(bar)"
                                    class="absolute top-1 left-1/2 -translate-x-1/2
                                           px-2 py-0.5 text-xs font-semibold rounded
                                           bg-white/90 shadow border border-amber-400 text-amber-700
                                           pointer-events-auto hover:bg-amber-50 transition-colors"
                                    aria-label="Convertir barra a polígono personalizado">
                                ⬡ Polígono
                            </button>

                            <span class="text-xs font-semibold text-amber-800 dark:text-amber-300
                                         text-center px-1 leading-tight pointer-events-none"
                                  x-text="bar.name">
                            </span>

                            <span class="absolute top-1 left-1 text-xs text-amber-600 dark:text-amber-400 pointer-events-none leading-none" aria-hidden="true">🍺</span>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    x-show="!(isRotating && rotatingId === bar.id)"
                                    @click.stop="deleteElement(bar)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :class="selectedId === bar.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === bar.id || hoveredId === bar.id ? 0 : -1"
                                    :aria-label="`Eliminar ${bar.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Handle de rotación --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    transition-opacity z-10"
                             :class="selectedId === bar.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, bar)"
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
                <template x-for="stool in visibleElements().filter(e => e.shape === 'stool')" :key="'s'+stool.id">
                    <div
                        :data-table-id="stool.id"
                        class="element-item absolute group select-none touch-none"
                        tabindex="0"
                        @focus="selectedId = stool.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        @keydown.enter.space.prevent.stop="selectedId = stool.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        :style="`left:${stool.position_x}px; top:${stool.position_y}px;
                                 width:${stool.width}px; height:${stool.height}px;
                                 transform:rotate(${stool.rotation ?? 0}deg);
                                 transform-origin:center;
                                 z-index:${hoveredId === stool.id || selectedId === stool.id || rotatingId === stool.id ? 30 : 10};`"
                        @mouseenter="hoveredId = stool.id"
                        @mouseleave="hoveredId = null"
                        @click.stop="selectedId = stool.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null;"
                        @contextmenu.prevent.stop="openContextMenu($event, stool, 'stool')"
                        @keydown.stop="if ($event.key === 'ContextMenu' || ($event.shiftKey && $event.key === 'F10')) openContextMenu($event, stool, 'stool')"
                        :aria-label="`Taburete: ${stool.name}`"
                    >
                        <div class="w-full h-full relative flex items-center justify-center
                                    rounded-full
                                    bg-green-50 dark:bg-green-900/30
                                    border-2 border-green-400 dark:border-green-500
                                    shadow-md cursor-grab active:cursor-grabbing
                                    transition-shadow hover:shadow-lg"
                             :class="{'zampa-selected': isActive(stool.id)}"
                             @mousedown.prevent="startElementDrag($event, stool)">

                            <span class="text-xs font-semibold text-green-700 dark:text-green-300
                                         text-center px-1 leading-tight pointer-events-none"
                                  x-text="stool.name">
                            </span>

                            <span class="absolute top-1 left-1 text-xs text-green-500 dark:text-green-400 pointer-events-none leading-none" aria-hidden="true">●</span>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    x-show="!(isRotating && rotatingId === stool.id)"
                                    @mousedown.stop
                                    @click.stop="deleteElement(stool)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :class="selectedId === stool.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === stool.id || hoveredId === stool.id ? 0 : -1"
                                    :aria-label="`Eliminar ${stool.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Handle de rotación --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    transition-opacity z-10"
                             :class="selectedId === stool.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, stool)"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar ${stool.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-green-400 shadow-md
                                        flex items-center justify-center text-green-500
                                        transition-all duration-150
                                        hover:bg-green-500 hover:border-green-600 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-green-400"></div>
                        </div>

                        {{-- Handle de redimensionado --}}
                        <div class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startResize($event, stool)"
                             role="button"
                             :aria-label="`Redimensionar ${stool.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-green-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Sillas: drag Alpine-nativo --}}
                <template x-for="chair in visibleElements().filter(e => e.shape === 'chair')" :key="'c'+chair.id">
                    <div
                        :data-table-id="chair.id"
                        class="element-item absolute group select-none touch-none"
                        tabindex="0"
                        @focus="selectedId = chair.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        @keydown.enter.space.prevent.stop="selectedId = chair.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                        :style="`left:${chair.position_x}px; top:${chair.position_y}px;
                                 width:${chair.width}px; height:${chair.height}px;
                                 transform:rotate(${chair.rotation ?? 0}deg);
                                 transform-origin:center;
                                 z-index:${hoveredId === chair.id || selectedId === chair.id || rotatingId === chair.id ? 30 : 10};`"
                        @mouseenter="hoveredId = chair.id"
                        @mouseleave="hoveredId = null"
                        @click.stop="selectedId = chair.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null;"
                        @contextmenu.prevent.stop="openContextMenu($event, chair, 'stool')"
                        @keydown.stop="if ($event.key === 'ContextMenu' || ($event.shiftKey && $event.key === 'F10')) openContextMenu($event, chair, 'stool')"
                        :aria-label="`Silla: ${chair.name}`"
                    >
                        <div class="w-full h-full relative flex flex-col items-center justify-center
                                    rounded
                                    bg-green-50 dark:bg-green-900/30
                                    border-2 border-green-400 dark:border-green-500
                                    shadow-md cursor-grab active:cursor-grabbing
                                    transition-shadow hover:shadow-lg"
                             :class="{'zampa-selected': isActive(chair.id)}"
                             @mousedown.prevent="startElementDrag($event, chair)">

                            {{-- Respaldo visual --}}
                            <div class="w-4/5 h-1/3 rounded-t bg-green-200 dark:bg-green-700 border border-green-400 dark:border-green-500 pointer-events-none"></div>

                            <span class="text-[9px] font-semibold text-green-700 dark:text-green-300
                                         text-center leading-tight pointer-events-none mt-0.5"
                                  x-text="chair.name">
                            </span>

                            {{-- Botón eliminar --}}
                            <button type="button"
                                    x-show="!(isRotating && rotatingId === chair.id)"
                                    @mousedown.stop
                                    @click.stop="deleteElement(chair)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :class="selectedId === chair.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === chair.id || hoveredId === chair.id ? 0 : -1"
                                    :aria-label="`Eliminar ${chair.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Handle de rotación --}}
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    transition-opacity z-10"
                             :class="selectedId === chair.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, chair)"
                             role="button"
                             tabindex="0"
                             style="cursor: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grab;"
                             :aria-label="`Rotar ${chair.name} (arrastra para girar)`">
                            <div class="w-6 h-6 rounded-full
                                        bg-white dark:bg-gray-800
                                        border-2 border-green-400 shadow-md
                                        flex items-center justify-center text-green-500
                                        transition-all duration-150
                                        hover:bg-green-500 hover:border-green-600 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="w-px h-3 bg-green-400"></div>
                        </div>

                        {{-- Handle de redimensionado --}}
                        <div class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100
                                    transition-opacity"
                             @mousedown.stop.prevent="startResize($event, chair)"
                             role="button"
                             :aria-label="`Redimensionar ${chair.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-green-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Chimeneas --}}
                <template x-for="fp in visibleElements().filter(e => e.shape === 'fireplace')" :key="'fp'+fp.id">
                    <div :data-table-id="fp.id"
                         class="element-item absolute group select-none touch-none"
                         tabindex="0"
                         @focus="selectedId = fp.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @keydown.enter.space.prevent.stop="selectedId = fp.id;"
                         :style="`left:${fp.position_x}px; top:${fp.position_y}px; width:${fp.width}px; height:${fp.height}px; transform:rotate(${fp.rotation ?? 0}deg); transform-origin:center; z-index:${hoveredId === fp.id || selectedId === fp.id ? 30 : 10};`"
                         @mouseenter="hoveredId = fp.id" @mouseleave="hoveredId = null"
                         @click.stop="selectedId = fp.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @contextmenu.prevent.stop="openContextMenu($event, fp, 'stool')"
                         :aria-label="`Chimenea: ${fp.name}`">
                        <div class="w-full h-full relative flex items-center justify-center rounded bg-red-100 dark:bg-red-900/40 border-2 border-red-700 dark:border-red-600 shadow-md cursor-grab active:cursor-grabbing transition-shadow hover:shadow-lg"
                             :class="{'zampa-selected': isActive(fp.id)}"
                             @mousedown.prevent="startElementDrag($event, fp)">
                            <span class="text-lg pointer-events-none" aria-hidden="true">🔥</span>
                            <span class="absolute bottom-0.5 left-0 right-0 text-[9px] font-semibold text-red-800 dark:text-red-300 text-center pointer-events-none" x-text="fp.name"></span>
                            <button type="button" @mousedown.stop @click.stop="deleteElement(fp)"
                                    class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center transition-opacity hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 shadow-md"
                                    :class="selectedId === fp.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === fp.id || hoveredId === fp.id ? 0 : -1"
                                    :aria-label="`Eliminar ${fp.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2 flex flex-col items-center gap-0 transition-opacity z-10"
                             :class="selectedId === fp.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, fp)" role="button" tabindex="0"
                             style="cursor:grab;" :aria-label="`Rotar ${fp.name}`">
                            <div class="w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-2 border-red-500 shadow-md flex items-center justify-center text-red-500 transition-all hover:bg-red-500 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            </div>
                            <div class="w-px h-3 bg-red-500"></div>
                        </div>
                        <div class="resize-handle absolute bottom-0 right-0 w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100 transition-opacity"
                             @mousedown.stop.prevent="startResize($event, fp)" role="button" :aria-label="`Redimensionar ${fp.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-red-500"><path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </div>
                    </div>
                </template>

                {{-- Pilares --}}
                <template x-for="pl in visibleElements().filter(e => e.shape === 'pillar')" :key="'pl'+pl.id">
                    <div :data-table-id="pl.id"
                         class="element-item absolute group select-none touch-none"
                         tabindex="0"
                         @focus="selectedId = pl.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @keydown.enter.space.prevent.stop="selectedId = pl.id;"
                         :style="`left:${pl.position_x}px; top:${pl.position_y}px; width:${pl.width}px; height:${pl.height}px; transform:rotate(${pl.rotation ?? 0}deg); transform-origin:center; z-index:${hoveredId === pl.id || selectedId === pl.id ? 30 : 10};`"
                         @mouseenter="hoveredId = pl.id" @mouseleave="hoveredId = null"
                         @click.stop="selectedId = pl.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @contextmenu.prevent.stop="openContextMenu($event, pl, 'stool')"
                         :aria-label="`Pilar: ${pl.name}`">
                        <div class="w-full h-full relative flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600 border-2 border-gray-500 dark:border-gray-400 shadow-md cursor-grab active:cursor-grabbing transition-shadow hover:shadow-lg"
                             :class="{'zampa-selected': isActive(pl.id)}"
                             @mousedown.prevent="startElementDrag($event, pl)">
                            <span class="text-[9px] font-bold text-gray-600 dark:text-gray-300 pointer-events-none" x-text="pl.name"></span>
                            <button type="button" @mousedown.stop @click.stop="deleteElement(pl)"
                                    class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center transition-opacity hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 shadow-md"
                                    :class="selectedId === pl.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === pl.id || hoveredId === pl.id ? 0 : -1"
                                    :aria-label="`Eliminar ${pl.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2 flex flex-col items-center gap-0 transition-opacity z-10"
                             :class="selectedId === pl.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, pl)" role="button" tabindex="0" style="cursor:grab"
                             :aria-label="`Rotar ${pl.name}`">
                            <div class="w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-500 shadow-md flex items-center justify-center text-gray-500 transition-all hover:bg-gray-500 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            </div>
                            <div class="w-px h-3 bg-gray-500"></div>
                        </div>
                        <div class="resize-handle absolute bottom-0 right-0 w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100 transition-opacity"
                             @mousedown.stop.prevent="startResize($event, pl)" role="button" :aria-label="`Redimensionar ${pl.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-gray-400"><path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </div>
                    </div>
                </template>

                {{-- Columnas --}}
                <template x-for="col in visibleElements().filter(e => e.shape === 'column')" :key="'col'+col.id">
                    <div :data-table-id="col.id"
                         class="element-item absolute group select-none touch-none"
                         tabindex="0"
                         @focus="selectedId = col.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @keydown.enter.space.prevent.stop="selectedId = col.id;"
                         :style="`left:${col.position_x}px; top:${col.position_y}px; width:${col.width}px; height:${col.height}px; transform:rotate(${col.rotation ?? 0}deg); transform-origin:center; z-index:${hoveredId === col.id || selectedId === col.id ? 30 : 10};`"
                         @mouseenter="hoveredId = col.id" @mouseleave="hoveredId = null"
                         @click.stop="selectedId = col.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null;"
                         @contextmenu.prevent.stop="openContextMenu($event, col, 'stool')"
                         :aria-label="`Columna: ${col.name}`">
                        <div class="w-full h-full relative flex items-center justify-center rounded-sm bg-gray-300 dark:bg-gray-500 border-2 border-gray-600 dark:border-gray-300 shadow-md cursor-grab active:cursor-grabbing transition-shadow hover:shadow-lg"
                             :class="{'zampa-selected': isActive(col.id)}"
                             @mousedown.prevent="startElementDrag($event, col)">
                            {{-- Capitel y basa decorativos --}}
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gray-500 dark:bg-gray-300 rounded-t-sm pointer-events-none"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gray-500 dark:bg-gray-300 rounded-b-sm pointer-events-none"></div>
                            <span class="text-[9px] font-bold text-gray-700 dark:text-gray-200 pointer-events-none" x-text="col.name"></span>
                            <button type="button" @mousedown.stop @click.stop="deleteElement(col)"
                                    class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center transition-opacity hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 shadow-md"
                                    :class="selectedId === col.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === col.id || hoveredId === col.id ? 0 : -1"
                                    :aria-label="`Eliminar ${col.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2 flex flex-col items-center gap-0 transition-opacity z-10"
                             :class="selectedId === col.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, col)" role="button" tabindex="0" style="cursor:grab"
                             :aria-label="`Rotar ${col.name}`">
                            <div class="w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-500 shadow-md flex items-center justify-center text-gray-500 transition-all hover:bg-gray-500 hover:text-white hover:scale-110">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            </div>
                            <div class="w-px h-3 bg-gray-500"></div>
                        </div>
                        <div class="resize-handle absolute bottom-0 right-0 w-4 h-4 cursor-se-resize opacity-0 group-hover:opacity-100 transition-opacity"
                             @mousedown.stop.prevent="startResize($event, col)" role="button" :aria-label="`Redimensionar ${col.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-gray-400"><path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </div>
                    </div>
                </template>

                {{-- Mesas existentes --}}
                <template x-for="table in visibleTables()" :key="table.id">
                    <div
                        :data-table-id="table.id"
                        class="table-item absolute group select-none touch-none"
                        tabindex="0"
                        @focus="selectedId = table.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null; if (!editMode && table.is_service_point !== false) Alpine.store('viewPanel').open(table);"
                        @keydown.enter.space.prevent.stop="selectedId = table.id; editingTableId=null; editingTable=null; editingZoneId=null; editingZone=null; if (!editMode && table.is_service_point !== false) Alpine.store('viewPanel').open(table);"
                        :style="`left:${table.position_x}px; top:${table.position_y}px;
                                 width:${table.width}px; height:${table.height}px;
                                 transform: rotate(${table.rotation ?? 0}deg);
                                 transform-origin: center;
                                 z-index: ${hoveredId === table.id || selectedId === table.id || rotatingId === table.id || editingTableId === table.id
                                     ? (floorsEnabled && currentView === 'general' ? 100 : 30)
                                     : (floorsEnabled && currentView === 'general' ? 10 + ((table.floor ?? 1) - 1) * 10 : 10)};`"
                        @mouseenter="hoveredId = table.id"
                        @mouseleave="hoveredId = null"
                        @click.stop="selectedId = table.id; editingTableId = null; editingTable = null; editingZoneId = null; editingZone = null; if (!editMode && table.is_service_point !== false) Alpine.store('viewPanel').open(table);"
                        @contextmenu.prevent.stop="openContextMenu($event, table, 'table')"
                        @keydown.stop="if ($event.key === 'ContextMenu' || ($event.shiftKey && $event.key === 'F10')) openContextMenu($event, table, 'table')"
                        :aria-label="`Mesa ${table.name}`"
                    >
                        {{-- Fondo de la mesa (color según estado del pedido) --}}
                        <div class="w-full h-full relative flex items-center justify-center
                                    border-2 shadow-md transition-all duration-300 hover:shadow-lg"
                             :class="{
                                'cursor-grab active:cursor-grabbing': editMode,
                                'cursor-pointer': !editMode,
                                'rounded-full':    table.shape === 'round',
                                'rounded-xl':      table.shape === 'square',
                                'rounded-lg':      table.shape === 'rectangle',
                                'zampa-selected':  isActive(table.id),
                                'bg-green-50 dark:bg-green-900/30 border-green-400 dark:border-green-500':
                                    !table.orderStatus || table.orderStatus === 'free',
                                'bg-amber-100 dark:bg-amber-900/60 border-amber-400 dark:border-amber-500':
                                    table.orderStatus === 'occupied',
                                'bg-emerald-200 dark:bg-emerald-800/60 border-emerald-500 dark:border-emerald-400 animate-pulse':
                                    table.orderStatus === 'ready',
                                'bg-blue-100 dark:bg-blue-900/60 border-blue-400 dark:border-blue-500':
                                    table.orderStatus === 'payment_pending',
                             }"
                             :style="zoneFor(table) && (!table.orderStatus || table.orderStatus === 'free') ? `border-color: ${zoneFor(table).color}` : null">

                            {{-- Nombre --}}
                            <span class="text-xs font-semibold text-center px-1 leading-tight pointer-events-none"
                                  :class="{
                                      'text-green-700 dark:text-green-300':   !table.orderStatus || table.orderStatus === 'free',
                                      'text-amber-800 dark:text-amber-200':   table.orderStatus === 'occupied',
                                      'text-emerald-800 dark:text-emerald-200': table.orderStatus === 'ready',
                                      'text-blue-800  dark:text-blue-200':    table.orderStatus === 'payment_pending',
                                  }"
                                  x-text="table.name">
                            </span>

                            {{-- Badge de estado con icono --}}
                            <span class="absolute top-1 left-1 w-2 h-2 rounded-full"
                                  role="img"
                                  :class="{
                                      'bg-green-400':  !table.orderStatus || table.orderStatus === 'free',
                                      'bg-amber-500':  table.orderStatus === 'occupied',
                                      'bg-green-600':  table.orderStatus === 'ready',
                                      'bg-blue-500':   table.orderStatus === 'payment_pending',
                                  }"
                                  :aria-label="{
                                      'free':            'Estado: Libre',
                                      'occupied':        'Estado: Ocupada',
                                      'ready':           'Estado: Lista para servir',
                                      'payment_pending': 'Estado: Pendiente de pago',
                                  }[table.orderStatus] ?? 'Estado: Libre'">
                            </span>

                            {{-- Badge: listo para servir --}}
                            <span x-show="table.orderStatus === 'ready'"
                                  class="absolute bottom-1 right-1
                                         flex items-center justify-center
                                         w-5 h-5 rounded-full
                                         bg-green-600 text-white text-xs font-bold
                                         shadow-md"
                                  title="Listo para servir"
                                  aria-label="Mesa lista para servir">
                                ✓
                            </span>

                            {{-- Badge: solicitud de cuenta --}}
                            <span x-show="table.orderStatus === 'payment_pending'"
                                  class="absolute bottom-1 right-1
                                         flex items-center justify-center
                                         w-5 h-5 rounded-full
                                         bg-blue-500 text-white text-xs font-bold
                                         shadow-md animate-pulse"
                                  title="Solicita la cuenta"
                                  aria-label="Mesa solicita la cuenta">
                                €
                            </span>

                            {{-- Botón editar forma — solo admin --}}
                            <button type="button"
                                    x-show="!readonly && editMode && !(isRotating && rotatingId === table.id)"
                                    @click.stop="closeContextMenu(); if (editingTableId === table.id) { editingTableId = null; editingTable = null; _editBtnEl = null; } else { editingTableId = table.id; editingTable = table; _editBtnEl = $el; editPanelPos = panelPosFromBtn($el, 220); editingZoneId = null; editingZone = null; _zoneBtnEl = null; }"
                                    class="absolute -top-2.5 -right-16
                                           w-6 h-6 rounded-full bg-gray-600 dark:bg-gray-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                                           shadow-md"
                                    :class="selectedId === table.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === table.id || hoveredId === table.id ? 0 : -1"
                                    :aria-label="`Editar forma de mesa ${table.name}`"
                                    :aria-expanded="editingTableId === table.id">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>

                            {{-- Botón QR --}}
                            <button type="button"
                                    x-show="!(isRotating && rotatingId === table.id)"
                                    @click.stop="$store.qrModal.open(table)"
                                    class="absolute -top-2.5 -right-9
                                           w-6 h-6 rounded-full bg-indigo-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           shadow-md"
                                    :class="selectedId === table.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === table.id || hoveredId === table.id ? 0 : -1"
                                    :aria-label="`Ver QR de la mesa ${table.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm9-2h7v7h-7V3zm2 2v3h3V5h-3zM3 14h7v7H3v-7zm2 2v3h3v-3H5zm11.5-2a.5.5 0 01.5.5v1h1.5a.5.5 0 010 1H17v1.5a.5.5 0 01-1 0V17h-1.5a.5.5 0 010-1H16v-1.5a.5.5 0 01.5-.5zm3 3a.5.5 0 01.5.5V21h-2.5a.5.5 0 010-1H21v-1.5a.5.5 0 01.5-.5z"/>
                                </svg>
                            </button>

                            {{-- Botón eliminar — solo admin --}}
                            <button type="button"
                                    x-show="!readonly && editMode && !(isRotating && rotatingId === table.id)"
                                    @click.stop="deleteTable(table)"
                                    class="absolute -top-2.5 -right-2.5
                                           w-6 h-6 rounded-full bg-red-500 text-white
                                           flex items-center justify-center transition-opacity
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400
                                           shadow-md"
                                    :class="selectedId === table.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    :tabindex="selectedId === table.id || hoveredId === table.id ? 0 : -1"
                                    :aria-label="`Eliminar mesa ${table.name}`">
                                <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                        </div>

                        {{-- Handle de rotación — solo admin --}}
                        <div x-show="!readonly && editMode" class="rotation-handle absolute -top-9 left-1/2 -translate-x-1/2
                                    flex flex-col items-center gap-0
                                    transition-opacity z-10"
                             :class="selectedId === table.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startRotation($event, table)"
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
                        <div x-show="!readonly && editMode"
                             class="resize-handle absolute bottom-0 right-0
                                    w-4 h-4 cursor-se-resize transition-opacity"
                             :class="selectedId === table.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                             @mousedown.stop.prevent="startResize($event, table)"
                             role="button"
                             :aria-label="`Redimensionar mesa ${table.name}`">
                            <svg aria-hidden="true" viewBox="0 0 10 10" fill="none" class="w-full h-full text-indigo-400">
                                <path d="M9 1L1 9M9 5L5 9M9 9H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </template>

                {{-- Indicador de zona de soltar --}}
                <div x-show="isDraggingFromPalette || isDraggingZone"
                     class="absolute inset-0 rounded-xl border-4 border-dashed border-indigo-400
                            bg-indigo-50/30 dark:bg-indigo-900/20 pointer-events-none
                            flex items-center justify-center">
                    <p class="text-indigo-500 font-semibold text-lg"
                       x-text="isDraggingZone ? 'Suelta aquí para crear la zona' : currentDragShape === 'bar' ? 'Suelta aquí para colocar la barra' : currentDragShape === 'stool' ? 'Suelta aquí para colocar el taburete' : 'Suelta aquí para crear la mesa'"></p>
                </div>
                {{-- Panel de edición de zona — absolute dentro del canvas, coordenadas en espacio del canvas --}}
                <div x-show="editingZoneId !== null && editingZone !== null"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-effect="if (editingZoneId !== null) $nextTick(() => $el.querySelector('input, select, button')?.focus())"
                     @click.stop
                     @keydown.escape.window="_zoneBtnEl?.focus(); editingZoneId = null; editingZone = null; _zoneBtnEl = null"
                     class="absolute z-[200] bg-white dark:bg-gray-800 rounded-xl shadow-xl
                            border border-gray-200 dark:border-gray-700 p-3 min-w-[200px]"
                     :style="`left:${editZonePanelPos.x}px; top:${editZonePanelPos.y}px`"
                     role="dialog"
                     aria-modal="true"
                     :aria-label="editingZone ? `Editar zona ${editingZone.name}` : 'Editar zona'">

                    <template x-if="editingZone">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                            <div class="flex gap-1 mb-3">
                                <input type="text"
                                       :value="editingZone.name"
                                       @keydown.enter.stop="updateZoneName(editingZone, $event.target.value); $event.target.blur()"
                                       @blur.stop="updateZoneName(editingZone, $event.target.value)"
                                       @click.stop
                                       maxlength="50"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                              px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       :aria-label="`Nombre de la zona ${editingZone.name}`">
                            </div>

                            <template x-if="floorsEnabled && floorCount > 1">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Planta</p>
                                    <select @change.stop="moveZoneToFloor(editingZone, parseInt($event.target.value))"
                                            @click.stop
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                                   px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
                                            :aria-label="`Planta de la zona ${editingZone.name}`">
                                        <template x-for="n in floorCount" :key="n">
                                            <option :value="n"
                                                    :selected="(editingZone.floor ?? 1) === n"
                                                    x-text="`Planta ${n}`">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </template>

                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Color</p>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="color"
                                       :value="editingZone.color"
                                       @input.stop="editingZone.color = $event.target.value"
                                       @change.stop="updateZoneColor(editingZone, $event.target.value)"
                                       @click.stop
                                       class="w-8 h-8 rounded cursor-pointer border border-gray-200 p-0.5"
                                       :aria-label="`Color de la zona ${editingZone.name}`">
                                <span class="text-xs text-gray-500 font-mono" x-text="editingZone.color"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Panel de edición de mesa — absolute dentro del canvas, coordenadas en espacio del canvas --}}
                <div x-show="editingTableId !== null && editingTable !== null"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-effect="if (editingTableId !== null) $nextTick(() => $el.querySelector('input, select, button')?.focus())"
                     @click.stop
                     @keydown.escape.window="_editBtnEl?.focus(); editingTableId = null; editingTable = null; _editBtnEl = null"
                     class="absolute z-[200] bg-white dark:bg-gray-800 rounded-xl shadow-xl
                            border border-gray-200 dark:border-gray-700 p-3 min-w-[200px]"
                     :style="`left:${editPanelPos.x}px; top:${editPanelPos.y}px`"
                     role="dialog"
                     aria-modal="true"
                     :aria-label="editingTable ? `Editar mesa ${editingTable.name}` : 'Editar mesa'">

                    <template x-if="editingTable">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Nombre</p>
                            <div class="flex gap-1 mb-3">
                                <input type="text"
                                       :value="editingTable.name"
                                       @keydown.enter.stop="updateName(editingTable, $event.target.value); $event.target.blur()"
                                       @blur.stop="updateName(editingTable, $event.target.value)"
                                       @click.stop
                                       maxlength="50"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                              px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       :aria-label="`Nombre de la mesa ${editingTable.name}`">
                            </div>

                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 mt-3 uppercase tracking-wide">Zona</p>
                            <select @change.stop="updateZoneAssignment(editingTable, $event.target.value)"
                                    @click.stop
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
                                    :aria-label="`Zona de la mesa ${editingTable.name}`">
                                <option value="">Sin zona</option>
                                <template x-for="zone in zones.filter(z => !floorsEnabled || (z.floor ?? 1) === (editingTable.floor ?? 1))" :key="zone.id">
                                    <option :value="zone.id"
                                            :selected="editingTable.zone_id == zone.id"
                                            x-text="zone.name"></option>
                                </template>
                            </select>

                            <template x-if="floorsEnabled && floorCount > 1">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 mt-3 uppercase tracking-wide">Planta</p>
                                    <select @change.stop="moveToFloor(editingTable, parseInt($event.target.value))"
                                            @click.stop
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                                   px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
                                            :aria-label="`Planta de la mesa ${editingTable.name}`">
                                        <template x-for="n in floorCount" :key="n">
                                            <option :value="n"
                                                    :selected="(editingTable.floor ?? 1) === n"
                                                    x-text="`Planta ${n}`">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </template>

                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Forma</p>
                            <div class="flex gap-1.5" role="group" aria-label="Seleccionar forma">
                                <button type="button"
                                        @click.stop="updateShape(editingTable, 'square')"
                                        :class="editingTable.shape === 'square'
                                            ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        aria-label="Forma cuadrada">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
                                </button>
                                <button type="button"
                                        @click.stop="updateShape(editingTable, 'round')"
                                        :class="editingTable.shape === 'round'
                                            ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        aria-label="Forma redonda">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
                                </button>
                                <button type="button"
                                        @click.stop="updateShape(editingTable, 'rectangle')"
                                        :class="editingTable.shape === 'rectangle'
                                            ? 'bg-indigo-600 text-white ring-2 ring-indigo-400'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        aria-label="Forma rectangular">
                                    <svg class="w-5 h-3" fill="currentColor" viewBox="0 0 24 14" aria-hidden="true"><rect x="0" y="0" width="24" height="14" rx="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </main>

        {{-- Indicador de grados de rotación — fuera del canvas escalado para que fixed funcione correctamente --}}
        <div x-show="rotTooltip.show"
             x-transition:enter="transition ease-out duration-75"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed pointer-events-none z-[200] select-none"
             :style="`left:${rotTooltip.x + 14}px; top:${rotTooltip.y + 14}px`"
             aria-hidden="true">
            <span class="inline-flex items-center gap-0.5
                         bg-gray-900/90 text-white
                         text-xs font-mono font-semibold
                         px-1.5 py-0.5 rounded-md shadow-lg ring-1 ring-white/10">
                <span x-text="rotTooltip.deg > 180 ? rotTooltip.deg - 360 : rotTooltip.deg"></span>°
            </span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         Menú contextual de estructuras (clic derecho / ContextMenu)
         ═══════════════════════════════════════════════════════════ --}}
    <div id="context-menu"
         x-show="contextMenu.show"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-effect="if (contextMenu.show) $nextTick(() => { const first = $el.querySelector('[role=menuitem]'); first?.focus(); })"
         @click.stop
         @keydown.escape.prevent="closeContextMenu()"
         @keydown.arrow-down.prevent="focusNextContextItem($event.target)"
         @keydown.arrow-up.prevent="focusPrevContextItem($event.target)"
         @keydown.home.prevent="$el.querySelector('[role=menuitem]')?.focus()"
         @keydown.end.prevent="[...$el.querySelectorAll('[role=menuitem]')].at(-1)?.focus()"
         @keydown.tab="closeContextMenu()"
         class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-xl shadow-2xl
                border border-gray-200 dark:border-gray-700 py-1 min-w-[200px]
                select-none"
         :style="`left:${contextMenu.x}px; top:${contextMenu.y}px`"
         role="menu"
         aria-label="Opciones de la estructura"
         x-cloak>

        {{-- Cabecera: nombre de la estructura --}}
        <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <span class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate"
                  x-text="contextMenu.item?.name ?? 'Estructura'"></span>
            <span class="ml-auto text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                  :class="{
                      'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300': contextMenu.type === 'table',
                      'bg-amber-100  text-amber-600  dark:bg-amber-900/50  dark:text-amber-300':  contextMenu.type === 'bar' || contextMenu.type === 'stool',
                      'bg-green-100  text-green-600  dark:bg-green-900/50  dark:text-green-300':  contextMenu.type === 'zone',
                  }"
                  x-text="{ table: 'Mesa', bar: 'Barra', stool: 'Taburete', zone: 'Zona' }[contextMenu.type] ?? ''">
            </span>
        </div>

        {{-- ── OPCIONES PARA MESAS ── --}}
        <template x-if="contextMenu.type === 'table'">
            <div>
                {{-- Editar (solo edit mode) --}}
                <template x-if="!readonly && editMode">
                    <button type="button"
                            role="menuitem"
                            tabindex="0"
                            @click.stop="editingTableId = contextMenu.item.id; editingTable = contextMenu.item; editPanelPos = panelPosFromItem(contextMenu.item, 220); editingZoneId = null; editingZone = null; closeContextMenu()"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                   text-gray-700 dark:text-gray-200
                                   hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                   focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                   transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                        Editar mesa
                    </button>
                </template>

                {{-- Ver QR (siempre disponible) --}}
                <button type="button"
                        role="menuitem"
                        tabindex="0"
                        @click.stop="$store.qrModal.open(contextMenu.item); closeContextMenu()"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                               text-gray-700 dark:text-gray-200
                               hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                               focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                               transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0 text-indigo-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm9-2h7v7h-7V3zm2 2v3h3V5h-3zM3 14h7v7H3v-7zm2 2v3h3v-3H5zm11.5-2a.5.5 0 01.5.5v1h1.5a.5.5 0 010 1H17v1.5a.5.5 0 01-1 0V17h-1.5a.5.5 0 010-1H16v-1.5a.5.5 0 01.5-.5zm3 3a.5.5 0 01.5.5V21h-2.5a.5.5 0 010-1H21v-1.5a.5.5 0 01.5-.5z"/>
                    </svg>
                    Ver código QR
                </button>

                {{-- Cambiar forma + Eliminar (solo edit mode) --}}
                <template x-if="!readonly && editMode">
                    <div>
                        <div class="border-t border-gray-100 dark:border-gray-700 my-1" role="separator"></div>
                        <p class="px-3 py-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider"
                           role="presentation">Cambiar forma</p>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="updateShape(contextMenu.item, 'square'); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                       focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                       transition-colors"
                                :class="contextMenu.item?.shape === 'square'
                                    ? 'text-indigo-600 dark:text-indigo-400 font-semibold'
                                    : 'text-gray-700 dark:text-gray-200'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
                            Cuadrada
                            <svg x-show="contextMenu.item?.shape === 'square'" class="w-3.5 h-3.5 ml-auto text-indigo-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="updateShape(contextMenu.item, 'round'); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                       focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                       transition-colors"
                                :class="contextMenu.item?.shape === 'round'
                                    ? 'text-indigo-600 dark:text-indigo-400 font-semibold'
                                    : 'text-gray-700 dark:text-gray-200'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
                            Redonda
                            <svg x-show="contextMenu.item?.shape === 'round'" class="w-3.5 h-3.5 ml-auto text-indigo-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="updateShape(contextMenu.item, 'rectangle'); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                       focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                       transition-colors"
                                :class="contextMenu.item?.shape === 'rectangle'
                                    ? 'text-indigo-600 dark:text-indigo-400 font-semibold'
                                    : 'text-gray-700 dark:text-gray-200'">
                            <svg class="w-5 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 14" aria-hidden="true"><rect x="0" y="0" width="24" height="14" rx="3"/></svg>
                            Rectangular
                            <svg x-show="contextMenu.item?.shape === 'rectangle'" class="w-3.5 h-3.5 ml-auto text-indigo-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1" role="separator"></div>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="deleteTable(contextMenu.item); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-red-600 dark:text-red-400
                                       hover:bg-red-50 dark:hover:bg-red-900/20
                                       focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20
                                       transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Eliminar mesa
                        </button>
                    </div>
                </template>
            </div>
        </template>

        {{-- ── OPCIONES PARA ZONAS ── --}}
        <template x-if="contextMenu.type === 'zone'">
            <div>
                <template x-if="!readonly && editMode">
                    <div>
                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="editingZoneId = contextMenu.item.id; editingZone = contextMenu.item; editZonePanelPos = panelPosFromItem(contextMenu.item, 220); editingTableId = null; editingTable = null; closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-gray-700 dark:text-gray-200
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                       focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                       transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                            Editar zona
                        </button>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                x-show="!contextMenu.item?.vertices || contextMenu.item?.vertices.length < 3"
                                @click.stop="initPolygonVertices(contextMenu.item); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-gray-700 dark:text-gray-200
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                       focus:outline-none focus:bg-indigo-50 dark:focus:bg-indigo-900/30
                                       transition-colors">
                            <span class="w-4 h-4 flex-shrink-0 text-center leading-none text-base" aria-hidden="true">⬡</span>
                            Convertir a polígono
                        </button>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1" role="separator"></div>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="deleteZone(contextMenu.item); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-red-600 dark:text-red-400
                                       hover:bg-red-50 dark:hover:bg-red-900/20
                                       focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20
                                       transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Eliminar zona
                        </button>
                    </div>
                </template>
            </div>
        </template>

        {{-- ── OPCIONES PARA BARRAS ── --}}
        <template x-if="contextMenu.type === 'bar'">
            <div>
                <template x-if="!readonly && editMode">
                    <div>
                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                x-show="!contextMenu.item?.vertices || contextMenu.item?.vertices.length < 3"
                                @click.stop="initBarPolygonVertices(contextMenu.item); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-gray-700 dark:text-gray-200
                                       hover:bg-amber-50 dark:hover:bg-amber-900/30
                                       focus:outline-none focus:bg-amber-50 dark:focus:bg-amber-900/30
                                       transition-colors">
                            <span class="w-4 h-4 flex-shrink-0 text-center leading-none text-base" aria-hidden="true">⬡</span>
                            Convertir a polígono
                        </button>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1" role="separator"></div>

                        <button type="button"
                                role="menuitem"
                                tabindex="0"
                                @click.stop="deleteElement(contextMenu.item); closeContextMenu()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                       text-red-600 dark:text-red-400
                                       hover:bg-red-50 dark:hover:bg-red-900/20
                                       focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20
                                       transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Eliminar barra
                        </button>
                    </div>
                </template>
            </div>
        </template>

        {{-- ── OPCIONES PARA TABURETES ── --}}
        <template x-if="contextMenu.type === 'stool'">
            <div>
                <template x-if="!readonly && editMode">
                    <button type="button"
                            role="menuitem"
                            tabindex="0"
                            @click.stop="deleteElement(contextMenu.item); closeContextMenu()"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                   text-red-600 dark:text-red-400
                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                   focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20
                                   transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Eliminar taburete
                    </button>
                </template>
            </div>
        </template>
    </div>

    {{-- Toast fijo — siempre visible sin importar scroll ni zoom del canvas --}}
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0 translate-y-2"
         :class="toast.error ? 'bg-red-100 text-red-700 border-red-300' : 'bg-green-100 text-green-700 border-green-300'"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] border rounded-lg px-4 py-2 text-sm font-medium shadow-lg pointer-events-none"
         x-text="toast.msg"
         role="alert"
         aria-live="polite">
    </div>
</div>

{{-- Modal de QR de mesa --}}
<div x-data
     x-show="$store.qrModal.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     aria-modal="true"
     role="dialog"
     aria-labelledby="qr-modal-title"
     @click="$store.qrModal.close()"
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

        {{-- QR SVG inline desde caché prefetcheado --}}
        <div class="flex justify-center mb-4 p-3 bg-white rounded-xl border border-gray-200 dark:border-gray-700"
             :aria-label="`Código QR de la mesa ${$store.qrModal.table?.name ?? ''}`"
             role="img">
            <div x-show="$store.qrModal.qrSvg"
                 x-html="$store.qrModal.qrSvg"
                 class="w-48 h-48 [&>svg]:w-full [&>svg]:h-full">
            </div>
            <div x-show="!$store.qrModal.qrSvg && $store.qrModal.table"
                 class="w-48 h-48 flex items-center justify-center">
                <svg class="animate-spin w-8 h-8 text-indigo-400" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>
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

{{-- Panel de vista de mesa: QR + identificativo (modo solo lectura) --}}
<div x-data
     x-show="$store.viewPanel.show"
     @click.outside="$store.viewPanel.close()"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="translate-x-full opacity-0"
     x-transition:enter-end="translate-x-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0 opacity-100"
     x-transition:leave-end="translate-x-full opacity-0"
     class="fixed inset-y-0 right-0 w-80 sm:w-96 bg-white dark:bg-gray-800
            shadow-2xl border-l border-gray-200 dark:border-gray-700
            z-[95] flex flex-col overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="view-panel-title"
     @keydown.escape.window="$store.viewPanel.close()">

    {{-- Cabecera --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-3 h-3 rounded-full shrink-0"
                  :class="{
                      'bg-green-400':  !$store.viewPanel.table?.orderStatus || $store.viewPanel.table?.orderStatus === 'free',
                      'bg-amber-500':  $store.viewPanel.table?.orderStatus === 'occupied',
                      'bg-green-600 animate-pulse':  $store.viewPanel.table?.orderStatus === 'ready',
                      'bg-blue-500 animate-pulse':   $store.viewPanel.table?.orderStatus === 'payment_pending',
                  }"
                  :aria-label="{free:'Libre',occupied:'Ocupada',ready:'Lista para servir',payment_pending:'Pago pendiente'}[$store.viewPanel.table?.orderStatus] ?? 'Libre'">
            </span>
            <h2 id="view-panel-title"
                class="text-lg font-bold text-gray-900 dark:text-white truncate"
                x-text="$store.viewPanel.table?.name ?? ''">
            </h2>
        </div>
        <button type="button"
                @click="$store.viewPanel.close()"
                class="ml-2 w-8 h-8 shrink-0 rounded-full flex items-center justify-center
                       text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors"
                aria-label="Cerrar panel de mesa">
            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Cuerpo --}}
    <div class="flex-1 p-5 space-y-4 overflow-y-auto">

        {{-- Chips: estado + planta --}}
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                  :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': !$store.viewPanel.table?.orderStatus || $store.viewPanel.table?.orderStatus === 'free',
                      'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': $store.viewPanel.table?.orderStatus === 'occupied',
                      'bg-green-200 text-green-900 dark:bg-green-800/40 dark:text-green-200': $store.viewPanel.table?.orderStatus === 'ready',
                      'bg-blue-100  text-blue-800  dark:bg-blue-900/40  dark:text-blue-300':  $store.viewPanel.table?.orderStatus === 'payment_pending',
                  }"
                  x-text="{free:'Libre',occupied:'Ocupada',ready:'Lista para servir',payment_pending:'Pago pendiente'}[$store.viewPanel.table?.orderStatus] ?? 'Libre'">
            </span>
            <template x-if="floorsEnabled && $store.viewPanel.table?.floor">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium
                             bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    <svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Planta <span x-text="$store.viewPanel.table?.floor"></span>
                </span>
            </template>
        </div>

        {{-- QR de la mesa — SVG inline via fetch para evitar caché del navegador --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white p-4 flex flex-col items-center gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 self-start">
                Código QR
            </p>
            <div class="w-52 h-52 flex items-center justify-center"
                 :aria-label="$store.viewPanel.table ? `Código QR de ${$store.viewPanel.table.name}` : ''"
                 role="img">
                <div x-show="$store.viewPanel.qrSvg"
                     x-html="$store.viewPanel.qrSvg"
                     class="w-52 h-52 [&>svg]:w-full [&>svg]:h-full">
                </div>
                <div x-show="!$store.viewPanel.qrSvg && $store.viewPanel.table"
                     class="flex items-center justify-center w-full h-full">
                    <svg class="animate-spin w-8 h-8 text-indigo-400" aria-hidden="true"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- URL de la carta --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">
                Enlace de la carta
            </p>
            <p class="text-xs text-indigo-600 dark:text-indigo-400 break-all font-mono leading-relaxed"
               x-text="`${window.location.origin}/carta/${$store.viewPanel.table?.unique_hash ?? ''}`">
            </p>
        </div>

        {{-- Identificativo de la estructura --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
                Identificativo
            </p>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500 dark:text-gray-400">ID interno</dt>
                    <dd class="font-mono font-medium text-gray-900 dark:text-white"
                        x-text="`#${$store.viewPanel.table?.id ?? '—'}`"></dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500 dark:text-gray-400">Forma</dt>
                    <dd class="text-gray-900 dark:text-white"
                        x-text="{square:'Cuadrada',round:'Redonda',rectangle:'Rectangular',bar:'Barra',stool:'Taburete'}[$store.viewPanel.table?.shape] ?? '—'"></dd>
                </div>
                <template x-if="$store.viewPanel.table && zoneFor($store.viewPanel.table)">
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 dark:text-gray-400">Zona</dt>
                        <dd class="text-gray-900 dark:text-white"
                            x-text="zoneFor($store.viewPanel.table)?.name ?? '—'"></dd>
                    </div>
                </template>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500 dark:text-gray-400">Posición</dt>
                    <dd class="font-mono text-xs text-gray-700 dark:text-gray-300"
                        x-text="`(${$store.viewPanel.table?.position_x ?? 0}, ${$store.viewPanel.table?.position_y ?? 0})`"></dd>
                </div>
            </dl>
        </div>

    </div>

    {{-- Pie: acciones --}}
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 space-y-2 shrink-0">
        <a :href="`/mesas/${$store.viewPanel.table?.id}/qr/descargar`"
           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                  text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700
                  focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition-colors"
           :aria-label="`Descargar QR de ${$store.viewPanel.table?.name}`">
            <svg aria-hidden="true" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Descargar QR
        </a>
        <a :href="`${window.location.origin}/carta/${$store.viewPanel.table?.unique_hash ?? ''}`"
           target="_blank"
           rel="noopener noreferrer"
           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                  text-sm font-medium text-indigo-700 dark:text-indigo-300
                  bg-indigo-50 dark:bg-indigo-900/30
                  hover:bg-indigo-100 dark:hover:bg-indigo-900/50
                  focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition-colors"
           :aria-label="`Abrir carta digital de ${$store.viewPanel.table?.name} en nueva pestaña`">
            <svg aria-hidden="true" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Ver carta digital
        </a>
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
            class="text-lg font-bold text-center text-gray-900 dark:text-white mb-2"
            x-text="
                $store.deleteModal.table?._isFloor          ? `Eliminar Planta ${$store.deleteModal.table.name}` :
                $store.deleteModal.table?.shape === 'bar'   ? 'Eliminar barra' :
                $store.deleteModal.table?.shape === 'stool' ? 'Eliminar taburete' :
                $store.deleteModal.table?.shape             ? 'Eliminar mesa' :
                                                              'Eliminar zona'
            ">
        </h2>

        <p id="delete-modal-desc"
           class="text-sm text-center text-gray-500 dark:text-gray-400 mb-6">
            <template x-if="$store.deleteModal.table?._isFloor">
                <span>
                    Se eliminarán <span class="font-semibold text-gray-700 dark:text-gray-200"
                                        x-text="$store.deleteModal.table._count"></span>
                    estructura(s) de la <span class="font-semibold text-gray-700 dark:text-gray-200"
                                              x-text="`Planta ${$store.deleteModal.table.name}`"></span>.
                    Esta acción no se puede deshacer.
                </span>
            </template>
            <template x-if="!$store.deleteModal.table?._isFloor">
                <span>
                    ¿Eliminar <span class="font-semibold text-gray-700 dark:text-gray-200"
                                    x-text="`&quot;${$store.deleteModal.table?.name}&quot;`"></span>?
                    Esta acción no se puede deshacer.
                </span>
            </template>
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

{{-- Modal de confirmación de cambio de tamaño del plano --}}
<div x-data
     x-show="$store.sizeModal.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     aria-modal="true"
     role="alertdialog"
     aria-labelledby="size-modal-title"
     aria-describedby="size-modal-desc"
     @keydown.escape.window="$store.sizeModal.resolve(false)">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.stop>

        {{-- Icono de precaución --}}
        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4
                    rounded-full bg-amber-100 dark:bg-amber-900/30">
            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>

        <h2 id="size-modal-title"
            class="text-lg font-bold text-center text-gray-900 dark:text-white mb-3">
            Cambiar tamaño del plano a <span x-text="$store.sizeModal.label" class="text-amber-600 dark:text-amber-400"></span>
        </h2>

        <div id="size-modal-desc" class="text-sm text-gray-600 dark:text-gray-400 space-y-2 mb-6">
            <template x-if="$store.sizeModal.isShrink">
                <div class="space-y-2">
                    <p>Estás reduciendo el tamaño del plano. Ten en cuenta lo siguiente:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-500 dark:text-gray-400">
                        <li>Las estructuras que queden fuera del nuevo borde serán <span class="font-semibold text-amber-600 dark:text-amber-400">desplazadas automáticamente</span> hacia dentro del plano.</li>
                        <li>El diseño actual puede verse <span class="font-semibold text-amber-600 dark:text-amber-400">alterado</span> si hay mesas, barras o zonas cerca de los bordes.</li>
                        <li>Puedes usar <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs font-mono">Ctrl+Z</kbd> para deshacer si el resultado no es el esperado.</li>
                    </ul>
                </div>
            </template>
            <template x-if="!$store.sizeModal.isShrink">
                <p>El plano se ampliará al tamaño <span class="font-semibold" x-text="$store.sizeModal.label"></span>. Las estructuras existentes no se moverán.</p>
            </template>
        </div>

        <div class="flex gap-3">
            <button type="button"
                    @click="$store.sizeModal.resolve(false)"
                    class="flex-1 px-4 py-2 rounded-xl text-sm font-medium
                           text-gray-700 dark:text-gray-200
                           bg-gray-100 dark:bg-gray-700
                           hover:bg-gray-200 dark:hover:bg-gray-600
                           focus:outline-none focus:ring-2 focus:ring-gray-400
                           transition-colors">
                Cancelar
            </button>
            <button type="button"
                    @click="$store.sizeModal.resolve(true)"
                    x-init="$watch('$store.sizeModal.show', v => v && $nextTick(() => $el.focus()))"
                    class="flex-1 px-4 py-2 rounded-xl text-sm font-medium text-white
                           bg-amber-500 hover:bg-amber-600
                           focus:outline-none focus:ring-2 focus:ring-amber-400
                           transition-colors">
                Cambiar tamaño
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

        <h2 id="modal-title" class="text-lg font-bold text-gray-900 dark:text-white mb-4"
            x-text="$store.tableModal.mode === 'zone' ? 'Nueva zona' : 'Nueva mesa'">
        </h2>

        <div class="mb-4">
            <label for="new-table-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                   x-text="$store.tableModal.mode === 'zone' ? 'Nombre de la zona' : 'Nombre de la mesa'">
            </label>
            <input id="new-table-name"
                   type="text"
                   x-model="$store.tableModal.name"
                   @keydown.enter="$store.tableModal.confirm()"
                   maxlength="50"
                   :placeholder="$store.tableModal.mode === 'zone' ? 'Ej: Terraza, Interior...' : 'Ej: Terraza A... (vacío = número automático)'"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                          px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   :aria-required="$store.tableModal.mode === 'zone'"
                   x-init="$watch('$store.tableModal.open', v => v && $nextTick(() => $el.focus()))">
            <p x-show="$store.tableModal.mode === 'table'"
               class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                Deja vacío para asignar un número automático.
            </p>
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
                    :disabled="$store.tableModal.mode === 'zone' && !$store.tableModal.name.trim()"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white
                           bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50
                           disabled:cursor-not-allowed transition-colors">
                Crear
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL — Atajos de teclado
══════════════════════════════════════════════════════ --}}
<div x-data
     x-show="$store.helpModal.show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
     role="dialog"
     aria-modal="true"
     aria-labelledby="help-modal-title"
     @click.self="$store.helpModal.show = false"
     @keydown.escape.window="if ($store.helpModal.show) { $store.helpModal.show = false; $event.stopPropagation(); }"
     x-effect="if ($store.helpModal.show) $nextTick(() => $refs.helpClose?.focus())">

        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto
                    bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">

            {{-- Cabecera --}}
            <div class="sticky top-0 flex items-center justify-between
                        px-6 py-4 border-b border-gray-200 dark:border-gray-700
                        bg-white dark:bg-gray-800 rounded-t-2xl z-10">
                <h2 id="help-modal-title"
                    class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Atajos de teclado
                </h2>
                <button type="button"
                        x-ref="helpClose"
                        @click="$store.helpModal.show = false"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                               hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        aria-label="Cerrar panel de atajos">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Contenido --}}
            <div class="px-6 py-5 space-y-6">

                {{-- Navegar --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Navegar</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Seleccionar siguiente / anterior elemento</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Tab</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>Tab</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Abrir panel de edición</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Enter</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Space</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Abrir menú contextual</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Menu</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>F10</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Cerrar panel / Deseleccionar todo</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Esc</kbd></dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Mover --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Mover elemento</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Mover 10 px</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>↑</kbd> <kbd>↓</kbd> <kbd>←</kbd> <kbd>→</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Mover 1 px (precisión)</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>↑↓←→</kbd></dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Rotar --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Rotar elemento</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Girar sentido horario 5°</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>E</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Girar sentido horario 1°</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>E</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Girar sentido antihorario 5°</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>R</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Girar sentido antihorario 1°</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>R</kbd></dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Redimensionar --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Redimensionar elemento</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Ampliar / reducir 10 px</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Alt</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>↑↓←→</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Ampliar / reducir 1 px</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>Alt</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>↑↓←→</kbd></dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Vértices --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Editar vértices de zona / barra</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Añadir vértice — foco en <kbd>+</kbd></dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Enter</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Space</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Eliminar vértice — foco en <kbd>×</kbd></dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Enter</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Space</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Mover vértice 5 px (foco en <kbd>×</kbd>)</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>↑</kbd> <kbd>↓</kbd> <kbd>←</kbd> <kbd>→</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Mover vértice 1 px</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Shift</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>↑↓←→</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Salir del modo vértice</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Esc</kbd></dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- General --}}
                <section>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">General</h3>
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Copiar elemento seleccionado</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Ctrl</kbd><span class="text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>C</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Pegar copia</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Ctrl</kbd><span class="text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>V</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Eliminar elemento seleccionado</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Delete</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-0.5"> / </span><kbd>Backspace</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Deshacer</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Ctrl</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>Z</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Rehacer</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>Ctrl</kbd><span class="text-gray-400 dark:text-gray-400 text-[11px] font-medium select-none mx-px">+</span><kbd>Y</kbd></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">Abrir / cerrar esta ayuda</dt>
                            <dd class="flex items-center gap-1 shrink-0"><kbd>?</kbd></dd>
                        </div>
                    </dl>
                </section>

            </div>
        </div>
    </div>

{{-- interact.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>


@php
    $mapInit = [
        'tables'           => $tables,
        'elements'         => $elements,
        'zones'            => $zones,
        'floorWidth'       => $floorWidth,
        'floorHeight'      => $floorHeight,
        'floorsEnabled'    => $floorsEnabled,
        'floorCount'       => $floorCount,
        'floorCanvasSizes' => $floorCanvasSizes,
        'readonly'         => $readonly,
        'maxTables'        => $maxTables,
    ];
    $mapUrls = [
        'statuses'     => route('tables.map.statuses'),
        'canvasUpdate' => route('tables.canvas.update'),
        'store'        => route('tables.store'),
        'zonesStore'   => route('zones.store'),
        'floorSettings'=> route('tables.floor-settings'),
    ];
@endphp
<script id="map-init" type="application/json">@json($mapInit)</script>
<script id="map-urls" type="application/json">@json($mapUrls)</script>
</x-app-layout>
