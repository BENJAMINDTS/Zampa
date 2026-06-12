/**
 * Alpine.store('schedule') — estado reactivo de horarios (cocina y negocio).
 *
 * Se inicializa desde el JSON `menu-context` que el servidor inyecta en la página
 * y se sincroniza cada 30 s mediante polling al endpoint /api/v1/carta/{hash}/schedule.
 * Todos los indicadores de estado en la carta digital leen de este store,
 * por lo que cambian sin recargar la página cuando la cocina abre o cierra.
 *
 * @author Ayrton
 */

function readMenuContext() {
    const raw = document.getElementById('menu-context');
    return raw ? JSON.parse(raw.textContent) : {};
}

export function registerSchedule() {
    Alpine.store('schedule', {
        kitchenOpen:              true,
        kitchenNextOpening:       null,
        minutesUntilKitchenClose: null,
        kitchenCloseAt:           null,
        businessOpen:             true,
        businessNextOpening:      null,
        orderingAllowed:          true,
        bizDismissed:             false,
        _hash: '',

        init() {
            const ctx             = readMenuContext();
            this.kitchenOpen              = ctx.kitchenOpen              ?? true;
            this.kitchenNextOpening       = ctx.kitchenNextOpening       ?? null;
            this.minutesUntilKitchenClose = ctx.minutesUntilKitchenClose ?? null;
            this.kitchenCloseAt           = ctx.kitchenCloseAt           ?? null;
            this.businessOpen             = ctx.businessOpen             ?? true;
            this.businessNextOpening      = ctx.businessNextOpening      ?? null;
            this.orderingAllowed          = ctx.orderingAllowed          ?? true;
            this._hash                    = ctx.tableHash                ?? '';

            if (this._hash) {
                setInterval(() => this._poll(), 30_000);
            }
        },

        /**
         * Consulta el endpoint de estado y actualiza las propiedades reactivas.
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
                this.orderingAllowed          = data.ordering_allowed;
            } catch {
                // Error de red — se reintentará en el próximo ciclo de 30 s
            }
        },
    });
}
