/**
 * @fileoverview Bill / payment Alpine store for the public menu. Handles cash,
 *   card (Stripe), split, and mixed payment flows.
 *   Registered as `Alpine.store('bill')` for global access.
 *   Used in resources/views/menu/show.blade.php.
 * @module bill
 * @author BenjaminDTS
 */

/**
 * Calculates the equitative share per person, rounded to 2 decimal places.
 *
 * @param {number} total  - Order total before splitting.
 * @param {number} people - Number of people sharing (minimum 2).
 * @returns {number} Amount per person.
 */
export function calculateEquitativeShare(total, people) {
    const p = Math.max(2, parseInt(people) || 2);
    return Math.round((total / p) * 100) / 100;
}

/**
 * Calculates tip amount from a percentage.
 *
 * @param {number} base    - Base amount to apply percentage to.
 * @param {number} percent - Percentage (e.g. 10 for 10%).
 * @returns {number} Tip amount rounded to 2 decimal places.
 */
export function calculateTipFromPercent(base, percent) {
    return Math.round(base * percent) / 100;
}

/**
 * Returns the total of all selected split items.
 *
 * @param {Array<{id: number, total: number}>} splitItems    - All split-able items.
 * @param {number[]}                           selectedIds   - IDs of selected items.
 * @returns {number}
 */
export function calculateSelectedTotal(splitItems, selectedIds) {
    return splitItems
        .filter(i => selectedIds.includes(i.id))
        .reduce((sum, i) => sum + i.total, 0);
}

/**
 * Registers the `bill` global Alpine store and the `chat` global store.
 * Reads initial state from `<script id="menu-context" type="application/json">`.
 *
 * @returns {void}
 */
