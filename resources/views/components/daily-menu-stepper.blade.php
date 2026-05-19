{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Stepper modal del menú del día.
     Sin x-data propio: hereda todo el scope Alpine del banner.
     Se usa con: <x-daily-menu-stepper :hash="$hash" x-show="open" @close.window="close()" /> --}}
@props(['hash'])

<div
    id="daily-menu-stepper"
    role="dialog"
    aria-modal="true"
    aria-labelledby="stepper-title"
    @keydown.escape.window="if (open) close()"
    @keydown.tab.window="
        if (!open || !$el.contains(document.activeElement)) return;
        $event.preventDefault();
        const focusable = [...$el.querySelectorAll('button:not([disabled]),[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex=\'-1\'])')].filter(el => el.offsetParent !== null);
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        focusable[$event.shiftKey
            ? (idx - 1 + focusable.length) % focusable.length
            : (idx + 1) % focusable.length
        ].focus();
    "
    class="fixed inset-0 z-50 flex flex-col
           bg-[#050B1F]/95 backdrop-blur-sm
           sm:items-center sm:justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-cloak
    {{ $attributes }}
>

    {{-- Panel interior --}}
    <div class="relative flex flex-col w-full h-full
                sm:h-auto sm:max-w-lg sm:max-h-[90vh]
                sm:rounded-2xl sm:border sm:border-blue-600/40
                bg-[#0E1A38] overflow-hidden">

        {{-- ── Pantalla de éxito ─────────────────────────────────────── --}}
        <div
            x-show="enviado"
            class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center"
            role="status"
            aria-live="polite"
        >
            <div class="w-16 h-16 rounded-full bg-green-500/20 border border-green-500/30
                        flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-white font-bold text-xl mb-2">¡Pedido enviado!</h3>
            <p class="text-blue-200 text-sm max-w-xs leading-relaxed">
                Tu menú del día está en camino. Te lo serviremos en los tiempos acordados.
            </p>
            <button
                @click="close()"
                class="mt-8 px-6 py-2.5 rounded-lg font-semibold text-sm
                       bg-[#2E50B0] text-white hover:bg-[#3660CC]
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:ring-offset-2 focus:ring-offset-[#0E1A38]
                       transition-colors"
            >
                Volver a la carta
            </button>
        </div>

        {{-- ── Flujo del stepper ─────────────────────────────────────── --}}
        <div x-show="!enviado" class="flex flex-col flex-1 min-h-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 pt-4 pb-3
                        border-b border-blue-800/40 flex-shrink-0 gap-3">

                <h2 id="stepper-title"
                    class="text-white font-bold text-base shrink-0">
                    Menú del Día
                </h2>

                {{-- Indicador de pasos (dots) --}}
                <nav aria-label="Pasos del menú del día"
                     class="flex items-center gap-1.5 flex-1 justify-center flex-wrap">
                    <template x-for="(paso, idx) in pasos" :key="idx">
                        <button
                            type="button"
                            @click="irAPaso(idx)"
                            :aria-current="pasoActual === idx ? 'step' : undefined"
                            :aria-label="`Paso ${idx + 1} de ${pasos.length}: ${paso.label}`"
                            :class="{
                                'rounded-full transition-all duration-200': true,
                                'w-3 h-3 bg-[#2E50B0] scale-125': pasoActual === idx,
                                'w-2.5 h-2.5 bg-[#2E50B0]/50': pasoActual !== idx && idx < pasoActual,
                                'w-2.5 h-2.5 bg-white/20': idx > pasoActual
                            }"
                        ></button>
                    </template>
                </nav>

                {{-- Botón cerrar --}}
                <button
                    type="button"
                    @click="close()"
                    aria-label="Cerrar menú del día"
                    class="shrink-0 p-2 rounded-lg text-blue-300 hover:text-white
                           hover:bg-white/10 focus:outline-none
                           focus:ring-2 focus:ring-blue-400 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Barra de progreso --}}
            <div class="h-0.5 bg-white/10 flex-shrink-0" aria-hidden="true">
                <div class="h-full bg-[#2E50B0] transition-all duration-300"
                     :style="`width: ${pasos.length ? ((pasoActual + 1) / pasos.length) * 100 : 0}%`">
                </div>
            </div>

            {{-- ── Área de contenido scrollable ──────────────────────── --}}
            <div class="flex-1 overflow-y-auto px-4 py-5 min-h-0" data-step-content>

                {{-- Paso de selección de producto --}}
                <div x-show="pasoActualObj?.tipo === 'seleccion'">

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-white font-bold text-lg"
                            x-text="pasoActualObj?.label"></h3>
                        <template x-if="(pasoActualObj?.section?.max_quantity ?? 1) > 1">
                            <span class="text-xs text-blue-300 bg-blue-900/40 border border-blue-700/50 px-2.5 py-1 rounded-full"
                                  aria-live="polite">
                                <span x-text="(selections[pasoActualObj?.sectionId] ?? []).length"></span>/<span x-text="pasoActualObj?.section?.max_quantity"></span> elegidos
                            </span>
                        </template>
                    </div>

                    <div class="space-y-2" role="list">
                        <template x-for="product in pasoActualObj?.section?.products ?? []"
                                  :key="product.id">
                            <button
                                type="button"
                                role="listitem"
                                @click="selectProduct(pasoActualObj.sectionId, product.id, product.name, pasoActualObj.section?.max_quantity ?? 1)"
                                :aria-pressed="(selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id)"
                                :disabled="!(selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id) && (selections[pasoActualObj?.sectionId] ?? []).length >= (pasoActualObj?.section?.max_quantity ?? 1)"
                                :class="{
                                    'w-full text-left p-3 rounded-xl border-2 transition-all duration-150': true,
                                    'border-[#2E50B0] bg-[#2E50B0]/20': (selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id),
                                    'border-blue-800/40 bg-white/5 hover:border-blue-600/60 hover:bg-white/10': !(selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id),
                                    'opacity-40 cursor-not-allowed': !(selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id) && (selections[pasoActualObj?.sectionId] ?? []).length >= (pasoActualObj?.section?.max_quantity ?? 1)
                                }"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white font-medium text-sm leading-snug"
                                           x-text="product.name"></p>
                                        <p class="text-blue-300 text-xs mt-0.5 line-clamp-2"
                                           x-text="product.description"
                                           x-show="product.description"></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <template x-if="!pasoActualObj?.section?.is_free">
                                            <span class="text-blue-200 text-sm font-semibold"
                                                  x-text="parseFloat(product.price).toFixed(2).replace('.', ',') + ' €'"></span>
                                        </template>
                                        <template x-if="pasoActualObj?.section?.is_free">
                                            <span class="text-green-400 text-xs font-medium px-1.5 py-0.5
                                                         rounded bg-green-500/10 border border-green-500/20">
                                                Incluido
                                            </span>
                                        </template>
                                        <span
                                            x-show="(selections[pasoActualObj?.sectionId] ?? []).some(s => s.product_id === product.id)"
                                            class="w-5 h-5 rounded-full bg-[#2E50B0]
                                                   flex items-center justify-center
                                                   text-white text-xs font-bold flex-shrink-0"
                                            aria-hidden="true"
                                        >✓</span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- Alerta si obligatorio y no seleccionó --}}
                    <div
                        x-show="intentoAvanzar && pasoActualObj?.required && !((selections[pasoActualObj?.sectionId] ?? []).length > 0)"
                        role="alert"
                        class="mt-3 flex items-center gap-2 text-red-400 text-xs
                               bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor"
                             viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Por favor, selecciona una opción para continuar.
                    </div>

                    {{-- Enlace para secciones opcionales --}}
                    <button
                        type="button"
                        x-show="!pasoActualObj?.required"
                        @click="skipSection(pasoActualObj.sectionId)"
                        class="mt-3 text-sm text-blue-400 hover:text-blue-200
                               underline underline-offset-2 transition-colors
                               focus:outline-none focus:ring-1 focus:ring-blue-400 rounded"
                    >
                        Sin <span x-text="pasoActualObj?.label?.toLowerCase()"></span>, gracias →
                    </button>

                </div>

                {{-- Paso de timing --}}
                <div x-show="pasoActualObj?.tipo === 'timing'">
                    <x-daily-menu-timing-control />
                </div>

                {{-- Paso de confirmación --}}
                <div x-show="pasoActualObj?.tipo === 'confirmacion'">

                    <h3 class="text-white font-bold text-lg mb-5">Resumen de tu pedido</h3>

                    <div id="confirmation-summary" class="space-y-3">
                        <template x-for="sel in confirmationSelections()" :key="sel.section_label">
                            <div class="flex items-start gap-3 text-sm
                                        border-b border-blue-800/30 pb-3 last:border-0 last:pb-0">
                                <span class="text-blue-400 w-28 flex-shrink-0 pt-0.5"
                                      x-text="sel.section_label"></span>
                                <span class="text-white font-medium leading-snug"
                                      x-text="sel.product_name"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Horarios estimados por ronda --}}
                    <div class="mt-4 pt-4 border-t border-blue-800/40"
                         x-show="timingRules.length > 1">
                        <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider mb-2">
                            Horarios estimados
                        </p>
                        <template x-for="rule in timingRules" :key="rule.round">
                            <div class="flex items-center justify-between text-xs text-blue-300 mb-1.5">
                                <span x-text="`Ronda ${rule.round}`"></span>
                                <span class="font-semibold text-blue-100"
                                      x-text="horasEstimadas[rule.round] ?? '--:--'"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Precio total del menú --}}
                    <div class="mt-4 pt-4 border-t border-blue-800/40
                                flex items-center justify-between">
                        <span class="text-blue-300 text-sm">Total</span>
                        <span class="text-white font-bold text-lg"
                              x-text="parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>

                    {{-- Mensaje de error --}}
                    <div x-show="errorMsg"
                         role="alert"
                         class="mt-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30
                                text-red-400 text-sm flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor"
                             viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span x-text="errorMsg"></span>
                    </div>

                </div>

            </div>{{-- /data-step-content --}}

            {{-- ── Footer de navegación ───────────────────────────────── --}}
            <div class="flex items-center justify-between gap-3
                        px-4 py-3 border-t border-blue-800/40 flex-shrink-0">

                {{-- Anterior --}}
                <button
                    type="button"
                    x-show="pasoActual > 0"
                    @click="anterior()"
                    class="px-4 py-2.5 rounded-lg text-sm font-medium min-h-[44px]
                           text-blue-300 hover:text-white hover:bg-white/10
                           focus:outline-none focus:ring-2 focus:ring-blue-400
                           transition-colors"
                >
                    ← Anterior
                </button>
                <div x-show="pasoActual === 0" class="w-10" aria-hidden="true"></div>

                {{-- Siguiente (pasos de selección y timing) --}}
                <button
                    type="button"
                    x-show="pasos[pasoActual]?.tipo !== 'confirmacion'"
                    @click="
                        const _p = pasos[pasoActual];
                        if (!_p) return;
                        if (_p.tipo === 'seleccion' && _p.required && !((selections[_p.sectionId] ?? []).length > 0)) {
                            intentoAvanzar = true;
                            return;
                        }
                        intentoAvanzar = false;
                        if (pasoActual + 1 < pasos.length) pasoActual = pasoActual + 1;
                    "
                    :disabled="pasos[pasoActual]?.tipo === 'seleccion' && pasos[pasoActual]?.required && !((selections[pasos[pasoActual]?.sectionId] ?? []).length > 0)"
                    :aria-disabled="pasos[pasoActual]?.tipo === 'seleccion' && pasos[pasoActual]?.required && !((selections[pasos[pasoActual]?.sectionId] ?? []).length > 0)"
                    :aria-label="`Ir al paso siguiente: ${pasos[pasoActual + 1]?.label ?? ''}`"
                    :class="{
                        'px-5 py-2.5 rounded-lg text-sm font-semibold transition-all min-h-[44px]': true,
                        'bg-[#2E50B0] text-white hover:bg-[#3660CC] focus:outline-none focus:ring-2 focus:ring-blue-400': !(pasos[pasoActual]?.tipo === 'seleccion' && pasos[pasoActual]?.required && !((selections[pasos[pasoActual]?.sectionId] ?? []).length > 0)),
                        'bg-white/10 text-white/40 cursor-not-allowed': pasos[pasoActual]?.tipo === 'seleccion' && pasos[pasoActual]?.required && !((selections[pasos[pasoActual]?.sectionId] ?? []).length > 0)
                    }"
                >
                    Siguiente →
                </button>

                {{-- Confirmar (paso de confirmación) --}}
                <button
                    type="button"
                    x-show="pasoActualObj?.tipo === 'confirmacion'"
                    @click="submitOrder()"
                    :disabled="enviando"
                    :aria-busy="enviando"
                    aria-describedby="confirmation-summary"
                    class="flex-1 py-2.5 px-4 rounded-lg font-semibold text-sm min-h-[44px]
                           bg-yellow-400 text-gray-900 hover:bg-yellow-300
                           focus:outline-none focus:ring-2 focus:ring-yellow-400
                           focus:ring-offset-2 focus:ring-offset-[#0E1A38]
                           disabled:opacity-50 disabled:cursor-not-allowed
                           transition-colors"
                >
                    <span x-show="!enviando">Confirmar menú y enviar pedido</span>
                    <span x-show="enviando"
                          class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"
                             aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Enviando pedido...
                    </span>
                </button>

            </div>

        </div>{{-- /!enviado --}}

    </div>{{-- /panel interior --}}

</div>
