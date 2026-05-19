{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Banner del Menú del Día en la carta digital pública.
     Se inserta al inicio del <main> de la carta, antes de las categorías. --}}
@props(['hash'])

@once
<style>[x-cloak]{display:none!important}</style>
<script>
window.dailyMenuBanner = function (hash) {
    const SECTION_LABELS = {
        first_course:  'Primer plato',
        second_course: 'Segundo plato',
        dessert:       'Postre',
        coffee:        'Café',
        drink:         'Bebida',
        bread:         'Pan',
    };

    return {
        hash,

        /* ── Banner ─────────────────────────────────────────────────── */
        menuData: null,
        loading:  true,
        open:     false,
        showExclusivityWarning: false,

        /* ── Stepper ─────────────────────────────────────────────────── */
        pasos:         [],
        pasoActual:    0,
        selections:    {},   // { [sectionId]: { product_id, product_name } }
        timings:       {},   // { [roundNumber]: delayMinutes }
        enviando:      false,
        enviado:       false,
        errorMsg:      null,
        intentoAvanzar: false,
        timingRules:   [],

        /* ── Computed ───────────────────────────────────────────────── */
        get pasoActualObj() {
            return this.pasos[this.pasoActual] ?? null;
        },

        get puedeAvanzar() {
            const paso = this.pasoActualObj;
            if (!paso) return false;
            if (paso.tipo === 'timing') return true;
            if (paso.tipo === 'seleccion') {
                return !paso.required || !!this.selections[paso.sectionId];
            }
            return true;
        },

        get horasEstimadas() {
            const result = {};
            const now = new Date();
            Object.entries(this.timings).forEach(([round, delay]) => {
                const d = new Date(now.getTime() + delay * 60000);
                result[parseInt(round)] = d.toLocaleTimeString('es-ES', {
                    hour: '2-digit', minute: '2-digit',
                });
            });
            return result;
        },

        /* ── Inicialización ─────────────────────────────────────────── */
        async init() {
            try {
                const res  = await fetch(`/api/v1/menu/${hash}/daily-menu`);
                const json = await res.json();
                this.menuData = json.data ?? null;
                if (this.menuData) this._buildFromData(this.menuData);
            } catch {
                this.menuData = null;
            } finally {
                this.loading = false;
            }
        },

        /* ── Lógica del banner ──────────────────────────────────────── */
        openStepper() {
            if (Alpine.store('cart').items.length > 0) {
                this.showExclusivityWarning = true;
            } else {
                this._doOpen();
            }
        },

        clearCartAndOpen() {
            const cart = Alpine.store('cart');
            cart.items          = [];
            cart._barItemsCount = 0;
            cart._variantsUsed  = 0;
            cart.sent           = false;
            cart.error          = null;
            this.showExclusivityWarning = false;
            this._doOpen();
        },

        _doOpen() {
            this.open = true;
            this.$nextTick(() => {
                const stepper  = document.getElementById('daily-menu-stepper');
                const focusable = stepper?.querySelector(
                    'button:not([disabled]), input:not([disabled]), [href]'
                );
                if (focusable) focusable.focus();
            });
        },

        close() {
            this.open = false;
            this.$nextTick(() => this.$refs.openButton?.focus());
        },

        /* ── Construcción del stepper ───────────────────────────────── */
        _buildFromData(data) {
            const sections = data.sections ?? [];
            const pasos    = [];
            const timings  = {};

            // Agrupar secciones por round_number de su timing_rule embebida.
            // La API no devuelve un array timing_rules raíz; cada sección
            // lleva su regla embebida como section.timing_rule.
            const byRound = new Map();
            const noRound = [];

            sections.forEach(section => {
                const rn = section.timing_rule?.round_number ?? null;
                if (rn !== null) {
                    if (!byRound.has(rn)) {
                        byRound.set(rn, {
                            round:    rn,
                            minDelay: section.timing_rule.estimated_prep_minutes,
                            sections: [],
                        });
                        timings[rn] = section.timing_rule.default_delay_minutes;
                    }
                    byRound.get(rn).sections.push(section);
                } else {
                    noRound.push(section);
                }
            });

            const rounds = [...byRound.values()].sort((a, b) => a.round - b.round);

            // Secciones sin regla de timing van primero
            noRound.forEach(section => {
                pasos.push({
                    tipo:      'seleccion',
                    sectionId: section.id,
                    label:     SECTION_LABELS[section.type] ?? section.type,
                    required:  section.is_required,
                    round:     null,
                    section,
                });
            });

            // Secciones con timing, intercaladas con pasos de timing
            rounds.forEach((group, idx) => {
                group.sections.forEach(section => {
                    pasos.push({
                        tipo:      'seleccion',
                        sectionId: section.id,
                        label:     SECTION_LABELS[section.type] ?? section.type,
                        required:  section.is_required,
                        round:     group.round,
                        section,
                    });
                });

                if (idx < rounds.length - 1) {
                    const nextGroup = rounds[idx + 1];
                    const nextSec   = nextGroup.sections[0];
                    const nextName  = nextSec
                        ? (SECTION_LABELS[nextSec.type] ?? nextSec.type).toLowerCase()
                        : 'el siguiente plato';
                    pasos.push({
                        tipo:     'timing',
                        round:    nextGroup.round,
                        label:    `¿Cuándo quieres ${nextName}?`,
                        minDelay: nextGroup.minDelay,
                    });
                }
            });

            pasos.push({ tipo: 'confirmacion', label: 'Confirmar pedido' });
            this.pasos       = pasos;
            this.timings     = timings;
            this.timingRules = rounds;
        },

        sectionLabel(type) {
            return SECTION_LABELS[type] ?? type;
        },

        /* ── Navegación del stepper ─────────────────────────────────── */
        siguiente() {
            const paso = this.pasoActualObj;
            if (!paso) return;

            if (paso.tipo === 'seleccion') {
                this.intentoAvanzar = true;
                if (paso.required && !this.selections[paso.sectionId]) return;
                this.intentoAvanzar = false;
            }

            this.pasoActual = Math.min(this.pasoActual + 1, this.pasos.length - 1);

            setTimeout(() => {
                const stepper = document.getElementById('daily-menu-stepper');
                const el = stepper && stepper.querySelector(
                    '[data-step-content] button:not([disabled]), [data-step-content] input:not([disabled])'
                );
                if (el) el.focus();
            }, 50);
        },

        anterior() {
            this.intentoAvanzar = false;
            this.pasoActual = Math.max(0, this.pasoActual - 1);
        },

        irAPaso(idx) {
            if (idx <= this.pasoActual) this.pasoActual = idx;
        },

        /* ── Selecciones ────────────────────────────────────────────── */
        selectProduct(sectionId, productId, productName) {
            this.selections = {
                ...this.selections,
                [sectionId]: { product_id: productId, product_name: productName },
            };
        },

        skipSection(sectionId) {
            const copy = { ...this.selections };
            delete copy[sectionId];
            this.selections = copy;
            this.siguiente();
        },

        /* ── Timing ─────────────────────────────────────────────────── */
        decrementTiming(round, min) {
            if (this.timings[round] > min) {
                this.timings = {
                    ...this.timings,
                    [round]: Math.max(min, this.timings[round] - 5),
                };
            }
        },

        incrementTiming(round) {
            if (this.timings[round] < 120) {
                this.timings = {
                    ...this.timings,
                    [round]: Math.min(120, this.timings[round] + 5),
                };
            }
        },

        /* ── Confirmación ───────────────────────────────────────────── */
        confirmationSelections() {
            if (!this.menuData) return [];
            return (this.menuData.sections ?? [])
                .filter(s => this.selections[s.id])
                .map(s => ({
                    section_label: SECTION_LABELS[s.type] ?? s.type,
                    product_name:  this.selections[s.id].product_name,
                }));
        },

        async submitOrder() {
            this.enviando = true;
            this.errorMsg = null;
            try {
                const body = {
                    selections: Object.entries(this.selections)
                        .filter(([, v]) => v)
                        .map(([sectionId, sel]) => ({
                            section_id: parseInt(sectionId),
                            product_id: sel.product_id,
                            quantity:   1,
                        })),
                    timing_overrides: (() => {
                        const firstRound = this.timingRules.length > 0 ? this.timingRules[0].round : null;
                        return Object.entries(this.timings)
                            .filter(([round]) => parseInt(round) !== firstRound)
                            .map(([round, delay]) => ({
                                round:         parseInt(round),
                                delay_minutes: delay,
                            }));
                    })(),
                };
                const res = await fetch(`/api/v1/menu/${hash}/daily-menu/order`, {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                const result = await res.json();
                if (res.ok) {
                    this.enviado = true;
                } else {
                    this.errorMsg = result.message ?? 'Error al enviar el pedido.';
                }
            } catch {
                this.errorMsg = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.enviando = false;
            }
        },
    };
};
</script>
@endonce

<div
    x-data="dailyMenuBanner('{{ $hash }}')"
    x-show="!loading && menuData !== null"
    x-cloak
    class="mb-4"
>
    {{-- ── Card del banner ────────────────────────────────────────── --}}
    <div class="rounded-xl border border-blue-600/40
                bg-gradient-to-br from-[#2E50B0] to-[#1A3380]
                dark:from-[#1A3380] dark:to-[#0E1A38]
                p-4 shadow-lg shadow-blue-900/40">

        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">

                {{-- Etiqueta superior --}}
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="currentColor"
                         viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-xs font-semibold uppercase tracking-widest
                                 text-blue-200">
                        Menú del día
                    </span>
                </div>

                {{-- Título y descripción --}}
                <h2 class="text-white font-bold text-lg leading-snug"
                    x-text="menuData?.menu?.title"></h2>
                <p class="text-blue-200 text-sm mt-0.5 line-clamp-2"
                   x-text="menuData?.menu?.description"
                   x-show="menuData?.menu?.description"></p>

                {{-- Secciones como pills --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <template x-for="section in menuData?.sections ?? []"
                              :key="section.id">
                        <span class="text-xs px-2 py-0.5 rounded-full
                                     bg-white/10 text-blue-100">
                            <span x-text="sectionLabel(section.type)"></span>
                            <span x-show="section.is_free"
                                  class="text-yellow-300"> · incluido</span>
                        </span>
                    </template>
                </div>

            </div>

            {{-- Precio y disponibilidad --}}
            <div class="flex-shrink-0 text-right">
                <span class="text-2xl font-bold text-white"
                      x-text="parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' €'">
                </span>
                <x-daily-menu-availability />
            </div>
        </div>

        {{-- Botón de apertura --}}
        <button
            x-ref="openButton"
            type="button"
            @click="openStepper()"
            :disabled="menuData?.menu?.available_count === 0"
            aria-haspopup="dialog"
            :aria-expanded="open.toString()"
            aria-controls="daily-menu-stepper"
            class="mt-4 w-full py-2.5 px-4 rounded-lg font-semibold text-sm min-h-[44px]
                   bg-yellow-400 text-gray-900 hover:bg-yellow-300
                   focus:outline-none focus:ring-2 focus:ring-yellow-400
                   focus:ring-offset-2 focus:ring-offset-blue-900
                   disabled:opacity-40 disabled:cursor-not-allowed
                   transition-colors duration-150"
        >
            <span x-show="menuData?.menu?.available_count !== 0">
                Ver el menú completo →
            </span>
            <span x-show="menuData?.menu?.available_count === 0">
                Menú agotado
            </span>
        </button>

    </div>

    {{-- Dialog de exclusividad --}}
    <x-daily-menu-exclusivity-dialog />

    {{-- Stepper modal --}}
    <x-daily-menu-stepper :hash="$hash" x-show="open" />

</div>
