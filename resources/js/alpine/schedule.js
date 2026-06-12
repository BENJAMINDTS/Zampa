/**
 * Alpine.store('schedule') — estado reactivo de horarios (cocina y negocio).
 *
 * Se inicializa desde el JSON `menu-context` que el servidor inyecta en la página
 * y se sincroniza cada 30 s mediante polling al endpoint /api/v1/carta/{hash}/schedule.
 * Además programa un setTimeout preciso para cada hora de cierre conocida, de forma que
 * los ítems se bloquean al segundo exacto sin necesidad de recargar la página.
 *
 * @author Ayrton
 */

function readMenuContext() {
    const raw = document.getElementById('menu-context');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Milisegundos hasta la hora "HH:MM".
 * Si ya pasó hoy, apunta al mismo slot de mañana (horarios de madrugada).
 *
 * @param {string} timeStr  — "HH:MM"
 * @returns {number|null}
 */
function msUntilTimeStr(timeStr) {
    if (!timeStr) return null;
    const [h, m] = timeStr.split(':').map(Number);
    const now    = new Date();
    const target = new Date(now);
    target.setHours(h, m, 0, 0);
    if (target <= now) target.setDate(target.getDate() + 1);
    return target - now;
}

export function registerSchedule() {
    Alpine.store('schedule', {
        kitchenOpen:              true,
        kitchenNextOpening:       null,
        minutesUntilKitchenClose: null,
        kitchenCloseAt:           null,
        businessOpen:             true,
        businessNextOpening:      null,
        businessCloseAt:          null,
        orderingAllowed:          true,
        bizDismissed:             false,
        _hash:              '',
        _kitchenCloseTimer: null,
        _bizCloseTimer:     null,

        init() {
            const ctx = readMenuContext();
            this.kitchenOpen              = ctx.kitchenOpen              ?? true;
            this.kitchenNextOpening       = ctx.kitchenNextOpening       ?? null;
            this.minutesUntilKitchenClose = ctx.minutesUntilKitchenClose ?? null;
            this.kitchenCloseAt           = ctx.kitchenCloseAt           ?? null;
            this.businessOpen             = ctx.businessOpen             ?? true;
            this.businessNextOpening      = ctx.businessNextOpening      ?? null;
            this.businessCloseAt          = ctx.businessCloseAt          ?? null;
            this.orderingAllowed          = ctx.orderingAllowed          ?? true;
            this._hash                    = ctx.tableHash                ?? '';

            this._scheduleCloseTimers();

            if (this._hash) {
                setInterval(() => this._poll(), 30_000);
            }
        },

        /**
         * Programa setTimeout para cada hora de cierre conocida.
         * Se cancela y reprograma cada vez que llegan datos nuevos del servidor.
         */
        _scheduleCloseTimers() {
            // ── Cocina ────────────────────────────────────────────────────────
            clearTimeout(this._kitchenCloseTimer);
            this._kitchenCloseTimer = null;

            if (this.kitchenOpen && this.kitchenCloseAt) {
                const ms = msUntilTimeStr(this.kitchenCloseAt);
                if (ms !== null && ms > 0) {
                    this._kitchenCloseTimer = setTimeout(() => {
                        this.kitchenOpen              = false;
                        this.kitchenCloseAt           = null;
                        this.minutesUntilKitchenClose = null;
                        this._kitchenCloseTimer       = null;
                    }, ms);
                }
            }

            // ── Negocio ───────────────────────────────────────────────────────
            clearTimeout(this._bizCloseTimer);
            this._bizCloseTimer = null;

            if (this.businessOpen && this.businessCloseAt) {
                const ms = msUntilTimeStr(this.businessCloseAt);
                if (ms !== null && ms > 0) {
                    this._bizCloseTimer = setTimeout(() => {
                        this.businessOpen    = false;
                        this.businessCloseAt = null;
                        this.orderingAllowed = false;
                        this._bizCloseTimer  = null;
                    }, ms);
                }
            }
        },

        /**
         * Consulta el endpoint de estado y actualiza las propiedades reactivas.
         * Reprograma los timers por si el horario cambió desde el servidor.
         *
         * @returns {Promise<void>}
         */
        async _poll() {
            try {
                const res = await fetch(`/api/v1/carta/${this._hash}/schedule`, {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;
                this.kitchenOpen              = data.kitchen_open;
                this.kitchenNextOpening       = data.kitchen_next_opening;
                this.minutesUntilKitchenClose = data.minutes_until_kitchen_close;
                this.kitchenCloseAt           = data.kitchen_close_at;
                this.businessOpen             = data.business_open;
                this.businessNextOpening      = data.business_next_opening;
                this.businessCloseAt          = data.business_close_at ?? null;
                this.orderingAllowed          = data.ordering_allowed;
                this._scheduleCloseTimers();
            } catch {
                // Error de red — se reintentará en el próximo ciclo de 30 s
            }
        },
    });
}
