{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Stepper modal del menú del día — estructura idéntica al DS (scrim + dm-modal).
     Sin x-data propio: hereda el scope Alpine del banner (dailyMenuBanner). --}}
@props(['hash'])

{{-- ── Scrim + dm-modal ─────────────────────────────────────────── --}}
<div
    class="scrim scrim--center scrim--dm"
    x-show="open"
    :class="closingModal ? 'is-closing' : ''"
    @click="requestClose()"
    @keydown.escape.window="if (open) requestClose()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="dm-step-title"
    x-cloak
    {{ $attributes }}
>
    {{-- ── Panel dm-modal ──────────────────────────────────────── --}}
    <div
        class="dm-modal"
        :class="closingModal ? 'is-closing' : ''"
        @click.stop
    >
        {{-- Cabecera --}}
        <div class="dm-modal__head">
            <div class="dm-modal__brandRow">
                <span class="dm-modal__mark" aria-hidden="true">◇</span>
                <span class="dm-modal__brand">MENÚ DEL DÍA</span>
                <span class="dm-modal__date"
                      x-text="new Date().toLocaleDateString('es-ES', {day:'numeric', month:'long'})">
                </span>
            </div>
            <button type="button" class="icon-btn dm-modal__close"
                    @click="requestClose()"
                    aria-label="Cerrar menú del día">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Barra de progreso --}}
        <div
            class="dm-modal__progress"
            x-show="!enviado"
            role="progressbar"
            :aria-valuemin="1"
            :aria-valuemax="pasos.length"
            :aria-valuenow="pasoActual + 1"
            :aria-label="'Paso ' + (pasoActual + 1) + ' de ' + pasos.length"
        >
            <div class="dm-modal__progressBar">
                <div class="dm-modal__progressFill"
                     :style="'width: ' + pct + '%'">
                </div>
            </div>
            <div class="dm-modal__progressLabel"
                 x-text="'Paso ' + (pasoActual + 1) + ' de ' + pasos.length">
            </div>
        </div>

        {{-- ── Cuerpo scrollable ────────────────────────────────── --}}
        <div class="dm-modal__body">

            {{-- Pantalla de éxito --}}
            <div x-show="enviado" class="dm-step dm-step--done" role="status" aria-live="polite">
                <div class="dm-step__doneIc" aria-hidden="true">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="3"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12l5 5L20 6"/>
                    </svg>
                </div>
                <h3 class="dm-step__doneTitle">¡Pedido enviado a cocina!</h3>
                <p class="dm-step__doneCopy">
                    Tu menú del día está en camino. Te lo serviremos en los tiempos acordados.
                </p>
            </div>

            {{-- Paso de selección --}}
            <div x-show="!enviado && pasoActualObj?.tipo === 'seleccion'">
                <div class="dm-step">
                    <div class="dm-step__head">
                        <h3 class="dm-step__title" id="dm-step-title"
                            x-text="pasoActualObj?.label"></h3>
                        <p class="dm-step__hint"
                           x-text="(pasoActualObj?.section?.max_quantity ?? 1) === 1
                               ? (pasoActualObj?.optional ? 'Elige uno (opcional)' : 'Elige uno')
                               : 'Elige hasta ' + pasoActualObj?.section?.max_quantity">
                        </p>
                    </div>

                    <div class="dm-step__options"
                         :role="(pasoActualObj?.section?.max_quantity ?? 1) === 1 ? 'radiogroup' : 'group'"
                         aria-labelledby="dm-step-title">
                        <template x-for="product in pasoActualObj?.section?.products ?? []"
                                  :key="product.id">
                            <button
                                type="button"
                                :class="'dm-opt' +
                                    (isSelected(pasoActualObj.sectionId, product.id) ? ' dm-opt--on' : '') +
                                    (!product.is_active ? ' dm-opt--off' : '')"
                                @click="product.is_active && selectProduct(pasoActualObj.sectionId, product.id, product.name, pasoActualObj.section?.max_quantity ?? 1)"
                                :role="(pasoActualObj?.section?.max_quantity ?? 1) === 1 ? 'radio' : 'checkbox'"
                                :aria-checked="isSelected(pasoActualObj.sectionId, product.id)"
                                :disabled="!product.is_active"
                            >
                                <span
                                    :class="(pasoActualObj?.section?.max_quantity ?? 1) === 1
                                        ? 'dm-opt__radio' + (isSelected(pasoActualObj.sectionId, product.id) ? ' is-on' : '')
                                        : 'dm-opt__check' + (isSelected(pasoActualObj.sectionId, product.id) ? ' is-on' : '')"
                                    aria-hidden="true">
                                    <span x-show="(pasoActualObj?.section?.max_quantity ?? 1) > 1 && isSelected(pasoActualObj.sectionId, product.id)">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="3.2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12l5 5L20 7"/>
                                        </svg>
                                    </span>
                                </span>

                                <div :class="'dm-opt__photo dm-opt__photo--food-' + ((product.id % 6) + 1)"
                                     aria-hidden="true">
                                    <template x-if="product.image">
                                        <img :src="'/storage/' + product.image"
                                             :alt="product.name"
                                             class="w-full h-full object-cover rounded-[inherit]">
                                    </template>
                                </div>

                                <div class="dm-opt__body">
                                    <div class="dm-opt__nameRow">
                                        <span class="dm-opt__name" x-text="product.name"></span>
                                        <template x-if="!product.is_active">
                                            <span class="dm-opt__badge">Agotado</span>
                                        </template>
                                    </div>
                                    <span class="dm-opt__desc"
                                          x-text="product.description"
                                          x-show="product.description"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- Alerta obligatorio --}}
                    <div x-show="intentoAvanzar && pasoActualObj?.required && !((selections[pasoActualObj?.sectionId] ?? []).filter(s => s.product_id !== '__skipped__').length > 0)"
                         role="alert"
                         class="dm-step__error mt-[10px]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Por favor, selecciona una opción para continuar.
                    </div>

                    {{-- Skip opcional --}}
                    <template x-if="pasoActualObj?.optional">
                        <button
                            type="button"
                            :class="'dm-skip' + (isSkipped(pasoActualObj.sectionId) ? ' dm-skip--on' : '')"
                            @click="isSkipped(pasoActualObj.sectionId) ? selectProduct(pasoActualObj.sectionId, '__skipped__', '', 1) : skipSection(pasoActualObj.sectionId)"
                        >
                            <span :class="'dm-skip__check' + (isSkipped(pasoActualObj.sectionId) ? ' is-on' : '')" aria-hidden="true">
                                <template x-if="isSkipped(pasoActualObj.sectionId)">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3.5"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12l5 5L20 7"/>
                                    </svg>
                                </template>
                            </span>
                            <span x-text="isSkipped(pasoActualObj.sectionId)
                                ? 'Sin ' + pasoActualObj.label.toLowerCase() + ' (cambiar)'
                                : 'Sin ' + pasoActualObj.label.toLowerCase() + ', gracias'">
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Paso de timing --}}
            <div x-show="!enviado && pasoActualObj?.tipo === 'timing'">
                <x-daily-menu-timing-control />
            </div>

            {{-- Paso de confirmación --}}
            <div x-show="!enviado && pasoActualObj?.tipo === 'confirmacion'">
                <div class="dm-step">
                    <div class="dm-step__head">
                        <h3 class="dm-step__title" id="dm-step-title">Revisa tu selección</h3>
                        <p class="dm-step__hint">Confirma y enviamos a cocina.</p>
                    </div>

                    <div class="dm-summary">
                        <template x-for="row in confirmationSelections()" :key="row.section_label">
                            <div class="dm-summary__row">
                                <div class="dm-summary__lab" x-text="row.section_label"></div>
                                <div class="dm-summary__val">
                                    <template x-if="row.skipped">
                                        <span class="dm-summary__muted" x-text="'Sin ' + row.section_label.toLowerCase()"></span>
                                    </template>
                                    <template x-if="!row.skipped && row.empty">
                                        <span class="dm-summary__muted">— sin elegir</span>
                                    </template>
                                    <template x-if="!row.skipped && !row.empty">
                                        <span x-text="row.products.join(' · ')"></span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Tiempos entre rondas --}}
                        <template x-if="timingRules.length > 1">
                            <div class="dm-summary__row dm-summary__row--inset">
                                <div class="dm-summary__lab">Tiempos</div>
                                <div class="dm-summary__val"
                                     x-text="timingRules.map(r => timings[r.round] + ' min').join(' · ')">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="dm-summary__total">
                        <span class="dm-summary__totalLab">Total del menú</span>
                        <span class="dm-summary__totalVal"
                              x-text="parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' €'">
                        </span>
                    </div>

                    {{-- Error --}}
                    <div x-show="errorMsg" role="alert"
                         class="dm-step__error mt-3">
                        <span x-text="errorMsg"></span>
                    </div>
                </div>
            </div>

        </div>{{-- /dm-modal__body --}}

        {{-- ── Pie de navegación ───────────────────────────────── --}}
        <div class="dm-modal__foot">

            {{-- Éxito: solo botón cerrar --}}
            <template x-if="enviado">
                <button type="button" class="btn-primary" @click="requestClose()">Cerrar</button>
            </template>

            {{-- Confirmación: atrás + confirmar --}}
            <template x-if="!enviado && isLastStep">
                <span class="contents">
                    <button type="button" class="btn-text" @click="anterior()" :disabled="enviando">
                        Atrás
                    </button>
                    <button type="button" class="btn-primary dm-step__submit"
                            @click="submitOrder()" :disabled="enviando">
                        <span x-show="!enviando">
                            Confirmar y enviar ·
                            <span x-text="parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' €'"></span>
                        </span>
                        <span x-show="enviando">Enviando…</span>
                    </button>
                </span>
            </template>

            {{-- Pasos intermedios: cancelar/atrás + siguiente --}}
            <template x-if="!enviado && !isLastStep">
                <span class="contents">
                    <button type="button" class="btn-text" @click="anterior()">
                        <span x-text="isFirstStep ? 'Cancelar' : 'Atrás'"></span>
                    </button>
                    <button type="button" class="btn-primary"
                            @click="siguiente()"
                            :disabled="!canProceed">
                        Siguiente
                    </button>
                </span>
            </template>

        </div>

    </div>{{-- /dm-modal --}}
</div>{{-- /scrim --}}