export function registerBill() {
    const raw = document.getElementById('menu-context');
    const ctx = raw ? JSON.parse(raw.textContent) : {};

    Alpine.store('chat', { open: false });

    Alpine.store('bill', {
        active:      ctx.hasActiveOrder ?? false,
        requested:   ctx.billRequested  ?? false,
        sending:     false,
        error:       null,
        choosing:    false,
        method:      null,
        tableHash:          ctx.tableHash ?? '',
        ticketDownloadBase: '/ticket',

        /** Single step string — matches DS BillFlow states */
        step: '',

        /** Sub-flow stages */
        splitStage:  'intro',   // intro | items | equit | pay | done
        mixedStage:  'cash',    // cash | tip | pay | waiter | done

        showingTip:  false,
        tipAmount:   0,
        tipPercent:  null,
        orderTotal:         ctx.activeOrderTotal   ?? 0,
        originalOrderTotal: ctx.originalOrderTotal ?? 0,
        grandTotal:         ctx.activeOrderTotal   ?? 0,

        payingCard:   false,
        stripeReady:  false,
        stripeError:  null,
        stripeTotal:  0,
        paymentDone:  false,
        paidOrderId:  null,
        _stripe:      null,
        _elements:    null,

        splitEnabled:          ctx.splitPaymentEnabled  ?? false,
        splitMaxParts:         ctx.splitPaymentMaxParts ?? 2,
        splitEquitativeLocked: false,
        showingSplit:          false,
        splitMode:             null,
        splitShowItems:   false,
        splitShowEq:      false,
        splitItems:       [],
        splitSelected:    [],
        splitPeople:      2,
        splitEqSlice:     0,
        splitEqColor:     null,
        splitPayingCard:  false,
        splitStripeReady: false,
        splitStripeError: null,
        splitStripeTotal: 0,
        splitStripeTip:   0,
        _splitStripe:     null,
        _splitElements:   null,

        showingCashTip:  false,
        cashTipAmount:   0,
        cashTipPercent:  null,
        cashGrandTotal:  0,

        showingSplitTip:    false,
        splitTipAmount:     0,
        splitTipPercent:    null,
        splitTipBase:       0,
        splitTipType:       null,
        splitTipItemIds:    [],
        splitTipGrandTotal: 0,

        showingMixed:       false,
        mixedCashAmount:    0,
        mixedPayingCard:    false,
        mixedStripeReady:   false,
        mixedStripeError:   null,
        mixedStripeTotal:   0,
        mixedStripeTip:     0,
        mixedCashPending:   false,
        mixedCashPendingAmt: 0,
        _mixedStripe:       null,
        _mixedElements:     null,
        showingMixedTip:    false,
        mixedTipAmount:     0,
        mixedTipPercent:    null,
        mixedTipBase:       0,
        mixedTipGrandTotal: 0,

        open() {
            if (this.sending) return;
            this.error    = null;
            this.choosing = true;
            this.step     = 'method';
        },

        close() {
            this.choosing         = false;
            this.showingCashTip   = false;
            this.showingTip       = false;
            this.payingCard       = false;
            this.stripeReady      = false;
            this.showingSplit     = false;
            this.splitShowItems   = false;
            this.splitShowEq      = false;
            this.showingSplitTip  = false;
            this.showingMixed     = false;
            this.showingMixedTip  = false;
            this.mixedPayingCard  = false;
            this.step             = '';
        },

        backToMethod() {
            this.showingCashTip   = false;
            this.showingTip       = false;
            this.payingCard       = false;
            this.stripeReady      = false;
            this.showingSplit     = false;
            this.splitShowItems   = false;
            this.splitShowEq      = false;
            this.showingSplitTip  = false;
            this.showingMixed     = false;
            this.showingMixedTip  = false;
            this.mixedPayingCard  = false;
            this.choosing         = true;
            this.step             = 'method';
        },

        async requestCash() {
            if (this.requested || this.sending) return;
            this.choosing = false;
            this.sending  = true;
            this.error    = null;
            try {
                const res = await fetch('/api/v1/bill-request/' + this.tableHash, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ payment_method: 'cash', tip: this.cashTipAmount }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.requested      = true;
                    this.method         = 'cash';
                    this.showingCashTip = false;
                    this.step           = 'cashDone';
                } else {
                    if (res.status === 404) this.active = false;
                    this.error = data.message ?? 'Error al solicitar la cuenta.';
                }
            } catch {
                this.error = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
            }
        },

        async openCardPayment() {
            this.choosing = false;
            try {
                const res  = await fetch('/api/v1/payment/' + this.tableHash + '/total', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    this.active = false;
                    this.error  = data.message ?? 'No hay un pedido activo.';
                    return;
                }
                this.orderTotal = parseFloat(data.total) || 0;
                this.grandTotal = parseFloat(data.total) || 0;
            } catch {
                // use cached total from page load
            }
            this.tipPercent = 10;
            this.tipAmount  = calculateTipFromPercent(this.orderTotal, 10);
            this.grandTotal = this.orderTotal + this.tipAmount;
            this.showingTip = true;
            this.step       = 'tip';
        },

        setTipPercent(pct) {
            this.tipPercent = pct;
            this.tipAmount  = calculateTipFromPercent(this.orderTotal, pct);
            this.grandTotal = this.orderTotal + this.tipAmount;
        },

        updateCustomTip(value) {
            this.tipPercent = null;
            const parsed    = parseFloat(value) || 0;
            this.tipAmount  = Math.max(0, Math.round(parsed * 100) / 100);
            this.grandTotal = this.orderTotal + this.tipAmount;
        },

        closeTip() {
            this.showingTip = false;
            this.tipAmount  = 0;
            this.tipPercent = null;
            this.choosing   = true;
        },

        async proceedToStripe() {
            this.showingTip  = false;
            this.payingCard  = true;
            this.stripeReady = false;
            this.stripeError = null;
            this.sending     = true;
            this.step        = 'pay';
            try {
                const res = await fetch('/api/v1/payment/' + this.tableHash + '/intent', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ tip: this.tipAmount }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.stripeError = data.message ?? 'No se pudo iniciar el pago.';
                    this.payingCard  = false;
                    return;
                }
                this.stripeTotal = data.grand_total;
                this.grandTotal  = data.grand_total;
                requestAnimationFrame(() => requestAnimationFrame(() => this._mountStripe(data.client_secret)));
            } catch {
                this.stripeError = 'Error de conexión al iniciar el pago.';
                this.payingCard  = false;
            } finally {
                this.sending = false;
            }
        },

        _mountStripe(clientSecret) {
            const pk = document.querySelector('meta[name="stripe-key"]')?.content ?? '';
            if (!pk || !window.Stripe) {
                this.stripeError = 'Stripe no disponible. Recarga la página.';
                this.payingCard  = false;
                return;
            }
            try {
                this._stripe   = Stripe(pk);
                this._elements = this._stripe.elements({ clientSecret, locale: 'es' });
                const el = this._elements.create('payment');
                el.mount('#stripe-payment-element');
                el.on('ready', () => { this.stripeReady = true; });
            } catch {
                this.stripeError = 'Error al cargar el formulario de pago. Inténtalo de nuevo.';
                this.payingCard  = false;
            }
        },

        closeCardPayment() {
            this.payingCard  = false;
            this.stripeError = null;
            this._elements   = null;
            this._stripe     = null;
        },

        async submitCardPayment() {
            if (!this._stripe || !this._elements || this.sending) return;
            this.sending     = true;
            this.stripeError = null;
            try {
                const { error, paymentIntent } = await this._stripe.confirmPayment({
                    elements:      this._elements,
                    confirmParams: { return_url: window.location.href },
                    redirect:      'if_required',
                });
                if (error) { this.stripeError = error.message; return; }
                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    const res = await fetch('/api/v1/payment/' + this.tableHash + '/confirm', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({ payment_intent_id: paymentIntent.id, tip: this.tipAmount }),
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.payingCard  = false;
                        this.paymentDone = true;
                        this.paidOrderId = data.order_id ?? null;
                        this.requested   = true;
                        this.active      = false;
                        this.method      = 'card';
                        this.step        = 'cardDone';
                    } else {
                        this.stripeError = data.message ?? 'Error al confirmar el pago.';
                    }
                }
            } catch {
                this.stripeError = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
            }
        },

        async _loadSplitItems() {
            try {
                const res  = await fetch('/api/v1/payment/' + this.tableHash + '/split/items', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.splitItems            = data.items;
                    this.splitEquitativeLocked = data.equitative_locked ?? false;
                } else {
                    this.splitItems = [];
                }
            } catch {
                this.splitItems = [];
            }
            this.splitSelected = [];
        },

        isItemSelected(id) {
            return this.splitSelected.includes(id);
        },

        toggleSplitItem(id, claimed) {
            if (claimed) return;
            const idx = this.splitSelected.indexOf(id);
            if (idx === -1) this.splitSelected.push(id);
            else            this.splitSelected.splice(idx, 1);
        },

        get splitItemsTotal() {
            return calculateSelectedTotal(this.splitItems, this.splitSelected);
        },

        get splitMyPart() {
            return calculateEquitativeShare(this.originalOrderTotal, this.splitPeople);
        },

        openSplit() {
            if (this.requested || this.sending) return;
            this.choosing    = false;
            this.showingSplit = true;
            this.splitStage  = 'intro';
            this.step        = 'split';
        },

        setSplitStage(s) {
            this.splitStage = s;
            if (s === 'items') { this.step = 'splitItems'; this._loadSplitItems(); }
            else if (s === 'equit') { this.step = 'splitEq'; }
            else if (s === 'pay') { this.step = 'splitPay'; }
            else if (s === 'done') { this.step = 'splitDone'; }
            else { this.step = 'split'; }
        },

        closeSplitSelector() {
            this.showingSplit = false;
            this.step        = 'method';
            this.choosing    = true;
        },

        async openSplitItems() {
            this.showingSplit    = false;
            this.splitMode      = 'items';
            this.splitShowItems = true;
            this.step           = 'splitItems';
            await this._loadSplitItems();
        },

        closeSplitItems() {
            this.splitShowItems = false;
            this.splitMode      = null;
            this.splitSelected  = [];
            this.showingSplit   = true;
            this.splitStage     = 'intro';
            this.step           = 'split';
        },

        openSplitEq() {
            this.showingSplit = false;
            this.splitMode    = 'equitable';
            this.splitPeople  = 2;
            this.splitShowEq  = true;
            this.step         = 'splitEq';
        },

        closeSplitEq() {
            this.splitShowEq = false;
            this.splitMode   = null;
            this.showingSplit = true;
            this.splitStage  = 'intro';
            this.step        = 'split';
        },

        paySelectedItems() {
            if (!this.splitSelected || this.splitSelected.length === 0) return;
            this.openSplitTip(this.splitItemsTotal, 'items', [...this.splitSelected]);
        },

        payEquitative() {
            this.openSplitTip(this.splitMyPart, 'equit', []);
        },

        openCashTip() {
            this.choosing       = false;
            this.cashTipAmount  = 0;
            this.cashTipPercent = 10;
            this.cashGrandTotal = this.orderTotal + calculateTipFromPercent(this.orderTotal, 10);
            this.showingCashTip = true;
            this.step           = 'cashConfirm';
        },

        setCashTipPercent(pct) {
            this.cashTipPercent = pct;
            this.cashTipAmount  = calculateTipFromPercent(this.orderTotal, pct);
            this.cashGrandTotal = this.orderTotal + this.cashTipAmount;
        },

        updateCustomCashTip(value) {
            this.cashTipPercent = null;
            const parsed        = parseFloat(value) || 0;
            this.cashTipAmount  = Math.max(0, Math.round(parsed * 100) / 100);
            this.cashGrandTotal = this.orderTotal + this.cashTipAmount;
        },

        closeCashTip() {
            this.showingCashTip = false;
            this.cashTipAmount  = 0;
            this.cashTipPercent = null;
            this.choosing       = true;
        },

        async confirmCashPayment() {
            this.showingCashTip = false;
            await this.requestCash();
        },

        openSplitTip(amount, type, itemIds) {
            this.splitTipBase       = amount;
            this.splitTipType       = type;
            this.splitTipItemIds    = itemIds || [];
            this.splitTipAmount     = 0;
            this.splitTipPercent    = 10;
            this.splitTipGrandTotal = amount + calculateTipFromPercent(amount, 10);
            this.splitShowItems     = false;
            this.splitShowEq        = false;
            this.showingSplitTip    = true;
            this.step               = 'splitTip';
        },

        setSplitTipPercent(pct) {
            this.splitTipPercent    = pct;
            this.splitTipAmount     = calculateTipFromPercent(this.splitTipBase, pct);
            this.splitTipGrandTotal = this.splitTipBase + this.splitTipAmount;
        },

        updateCustomSplitTip(value) {
            this.splitTipPercent    = null;
            const parsed            = parseFloat(value) || 0;
            this.splitTipAmount     = Math.max(0, Math.round(parsed * 100) / 100);
            this.splitTipGrandTotal = this.splitTipBase + this.splitTipAmount;
        },

        closeSplitTip() {
            this.showingSplitTip = false;
            this.splitTipAmount  = 0;
            this.splitTipPercent = null;
            if (this.splitTipType === 'items') {
                this.splitShowItems = true;
            } else {
                this.splitShowEq = true;
            }
        },

        async confirmSplitTip() {
            this.showingSplitTip = false;
            await this.proceedSplitPayment(
                this.splitTipGrandTotal,
                this.splitTipType,
                this.splitTipItemIds,
                this.splitTipAmount,
            );
        },

        openMixed() {
            if (this.requested || this.sending) return;
            this.choosing        = false;
            this.mixedCashAmount = Math.round(this.orderTotal / 2 * 100) / 100;
            this.showingMixed    = true;
            this.mixedStage      = 'cash';
            this.mixedTipPercent = 0;
            this.mixedTipAmount  = 0;
            this.mixedTipGrandTotal = 0;
            this.step            = 'mixed';
        },

        setMixedStage(s) {
            this.mixedStage = s;
            if (s === 'cash') { this.step = 'mixed'; }
            else if (s === 'tip') {
                this.mixedTipBase = this.mixedCardAmount;
                this.mixedTipPercent = 0;
                this.mixedTipAmount = 0;
                this.mixedTipGrandTotal = this.mixedCardAmount;
                this.step = 'mixedTip';
            }
            else if (s === 'pay') { this.step = 'mixedPay'; this._startMixedPayment(); }
            else if (s === 'waiter') { this.step = 'mixedWaiter'; }
            else if (s === 'done') { this.step = 'mixedDone'; }
        },

        closeMixed() {
            this.showingMixed    = false;
            this.mixedCashAmount = 0;
            this.mixedStage      = 'cash';
            this.choosing        = true;
            this.step            = 'method';
        },

        get mixedCardAmount() {
            return Math.max(0, Math.round((this.orderTotal - this.mixedCashAmount) * 100) / 100);
        },

        get mixedCashValid() {
            return this.mixedCashAmount > 0
                && this.mixedCashAmount < this.orderTotal
                && this.mixedCardAmount >= 0.5;
        },

        proceedFromMixed() {
            if (this.mixedCashAmount >= this.orderTotal - 0.001) {
                this.showingMixed    = false;
                this.mixedCashAmount = 0;
                this.openCashTip();
            } else {
                this.openMixedTip();
            }
        },

        openMixedTip() {
            this.showingMixed       = false;
            this.mixedTipBase       = this.mixedCardAmount;
            this.mixedTipAmount     = 0;
            this.mixedTipPercent    = 10;
            this.mixedTipGrandTotal = this.mixedCardAmount + calculateTipFromPercent(this.mixedCardAmount, 10);
            this.showingMixedTip    = true;
            this.step               = 'mixedTip';
        },

        setMixedTipPercent(pct) {
            this.mixedTipPercent    = pct;
            this.mixedTipAmount     = calculateTipFromPercent(this.mixedTipBase, pct);
            this.mixedTipGrandTotal = this.mixedTipBase + this.mixedTipAmount;
        },

        updateCustomMixedTip(value) {
            this.mixedTipPercent    = null;
            const parsed            = parseFloat(value) || 0;
            this.mixedTipAmount     = Math.max(0, Math.round(parsed * 100) / 100);
            this.mixedTipGrandTotal = this.mixedTipBase + this.mixedTipAmount;
        },

        closeMixedTip() {
            this.showingMixedTip = false;
            this.mixedTipAmount  = 0;
            this.mixedTipPercent = null;
            this.showingMixed    = true;
            this.step            = 'mixed';
        },

        async confirmMixedTip() {
            this.showingMixedTip = false;
            await this._startMixedPayment();
        },

        async _startMixedPayment() {
            this.mixedPayingCard  = true;
            this.mixedStripeReady = false;
            this.mixedStripeError = null;
            this.sending          = true;
            this.step             = 'mixedPay';
            try {
                const res = await fetch('/api/v1/payment/' + this.tableHash + '/mixed/intent', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ cash_amount: this.mixedCashAmount, tip: this.mixedTipAmount }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.mixedStripeError = data.message ?? 'No se pudo iniciar el pago.';
                    this.mixedPayingCard  = false;
                    return;
                }
                this.mixedStripeTotal = data.grand_total ?? data.card_amount;
                this.mixedStripeTip   = this.mixedTipAmount;
                requestAnimationFrame(() => requestAnimationFrame(() => this._mountMixedStripe(data.client_secret)));
            } catch {
                this.mixedStripeError = 'Error de conexión al iniciar el pago.';
                this.mixedPayingCard  = false;
            } finally {
                this.sending = false;
            }
        },

        _mountMixedStripe(clientSecret) {
            const pk = document.querySelector('meta[name="stripe-key"]')?.content ?? '';
            if (!pk || !window.Stripe) {
                this.mixedStripeError = 'Stripe no disponible. Recarga la página.';
                this.mixedPayingCard  = false;
                return;
            }
            try {
                this._mixedStripe   = Stripe(pk);
                this._mixedElements = this._mixedStripe.elements({ clientSecret, locale: 'es' });
                const el = this._mixedElements.create('payment');
                el.mount('#mixed-stripe-element');
                el.on('ready', () => { this.mixedStripeReady = true; });
            } catch {
                this.mixedStripeError = 'Error al cargar el formulario de pago.';
                this.mixedPayingCard  = false;
            }
        },

        closeMixedPayment() {
            this.mixedPayingCard  = false;
            this.mixedStripeError = null;
            this._mixedStripe     = null;
            this._mixedElements   = null;
            this.showingMixed     = true;
            this.step             = 'mixedTip';
        },

        async submitMixedPayment() {
            if (!this._mixedStripe || !this._mixedElements || this.sending) return;
            this.sending          = true;
            this.mixedStripeError = null;
            try {
                const { error, paymentIntent } = await this._mixedStripe.confirmPayment({
                    elements:      this._mixedElements,
                    confirmParams: { return_url: window.location.href },
                    redirect:      'if_required',
                });
                if (error) { this.mixedStripeError = error.message; return; }
                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    const res = await fetch('/api/v1/payment/' + this.tableHash + '/mixed/confirm', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id,
                            cash_amount:       this.mixedCashAmount,
                            tip:               this.mixedStripeTip,
                        }),
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.mixedPayingCard     = false;
                        this._mixedStripe        = null;
                        this._mixedElements      = null;
                        this.mixedCashPending    = true;
                        this.mixedCashPendingAmt = data.cash_amount ?? this.mixedCashAmount;
                        this.requested           = true;
                        this.method              = 'mixed';
                        this.step                = 'mixedWaiter';
                    } else {
                        this.mixedStripeError = data.message ?? 'Error al confirmar el pago.';
                    }
                }
            } catch {
                this.mixedStripeError = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
            }
        },

        async proceedSplitPayment(amount, type, itemIds, tip = 0) {
            const ids = itemIds || [];
            this.splitShowItems   = false;
            this.splitShowEq      = false;
            this.splitPayingCard  = true;
            this.splitStripeReady = false;
            this.splitStripeError = null;
            this.sending          = true;
            this.step             = 'splitPay';
            try {
                const url  = type === 'items'
                    ? '/api/v1/payment/' + this.tableHash + '/split/pay-items'
                    : '/api/v1/payment/' + this.tableHash + '/split/pay-eq';
                const body = type === 'items'
                    ? { item_ids: ids, tip }
                    : { people: Math.max(2, parseInt(this.splitPeople) || 2), part_number: this.splitEqSlice + 1, session_color: this.splitEqColor, tip };
                const res = await fetch(url, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.splitStripeError = data.message ?? 'No se pudo iniciar el pago parcial.';
                    this.splitPayingCard  = false;
                    return;
                }
                this.splitStripeTotal = data.amount ?? amount;
                this.splitStripeTip   = tip;
                requestAnimationFrame(() => requestAnimationFrame(() => this._mountSplitStripe(data.client_secret)));
            } catch {
                this.splitStripeError = 'Error de conexión al iniciar el pago.';
                this.splitPayingCard  = false;
            } finally {
                this.sending = false;
            }
        },

        _mountSplitStripe(clientSecret) {
            const pk = document.querySelector('meta[name="stripe-key"]')?.content ?? '';
            if (!pk || !window.Stripe) {
                this.splitStripeError = 'Stripe no disponible. Recarga la página.';
                this.splitPayingCard  = false;
                return;
            }
            try {
                this._splitStripe   = Stripe(pk);
                this._splitElements = this._splitStripe.elements({ clientSecret, locale: 'es' });
                const el = this._splitElements.create('payment');
                el.mount('#split-stripe-element');
                el.on('ready', () => { this.splitStripeReady = true; });
            } catch {
                this.splitStripeError = 'Error al cargar el formulario de pago.';
                this.splitPayingCard  = false;
            }
        },

        closeSplitPayment() {
            this.splitPayingCard  = false;
            this.splitStripeError = null;
            this.splitMode        = null;
            this._splitElements   = null;
            this._splitStripe     = null;
            this.step             = 'method';
            this.choosing         = true;
        },

        payMixedCard() {
            return this.submitMixedPayment();
        },

        async submitSplitPayment() {
            if (!this._splitStripe || !this._splitElements || this.sending) return;
            this.sending          = true;
            this.splitStripeError = null;
            try {
                const { error, paymentIntent } = await this._splitStripe.confirmPayment({
                    elements:      this._splitElements,
                    confirmParams: { return_url: window.location.href },
                    redirect:      'if_required',
                });
                if (error) { this.splitStripeError = error.message; return; }
                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    const res = await fetch('/api/v1/payment/' + this.tableHash + '/split/confirm', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({ payment_intent_id: paymentIntent.id, type: this.splitMode }),
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        const basePaid  = this.splitStripeTotal - (this.splitStripeTip || 0);
                        this.orderTotal = Math.max(0, this.orderTotal - basePaid);
                        this.grandTotal = this.orderTotal;
                        if (data.fully_paid) {
                            this.splitPayingCard = false;
                            this.paymentDone     = true;
                            this.paidOrderId     = data.order_id ?? null;
                            this.requested       = true;
                            this.active          = false;
                            this.step            = 'splitDone';
                        } else {
                            this._splitStripe    = null;
                            this._splitElements  = null;
                            this.splitPayingCard = false;
                            await this._loadSplitItems();
                            this.splitShowItems  = true;
                            this.step            = 'splitItems';
                        }
                    } else {
                        this.splitStripeError = data.message ?? 'Error al confirmar el pago.';
                    }
                }
            } catch {
                this.splitStripeError = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
            }
        },
    });
}
