/**
 * Alpine.js component — daily menu banner & stepper.
 * Renders the "Menú del Día" floating banner and multi-step order modal.
 *
 * @author SebastianBCF
 * @author AyrtonAlania
 */

import Alpine from 'alpinejs';

const SECTION_LABELS = {
    first_course:  'Primer plato',
    second_course: 'Segundo plato',
    dessert:       'Postre',
    coffee:        'Café',
    drink:         'Bebida',
    bread:         'Pan',
};

/**
 * @param {string} hash  Table unique hash used to fetch the daily menu API.
 * @returns {object}
 */
export function dailyMenuBanner(hash) {
    return {
        hash,
        menuData:        null,
        loading:         true,
        open:            false,
        exiting:         false,
        stuck:           false,
        bannerDismissed: false,
        showExclusivityWarning: false,
        _autoDismissTimer: null,
        _scrollEl:         null,

        /* ── Stepper ─────────────────────────────────────────────────────────── */
        pasos:          [],
        pasoActual:     0,
        selections:     {},
        timings:        {},
        timingRules:    [],
        enviando:       false,
        enviado:        false,
        errorMsg:       null,
        intentoAvanzar: false,
        closingModal:   false,

        /* ── Computed ─────────────────────────────────────────────────────────── */
        get agotado() {
            return !this.menuData || this.menuData.menu?.available_count === 0;
        },
        get pasoActualObj() {
            return this.pasos[this.pasoActual] ?? null;
        },
        get pct() {
            if (!this.pasos.length) return 0;
            if (this.enviado) return 100;
            return Math.round(this.pasoActual / Math.max(this.pasos.length - 1, 1) * 100);
        },
        get isLastStep() {
            return this.pasoActualObj?.tipo === 'confirmacion';
        },
        get isFirstStep() {
            return this.pasoActual === 0;
        },
        get canProceed() {
            const p = this.pasoActualObj;
            if (!p) return false;
            if (p.tipo === 'timing' || p.tipo === 'confirmacion') return true;
            const picks = this.selections[p.sectionId] ?? [];
            if (p.optional) return true;
            return picks.length >= 1;
        },
        get dateLabel() {
            const d = new Date();
            return d.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
        },

        /* ── Init ─────────────────────────────────────────────────────────────── */
        async init() {
            try {
                const res  = await fetch('/api/v1/menu/' + this.hash + '/daily-menu');
                const json = await res.json();
                this.menuData = json.data ?? null;
                if (this.menuData) {
                    this._buildFromData(this.menuData);
                    this._startAutoDismiss();
                    this._bindScroll();
                }
            } catch {
                this.menuData = null;
            } finally {
                this.loading = false;
            }
        },

        _startAutoDismiss() {
            this._autoDismissTimer = setTimeout(() => this.dismiss(), 22000);
        },

        _bindScroll() {
            this._scrollEl = document.getElementById('main-content');
            if (!this._scrollEl) return;
            const onScroll = () => { this.stuck = this._scrollEl.scrollTop > 6; };
            this._scrollEl.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        },

        /* ── Banner ───────────────────────────────────────────────────────────── */
        dismiss() {
            if (this.exiting) return;
            this.exiting = true;
            if (this._autoDismissTimer) {
                clearTimeout(this._autoDismissTimer);
                this._autoDismissTimer = null;
            }
            setTimeout(() => { this.bannerDismissed = true; }, 280);
        },

        openStepper() {
            if (this.agotado) return;
            if (Alpine.store('cart').items.length > 0) {
                this.showExclusivityWarning = true;
            } else {
                this._doOpen();
            }
        },

        async clearCartAndOpen() {
            const cart = Alpine.store('cart');
            cart.items          = [];
            cart._barItemsCount = 0;
            cart._variantsUsed  = 0;
            cart.sent           = false;
            cart.error          = null;
            this.showExclusivityWarning = false;

            await fetch('/api/v1/menu/' + this.hash + '/cancel-alacarte', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept':       'application/json',
                },
            }).catch(() => {});

            this._doOpen();
        },

        _doOpen() {
            this.showExclusivityWarning = false;
            this.open         = true;
            this.closingModal = false;
        },

        requestClose() {
            if (this.enviando && !this.enviado) return;
            if (this.closingModal) return;
            this.closingModal = true;
            setTimeout(() => {
                this.open         = false;
                this.closingModal = false;
                if (this.enviado) {
                    this.enviado    = false;
                    this.pasoActual = 0;
                    this.selections = {};
                }
            }, 240);
        },

        /* ── Stepper build ────────────────────────────────────────────────────── */
        _buildFromData(data) {
            const sections = data.sections ?? [];
            const pasos    = [];
            const timings  = {};
            const byRound  = new Map();
            const noRound  = [];

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

            noRound.forEach(section => {
                pasos.push({
                    tipo:      'seleccion',
                    sectionId: section.id,
                    label:     SECTION_LABELS[section.type] ?? section.type,
                    required:  section.is_required,
                    optional:  !section.is_required,
                    round:     null,
                    section,
                });
            });

            rounds.forEach((group, idx) => {
                group.sections.forEach(section => {
                    pasos.push({
                        tipo:      'seleccion',
                        sectionId: section.id,
                        label:     SECTION_LABELS[section.type] ?? section.type,
                        required:  section.is_required,
                        optional:  !section.is_required,
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
                        label:    '¿Cuándo quieres ' + nextName + '?',
                        minDelay: nextGroup.minDelay,
                    });
                }
            });

            pasos.push({ tipo: 'confirmacion', label: 'Confirmar pedido' });

            this.pasos       = pasos;
            this.timings     = timings;
            this.timingRules = rounds;
        },

        sectionLabel: (type) => SECTION_LABELS[type] ?? type,

        /* ── Navegación ───────────────────────────────────────────────────────── */
        siguiente() {
            const p = this.pasoActualObj;
            if (!p) return;
            if (p.tipo === 'seleccion' && p.required && !(this.selections[p.sectionId]?.length > 0)) {
                this.intentoAvanzar = true;
                return;
            }
            this.intentoAvanzar = false;
            this.pasoActual = Math.min(this.pasoActual + 1, this.pasos.length - 1);
        },

        anterior() {
            this.intentoAvanzar = false;
            if (this.isFirstStep) { this.requestClose(); return; }
            this.pasoActual = Math.max(0, this.pasoActual - 1);
        },

        irAPaso(idx) {
            if (idx <= this.pasoActual) this.pasoActual = idx;
        },

        /* ── Selecciones ──────────────────────────────────────────────────────── */
        selectProduct(sectionId, productId, productName, maxQty) {
            const max     = maxQty ?? 1;
            const current = this.selections[sectionId] ?? [];
            const idx     = current.findIndex(s => s.product_id === productId);

            if (max === 1) {
                this.selections = {
                    ...this.selections,
                    [sectionId]: idx !== -1 ? [] : [{ product_id: productId, product_name: productName }],
                };
            } else {
                if (idx !== -1) {
                    this.selections = {
                        ...this.selections,
                        [sectionId]: current.filter(s => s.product_id !== productId),
                    };
                } else if (current.length < max) {
                    this.selections = {
                        ...this.selections,
                        [sectionId]: [...current, { product_id: productId, product_name: productName }],
                    };
                }
            }
        },

        isSelected(sectionId, productId) {
            return (this.selections[sectionId] ?? []).some(s => s.product_id === productId);
        },

        skipSection(sectionId) {
            this.selections = { ...this.selections, [sectionId]: [{ product_id: '__skipped__', product_name: '' }] };
            this.siguiente();
        },

        isSkipped(sectionId) {
            const arr = this.selections[sectionId] ?? [];
            return arr.length > 0 && arr[0].product_id === '__skipped__';
        },

        /* ── Timing ───────────────────────────────────────────────────────────── */
        setTiming(round, val) {
            this.timings = { ...this.timings, [round]: val };
        },

        /* ── Confirmación ─────────────────────────────────────────────────────── */
        confirmationSelections() {
            if (!this.menuData) return [];
            const result = [];
            (this.menuData.sections ?? []).forEach(s => {
                const arr     = (this.selections[s.id] ?? []).filter(x => x.product_id !== '__skipped__');
                const skipped = this.isSkipped(s.id);
                result.push({
                    section_label: this.sectionLabel(s.type),
                    products:      arr.map(x => x.product_name),
                    skipped,
                    empty:         !skipped && arr.length === 0,
                });
            });
            return result;
        },

        /* ── Submit ───────────────────────────────────────────────────────────── */
        async submitOrder() {
            this.enviando = true;
            this.errorMsg = null;
            try {
                const body = {
                    selections: Object.entries(this.selections)
                        .filter(([, v]) => v?.length > 0)
                        .flatMap(([sectionId, sels]) =>
                            sels
                                .filter(s => s.product_id !== '__skipped__')
                                .map(sel => ({
                                    section_id: parseInt(sectionId),
                                    product_id: sel.product_id,
                                    quantity:   1,
                                }))
                        ),
                    timing_overrides: (() => {
                        const firstRound = this.timingRules.length > 0 ? this.timingRules[0].round : null;
                        return Object.entries(this.timings)
                            .filter(([round]) => parseInt(round) !== firstRound)
                            .map(([round, delay]) => ({ round: parseInt(round), delay_minutes: delay }));
                    })(),
                };

                const res = await fetch('/api/v1/menu/' + this.hash + '/daily-menu/order', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept':       'application/json',
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
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {void}
 */
export function registerDailyMenuBanner() {
    Alpine.data('dailyMenuBanner', (hash) => dailyMenuBanner(hash));
}
