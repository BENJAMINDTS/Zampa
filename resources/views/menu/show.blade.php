<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta — {{ $table->user->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="stripe-key" content="{{ $stripePublicKey }}">
    <script src="https://js.stripe.com/v3/" defer></script>
    <style>
        /* ── Zampi Chatbot Animations ────────────────────────────── */
        @keyframes zampiSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes zampiFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-5px); }
        }
        @keyframes zampiPulse {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }
        @keyframes zampiTyping {
            0%, 80%, 100% { opacity: 1; }
            40%           { opacity: 0.2; }
        }

        .zampi-float    { animation: zampiFloat 3s ease-in-out infinite; }
        .zampi-pulse    { animation: zampiPulse 2s ease-in-out infinite; }
        .zampi-msg-appear { animation: zampiSlideUp 0.3s ease; }
        .zampi-typing-1 { animation: zampiTyping 1.2s ease-in-out 0.0s infinite; }
        .zampi-typing-2 { animation: zampiTyping 1.2s ease-in-out 0.2s infinite; }
        .zampi-typing-3 { animation: zampiTyping 1.2s ease-in-out 0.4s infinite; }

        /* Notificaciones del cart bar — animación directa via :class */
        @keyframes zampiNotifIn {
            0%   { opacity: 0; transform: translateY(14px) scale(0.70); }
            65%  { opacity: 1; transform: translateY(-3px)  scale(1.06); }
            100% { opacity: 1; transform: translateY(0)     scale(1);    }
        }
        @keyframes zampiNotifOut {
            0%   { opacity: 1; transform: translateY(0)    scale(1);   }
            100% { opacity: 0; transform: translateY(8px)  scale(0.8); }
        }
        .zampi-notif-pill     { display:flex; align-items:center; gap:5px; background:#991b1b;
                                border:1px solid #ef4444; border-radius:9999px;
                                padding:4px 10px; flex-shrink:0; }
        .zampi-notif-pill-in  { animation: zampiNotifIn  0.40s cubic-bezier(0.34,1.56,0.64,1) both; }
        .zampi-notif-pill-out { animation: zampiNotifOut 0.22s ease-in both; }
        @media (max-width: 640px) {
            .zampi-cartbar-row { padding-top: 20px !important; padding-bottom: 20px !important; }
            .zampi-hidden-mobile { display: none !important; }
            .zampi-notif-bar-active { flex: 1 !important; min-width: 0; overflow: visible; }
            .zampi-notif-bar .zampi-notif-pill:not(:last-child) { display: none !important; }
            .zampi-notif-bar-active .zampi-notif-pill { width: 100%; min-width: 0; display: flex; align-items: center; gap: 5px; overflow: hidden; }
            .zampi-notif-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .zampi-notif-suffix { flex-shrink: 0; white-space: nowrap; }
        }

        .zampi-bubble-user {
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            color: #fff;
            border-radius: 18px 18px 4px 18px;
            box-shadow: 0 4px 16px rgba(15,31,88,0.55);
            max-width: 78%;
            padding: 10px 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        .zampi-bubble-bot {
            background: #0E1A38;
            border: 1px solid rgba(46,80,176,0.35);
            color: #C8D8FF;
            border-radius: 18px 18px 18px 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
            max-width: 75%;
            padding: 10px 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        .zampi-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1A3380, #3070E8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .zampi-product-card {
            background: #0E1A38;
            border: 1px solid rgba(46,80,176,0.45);
            border-radius: 16px;
            padding: 10px;
            min-width: 140px;
            max-width: 160px;
            flex-shrink: 0;
            box-shadow: 0 4px 20px rgba(15,31,88,0.4);
            cursor: pointer;
            transition: transform 200ms ease;
        }
        .zampi-card-img {
            height: 56px;
            border-radius: 10px;
            background: linear-gradient(135deg, #162648, #0A1430);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
            font-size: 28px;
        }
        .zampi-add-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            border: none;
            color: #fff;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 8px rgba(46,80,176,0.6);
            transition: transform 150ms cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
        }
        .zampi-add-btn:hover  { transform: scale(1.1); }
        .zampi-add-btn:active { transform: scale(0.92); }
        .zampi-qr-btn {
            background: rgba(46,80,176,0.22);
            color: #8FA8E8;
            border: 1px solid rgba(46,80,176,0.5);
            border-radius: 9999px;
            padding: 5px 12px;
            font-size: 12px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 150ms ease;
            white-space: nowrap;
        }
        .zampi-qr-btn:hover  { background: #1A3380; color: #fff; }
        .zampi-order-summary {
            background: rgba(14,26,56,0.9);
            border: 1px solid rgba(46,80,176,0.45);
            border-radius: 16px;
            padding: 12px;
            margin-top: 8px;
            backdrop-filter: blur(12px);
        }
        .zampi-send-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            color: #fff;
            font-size: 20px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 14px rgba(46,80,176,0.65);
            flex-shrink: 0;
            transition: transform 150ms cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .zampi-send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .zampi-scrollbar::-webkit-scrollbar       { width: 4px; }
        .zampi-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .zampi-scrollbar::-webkit-scrollbar-thumb { background: rgba(46,80,176,0.5); border-radius: 4px; }
        .zampi-chat-input::placeholder            { color: rgba(84,120,208,0.7); }

        /* ── Zampi Chat — Layout ────────────────────────────────────── */

        /* El overlay cubre siempre toda la pantalla, sin importar el tamaño */
        .zampi-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(1,4,14,0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .zampi-panel {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
    </style>

    @php
        // Datos de productos para Alpine. Se calculan aquí para evitar que
        // @json() reciba una expresión multi-línea con corchetes anidados,
        // lo que confunde al parser de Blade.
        $productsForAlpine = $categories->flatMap(function ($category) {
            return $category->products->map(fn ($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,
                'categoryId'  => $category->id,
                'destination' => $category->destination,
                'allergenTypes' => $p->ingredients->where('is_allergen', true)
                    ->map(fn ($i) => $i->allergen_type ?? 'name:'.$i->name)
                    ->unique()->values()->toArray(),
                'removable'   => $p->ingredients
                    ->filter(fn ($i) => $i->pivot->is_removable)
                    ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])
                    ->values(),
                'extras'      => $p->ingredients
                    ->filter(fn ($i) => $i->pivot->is_extra)
                    ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'price' => (float) $i->pivot->extra_price])
                    ->values(),
            ]);
        })->values();

        // El precio de cada tapa se resuelve en backend (getPriceForProduct) para que
        // Alpine no tenga que replicar la lógica de modalidad de precio.
        $tapaProductsForAlpine = $tapaProducts->map(fn ($p) => [
            'id'    => $p->id,
            'name'  => $p->name,
            'price' => $tapaConfig ? (float) $tapaConfig->getPriceForProduct($p) : (float) $p->price,
        ])->values();

        $tapaConfigForAlpine = [
            'enabled'       => $tapaConfig?->tapas_enabled ?? false,
            'free'          => $tapaConfig?->tapas_free ?? true,
            'tapaPrice'     => (float) ($tapaConfig?->tapa_price ?? 0),
            'extraEnabled'  => $tapaConfig?->extra_tapa_enabled ?? false,
            'extraPrice'    => (float) ($tapaConfig?->extra_tapa_price ?? 0),
            'maxVariants'   => $tapaConfig?->max_tapa_variants ?? 0,
            'shouldSuggest' => $shouldSuggest,
            'variantsUsed'  => $tapaVariantsUsed,
            'barItemsCount' => (int) $barItemsCount,
            'kitchenOpen'   => $kitchenOpen,
        ];
    @endphp

    {{-- Los datos se inyectan en un <script> separado para evitar conflictos
         de escapado al pasar JSON como argumento en x-data. --}}
    <script id="menu-products" type="application/json">@json($productsForAlpine)</script>
    <script id="tapa-config" type="application/json">@json($tapaConfigForAlpine)</script>
    <script id="tapa-products" type="application/json">@json($tapaProductsForAlpine)</script>

    <script>
        document.addEventListener('alpine:init', () => {
            const raw  = document.getElementById('menu-products');
            const list = raw ? JSON.parse(raw.textContent) : [];

            const rawTapa      = document.getElementById('tapa-config');
            const tapaConfig   = rawTapa ? JSON.parse(rawTapa.textContent) : {};
            const rawTapaProd  = document.getElementById('tapa-products');
            const tapaProdList = rawTapaProd ? JSON.parse(rawTapaProd.textContent) : [];

            // ── Carrito global ──────────────────────────────────────────
            Alpine.store('cart', {
                items:   [],
                open:    false,
                sending: false,
                sent:    false,
                error:   null,
                tableHash: '{{ $table->unique_hash }}',

                showTapaModal:  false,
                tapaConfig:     tapaConfig,
                tapaProducts:   tapaProdList,
                _barItemsCount: tapaConfig.barItemsCount ?? 0,
                _variantsUsed:  tapaConfig.variantsUsed  ?? 0,

                add(product) {
                    const existing = this.items.find(i => i.productId === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.items.push({
                            productId:  product.id,
                            name:       product.name,
                            price:      product.price,
                            quantity:   1,
                            mods:       [],
                            removable:  product.removable || [],
                            extras:     product.extras    || [],
                        });
                    }
                    if (product.destination === 'bar') {
                        this._barItemsCount++;
                        this._checkTapaSuggestion();
                    }
                },

                _checkTapaSuggestion() {
                    const cfg = this.tapaConfig;
                    if (!cfg.enabled)                             return;
                    if (!cfg.kitchenOpen)                         return;
                    if (this._barItemsCount <= 0)                 return;
                    if (this._variantsUsed >= cfg.maxVariants)    return;
                    if (this.tapaProducts.length === 0)           return;
                    this.showTapaModal = true;
                },

                closeTapaModal() {
                    this.showTapaModal = false;
                },

                addTapa(tapaProduct) {
                    // tapaProduct.price ya viene resuelto por getPriceForProduct() en el backend
                    this.add({
                        id:          tapaProduct.id,
                        name:        tapaProduct.name,
                        price:       tapaProduct.price,
                        destination: 'kitchen',
                        removable:   [],
                        extras:      [],
                    });
                    this._variantsUsed++;
                    this.showTapaModal = false;
                },

                inc(productId) {
                    const item = this.items.find(i => i.productId === productId);
                    if (item) item.quantity++;
                },

                dec(productId) {
                    const idx = this.items.findIndex(i => i.productId === productId);
                    if (idx === -1) return;
                    this.items[idx].quantity--;
                    if (this.items[idx].quantity <= 0) this.items.splice(idx, 1);
                },

                toggleRemove(productId, ing) {
                    const item = this.items.find(i => i.productId === productId);
                    if (!item) return;
                    const i = item.mods.findIndex(m => m.ingredientId === ing.id && m.action === 'remove');
                    if (i === -1) item.mods.push({ ingredientId: ing.id, name: ing.name, action: 'remove', amountCharged: 0 });
                    else          item.mods.splice(i, 1);
                },

                toggleExtra(productId, ing) {
                    const item = this.items.find(i => i.productId === productId);
                    if (!item) return;
                    const i = item.mods.findIndex(m => m.ingredientId === ing.id && m.action === 'add');
                    if (i === -1) item.mods.push({ ingredientId: ing.id, name: ing.name, action: 'add', amountCharged: ing.price });
                    else          item.mods.splice(i, 1);
                },

                hasMod(productId, ingId, action) {
                    const item = this.items.find(i => i.productId === productId);
                    return item ? item.mods.some(m => m.ingredientId === ingId && m.action === action) : false;
                },

                lineTotal(item) {
                    const extra = item.mods.filter(m => m.action === 'add').reduce((s, m) => s + m.amountCharged, 0);
                    return (item.price + extra) * item.quantity;
                },

                get total() {
                    return this.items.reduce((s, item) => s + this.lineTotal(item), 0);
                },

                get count() {
                    return this.items.reduce((s, i) => s + i.quantity, 0);
                },

                fmt(n) {
                    return n.toFixed(2).replace('.', ',') + ' €';
                },

                async send() {
                    if (!this.items.length || this.sending) return;
                    this.sending = true;
                    this.error   = null;
                    try {
                        const res = await fetch('/api/v1/orders', {
                            method:  'POST',
                            headers: {
                                'Content-Type':  'application/json',
                                'Accept':        'application/json',
                                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                            body: JSON.stringify({
                                table_hash: this.tableHash,
                                items: this.items.map(item => ({
                                    product_id:    item.productId,
                                    quantity:      item.quantity,
                                    modifications: item.mods.map(m => ({
                                        ingredient_id:  m.ingredientId,
                                        action:         m.action,
                                        amount_charged: m.amountCharged,
                                    })),
                                })),
                            }),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            Alpine.store('bill').active = true;
                            this.sent  = true;
                            this.items = [];
                            this.open  = false;
                        } else {
                            this.error = data.message ?? 'Error al enviar el pedido.';
                        }
                    } catch {
                        this.error = 'Error de conexión. Inténtalo de nuevo.';
                    } finally {
                        this.sending = false;
                    }
                },
            });

            Alpine.data('menuFilters', () => ({
                products: list,
                activeAllergens: [],
                activeDestination: null,
                activeCategory: null,    // ID de categoría seleccionada, null = todas

                /**
                 * Activa o desactiva la exclusión de un alérgeno.
                 * "Sin X" = ocultar productos que CONTENGAN el alérgeno X.
                 */
                toggleAllergen(id) {
                    const idx = this.activeAllergens.indexOf(id);
                    if (idx === -1) {
                        this.activeAllergens.push(id);
                    } else {
                        this.activeAllergens.splice(idx, 1);
                    }
                },

                /**
                 * Activa/desactiva el filtro de destino (toggle).
                 */
                setDestination(dest) {
                    this.activeDestination = this.activeDestination === dest ? null : dest;
                    if (this.activeCategory !== null && !this.isCategoryVisible(this.activeCategory)) {
                        this.activeCategory = null;
                    }
                    this.activeAllergens = this.activeAllergens.filter(a => this.visibleAllergenKeys.includes(a));
                },

                /**
                 * Filtra por categoría. Al pulsar la misma dos veces se desactiva (toggle).
                 * Muestra ÚNICAMENTE los productos de esa categoría.
                 */
                setCategory(id) {
                    this.activeCategory = this.activeCategory === id ? null : id;
                },

                /**
                 * Un producto es visible si pasa los tres filtros:
                 *  - Su categoría coincide con activeCategory (si hay alguna activa).
                 *  - Su destino coincide con activeDestination (si hay alguno activo).
                 *  - No contiene ninguno de los alérgenos excluidos.
                 */
                isProductVisible(productId) {
                    const p = this.products.find(item => item.id === productId);
                    if (!p) return true;
                    if (this.activeCategory !== null && p.categoryId !== this.activeCategory) return false;
                    if (this.activeDestination !== null && p.destination !== this.activeDestination) return false;
                    if (this.activeAllergens.some(type => p.allergenTypes.includes(type))) return false;
                    return true;
                },

                /**
                 * Una sección de categoría es visible si:
                 *  - No hay filtro de categoría activo, o es la categoría seleccionada.
                 *  - Al menos uno de sus productos pasa los demás filtros.
                 */
                isCategoryVisible(categoryId) {
                    if (this.activeCategory !== null && this.activeCategory !== categoryId) return false;
                    const items = this.products.filter(p => p.categoryId === categoryId);
                    if (items.length === 0) return true;
                    return items.some(p => this.isProductVisible(p.id));
                },

                get visibleAllergenKeys() {
                    return [...new Set(
                        this.products
                            .filter(p => this.activeDestination === null || p.destination === this.activeDestination)
                            .flatMap(p => p.allergenTypes)
                    )];
                },

                get visibleCount() {
                    return this.products.filter(p => this.isProductVisible(p.id)).length;
                },

                get hasActiveFilters() {
                    return this.activeAllergens.length > 0
                        || this.activeDestination !== null
                        || this.activeCategory !== null;
                },

                clearAll() {
                    this.activeAllergens = [];
                    this.activeDestination = null;
                    this.activeCategory = null;
                },
            }));

            // ── Estado global del chat ───────────────────────────────────
            Alpine.store('chat', { open: false });

            // ── Solicitud de cuenta ──────────────────────────────────────
            Alpine.store('bill', {
                active:      @json($hasActiveOrder),
                requested:   @json($billRequested),
                sending:     false,
                error:       null,
                choosing:    false,
                method:      null,
                tableHash:   '{{ $table->unique_hash }}',

                // Estado de la pantalla de propina
                showingTip:  false,
                tipAmount:   0,
                tipPercent:  null,
                orderTotal:  @json((float) $activeOrderTotal),
                grandTotal:  @json((float) $activeOrderTotal),

                // Estado de pago con tarjeta
                payingCard:   false,
                stripeReady:  false,
                stripeError:  null,
                stripeTotal:  0,
                paymentDone:  false,
                _stripe:      null,
                _elements:    null,

                open() {
                    if (this.requested || this.sending) return;
                    this.error    = null;
                    this.choosing = true;
                },

                close() {
                    this.choosing = false;
                },

                // Pago en efectivo: envía la solicitud de cuenta al camarero
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
                            body: JSON.stringify({ payment_method: 'cash' }),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.requested = true;
                            this.method    = 'cash';
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

                // Paso 1 — Pago con tarjeta: obtiene el total actual y abre la pantalla de propina
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
                        this.orderTotal = data.total;
                        this.grandTotal = data.total;
                    } catch {
                        // si falla el fetch se usa el total cacheado en page load
                    }
                    this.tipAmount  = 0;
                    this.tipPercent = null;
                    this.showingTip = true;
                },

                // Selecciona un porcentaje de propina predefinido
                setTipPercent(pct) {
                    this.tipPercent = pct;
                    this.tipAmount  = Math.round(this.orderTotal * pct) / 100;
                    this.grandTotal = this.orderTotal + this.tipAmount;
                },

                // Actualiza la propina desde el input personalizado
                updateCustomTip(value) {
                    this.tipPercent = null;
                    const parsed    = parseFloat(value) || 0;
                    this.tipAmount  = Math.max(0, Math.round(parsed * 100) / 100);
                    this.grandTotal = this.orderTotal + this.tipAmount;
                },

                // Cierra la pantalla de propina y vuelve a elegir método de pago
                closeTip() {
                    this.showingTip = false;
                    this.tipAmount  = 0;
                    this.tipPercent = null;
                    this.choosing   = true;
                },

                // Paso 2 — Crea el PaymentIntent con la propina y abre Stripe Elements
                async proceedToStripe() {
                    this.showingTip  = false;
                    this.payingCard  = true;
                    this.stripeReady = false;
                    this.stripeError = null;
                    this.sending     = true;
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
                    } catch (e) {
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
                        if (error) {
                            this.stripeError = error.message;
                            return;
                        }
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
                                this.requested   = true;
                                this.active      = false;
                                this.method      = 'card';
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
            });

            // ── Widget de chat IA (Zampi Design System) ──────────────────
            Alpine.data('chatWidget', () => ({
                open:           false,
                conversationId: null,
                messages:       [],
                input:          '',
                sending:        false,
                isTyping:       false,
                closed:         false,
                error:          null,
                menuData:       null,
                msgSeq:         0,
                _now:           Date.now(),
                _ticker:        null,
                cartNotifs:     [],
                cartNotifSeq:   0,

                /* Vista unificada del carrito — lee del store global */
                get chatCart() {
                    return Alpine.store('cart').items.map(i => ({
                        id:    i.productId,
                        name:  i.name,
                        price: i.price,
                        qty:   i.quantity,
                    }));
                },

                init() {
                    this._ticker = setInterval(() => { this._now = Date.now(); }, 30000);

                    // Scroll al fondo cada vez que la barra del carrito aparece o desaparece
                    let _prevCartVisible = false;
                    this.$watch(() => Alpine.store('cart').count, (count) => {
                        const visible = count > 0;
                        if (visible !== _prevCartVisible) {
                            _prevCartVisible = visible;
                            this.scrollBottom();
                        }
                    });
                },
                destroy() {
                    clearInterval(this._ticker);
                },

                formatTime(ts) {
                    if (!ts) return '';
                    const diff = this._now - ts;
                    if (diff < 60000) return 'Ahora';
                    const d = new Date(ts);
                    return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
                },

                get tableHash() { return Alpine.store('cart').tableHash; },
                get cartCount()  { return Alpine.store('cart').count; },
                get cartTotal()  { return Alpine.store('cart').total; },
                get cartTotalStr() {
                    return '$' + this.cartTotal.toFixed(2).replace('.', ',');
                },

                /* ── Apertura / cierre ── */
                async openChat() {
                    this.open = true;
                    Alpine.store('chat').open = true;
                    document.body.style.overflow = 'hidden';
                    this.$nextTick(() => {
                        this.$refs.chatInput?.focus();
                        this.scrollBottom();
                    });
                    if (!this.menuData) await this.loadMenu();
                    if (!this.conversationId) await this.initConversation();
                },

                closeChat() {
                    this.open = false;
                    Alpine.store('chat').open = false;
                    document.body.style.overflow = '';
                },

                /* ── Carga del menú desde la API ── */
                async loadMenu() {
                    try {
                        const res = await fetch('/api/v1/menu/' + this.tableHash, {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success) this.menuData = data.data;
                        }
                    } catch { /* falla silenciosamente */ }
                },

                /* ── Inicio de conversación ── */
                async initConversation() {
                    const cats = this.menuData?.categories ?? [];
                    const qrs  = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);

                    this.pushMsg({ type: 'system',
                        text: (this.menuData?.table ?? 'Mesa') + ' · ' + (this.menuData?.restaurant ?? '') });
                    this.pushMsg({ type: 'bot',
                        text: '¡Hola! Soy Zampi, tu asistente de pedidos 🍔 ¿Qué te apetece hoy?',
                        quickReplies: qrs.length ? [...qrs, 'Ver mi pedido'] : ['Ver mi pedido'] });

                    /* Inicia conversación IA en segundo plano */
                    try {
                        const res = await fetch('/api/v1/chat/' + this.tableHash + '/start', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                        });
                        const data = await res.json();
                        if (res.ok && data.success) this.conversationId = data.data.conversation_id;
                    } catch { /* silent */ }
                },

                /* ── Mensajes ── */
                pushMsg(msg) {
                    this.msgSeq++;
                    this.messages.push({ _id: this.msgSeq, ...msg });
                    this.$nextTick(() => this.scrollBottom());
                },

                async botDelay(text, extra = {}, ms = 880) {
                    this.isTyping = true;
                    await new Promise(r => setTimeout(r, ms + Math.random() * 350));
                    this.isTyping = false;
                    this.pushMsg({ type: 'bot', text, ...extra });
                },

                /* ── Quick replies ── */
                handleQuickReply(label) {
                    if (this.isTyping || this.sending) return;
                    this.pushMsg({ type: 'user', text: label, time: Date.now() });

                    const cats    = this.menuData?.categories ?? [];
                    const matched = cats.find(c => label === this.getCategoryEmoji(c.name) + ' ' + c.name || c.name === label);

                    if (matched) {
                        this.showCategoryCards(matched);
                    } else if (label === 'Ver mi pedido') {
                        this.showCartSummary();
                    } else if (label === 'Confirmar pedido') {
                        Alpine.store('cart').open = true;
                    } else if (label === 'Seguir eligiendo') {
                        const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                        this.botDelay('¿Qué más te gustaría pedir?',
                            { quickReplies: [...qrs, 'Ver mi pedido'] });
                    } else if (label === '📋 Nuevo pedido') {
                        Alpine.store('cart').items = [];
                        const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                        this.botDelay('¡Claro! ¿Qué te gustaría pedir?',
                            { quickReplies: [...qrs, 'Ver mi pedido'] });
                    } else {
                        this.sendToAI(label);
                    }
                },

                /* ── Tarjetas de categoría ── */
                showCategoryCards(category) {
                    const emoji = this.getCategoryEmoji(category.name);
                    const cards = category.products.map(p => ({
                        id:    p.id,
                        name:  p.name,
                        desc:  p.description || '',
                        price: p.price,
                        emoji,
                    }));
                    this.botDelay(
                        'Aquí tienes nuestra selección de ' + category.name + ' 😋',
                        { cards, quickReplies: ['Ver mi pedido', 'Confirmar pedido'] }
                    );
                },

                /* ── Carrito del chat ── */
                /* ── Gestión del carrito del chat ── */
                cartQty(id) {
                    return Alpine.store('cart').items.find(i => i.productId === id)?.quantity ?? 0;
                },

                addToCart(card) {
                    const id = card.id ?? card.productId;
                    let destination = 'kitchen';
                    const cat = (this.menuData?.categories ?? []).find(c => c.products.some(p => p.id === id));
                    if (cat) destination = cat.destination;
                    Alpine.store('cart').add({ id, name: card.name, price: card.price, destination, removable: [], extras: [] });
                },

                decreaseQty(card) {
                    const id = card.id ?? card.productId;
                    Alpine.store('cart').dec(id);
                },

                decreaseQtyMin1(item) {
                    const existing = Alpine.store('cart').items.find(i => i.productId === item.id);
                    if (!existing || existing.quantity <= 1) return;
                    existing.quantity--;
                },

                showCartNotif(name) {
                    const id = ++this.cartNotifSeq;
                    if (this.cartNotifs.length >= 3) this.cartNotifs.shift();
                    this.cartNotifs.push({ id, name, leaving: false });
                    setTimeout(() => {
                        const notif = this.cartNotifs.find(n => n.id === id);
                        if (notif) notif.leaving = true;
                        setTimeout(() => {
                            this.cartNotifs = this.cartNotifs.filter(n => n.id !== id);
                        }, 250);
                    }, 2500);
                },

                removeCartItem(id) {
                    const item = Alpine.store('cart').items.find(i => i.productId === id);
                    Alpine.store('cart').items = Alpine.store('cart').items.filter(i => i.productId !== id);
                    if (item) {
                        this.showCartNotif(item.name);
                        const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                        this.pushMsg({ type: 'bot',
                            text: '🗑️ ' + item.name + ' eliminado del pedido.',
                            quickReplies: Alpine.store('cart').items.length ? ['Ver mi pedido', 'Confirmar pedido', ...qrs] : qrs,
                        });
                    }
                },

                showCartSummary() {
                    if (!this.chatCart.length) {
                        const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                        this.botDelay('Tu pedido está vacío. ¡Elige algo primero! 😊', { quickReplies: qrs });
                        return;
                    }
                    this.botDelay('¡Aquí está tu pedido! ¿Confirmamos? 🛒', {
                        cartLive: true,
                        quickReplies: ['Confirmar pedido', 'Seguir eligiendo'],
                    });
                },

                /* ── Confirmación del pedido ── */
                async confirmOrder() {
                    if (!this.chatCart.length) {
                        this.botDelay('No hay nada en tu pedido. ¡Elige algo primero!');
                        return;
                    }
                    this.sending  = true;
                    this.isTyping = true;
                    this.error    = null;

                    try {
                        const res = await fetch('/api/v1/orders', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                            body: JSON.stringify({
                                table_hash: this.tableHash,
                                items: this.chatCart.map(i => ({
                                    product_id:    i.id,
                                    quantity:      i.qty,
                                    modifications: [],
                                })),
                            }),
                        });
                        const data = await res.json();
                        this.isTyping = false;

                        if (res.ok && data.success) {
                            Alpine.store('cart').items = [];
                            Alpine.store('bill').active = true;
                            this.pushMsg({ type: 'system',
                                text: '✅ Pedido #' + data.order_id + ' confirmado — en preparación' });
                            this.pushMsg({ type: 'bot',
                                text: '¡Tu pedido está en camino! En unos minutos te lo llevamos a la mesa 🚀',
                                quickReplies: ['📋 Nuevo pedido'] });
                        } else {
                            this.isTyping = false;
                            this.error = data.message ?? 'Error al confirmar el pedido.';
                        }
                    } catch {
                        this.isTyping = false;
                        this.error = 'Error de conexión. Inténtalo de nuevo.';
                    } finally {
                        this.sending = false;
                        this.$nextTick(() => this.scrollBottom());
                    }
                },

                /* ── Envío de texto libre ── */
                async sendMessage() {
                    const text = this.input.trim();
                    if (!text || this.sending || this.isTyping) return;
                    this.input = '';
                    this.pushMsg({ type: 'user', text, time: Date.now() });

                    /* Detectar keywords locales primero */
                    const lower = text.toLowerCase();
                    const cats  = this.menuData?.categories ?? [];
                    const matched = cats.find(c => c.name.toLowerCase() === lower);
                    if (matched)                                   { this.showCategoryCards(matched); return; }
                    if (lower.includes('mi pedido') ||
                        lower.includes('ver pedido') ||
                        lower.includes('carrito'))                 { this.showCartSummary(); return; }
                    if (lower.includes('confirmar') &&
                        lower.includes('pedido'))                  { this.confirmOrder(); return; }

                    /* Detectar intención de eliminar producto del pedido */
                    if (this.tryRemoveByName(lower, text)) return;

                    /* Detectar producto por nombre con intención de pedido */
                    if (this.tryAddByName(lower)) return;

                    await this.sendToAI(text);
                },

                /* ── Eliminar producto del pedido por texto ── */
                tryRemoveByName(lower, originalText) {
                    if (!this.chatCart.length) return false;
                    const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                    const removePatterns = [/\b(quita|quitar|elimina|eliminar|borra|borrar|saca|sacar|cancela|cancelar|no quiero|sin )\b/];
                    if (!removePatterns.some(p => p.test(norm(lower)))) return false;

                    const nl = norm(lower);
                    for (const item of this.chatCart) {
                        const words = norm(item.name).split(/\s+/).filter(w => w.length > 3);
                        if (words.some(w => nl.includes(w))) {
                            Alpine.store('cart').items = Alpine.store('cart').items.filter(i => i.productId !== item.id);
                            const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                            this.pushMsg({ type: 'bot',
                                text: '🗑️ ' + item.name + ' eliminado del pedido. ¿Seguimos? 😊',
                                quickReplies: this.chatCart.length ? ['Ver mi pedido', 'Confirmar pedido', ...qrs] : qrs,
                            });
                            return true;
                        }
                    }
                    return false;
                },

                /* ── Añadir producto por nombre escrito en el chat ── */
                tryAddByName(lower) {
                    if (!this.menuData) return false;

                    /* 1. Bloquear contextos que NO son pedidos */
                    const blockPatterns = [
                        /\b(modifica|cambia|actualiza|edita|borra|elimina|quita)\b/,
                        /\b(precio|coste|cuesta|cu[aá]nto|vale|valor)\b/,
                        /[¿?]/,
                        /\b(qu[eé]|cu[aá]l|c[oó]mo|cu[aá]ndo|d[oó]nde|cu[aá]ntos)\b/,
                        /\b(lleva|tiene|contiene|incluye|hay)\b/,
                        /\b(alergi[ao]|al[eé]rgeno|intolerancia|gluten|lactosa)\b/,
                        /\b(ingrediente|receta|composici[oó]n|preparaci[oó]n)\b/,
                        /\b(informaci[oó]n|info|saber|conocer|explicar|describir|d[eé]cuéntame)\b/,
                        /\bno\s+(quiero|me|le|pido|necesito)\b/,
                        /\b(s[oó]lo|solo|sin)\s+\w/,
                    ];
                    if (blockPatterns.some(p => p.test(lower))) return false;

                    /* 2. Intención de pedido explícita (word-boundary) */
                    const intentPatterns = [
                        /\b(quiero|quisiera|deseo)\b/,
                        /\b(dame|deme|tr[aá]eme|trae)\b/,
                        /\b(ponme|pon|ponednos)\b/,
                        /\b(agrega|agr[eé]game|a[ñn]ade|a[ñn][aá]deme)\b/,
                        /\b(pido|pedimos|pedir)\b/,
                        /\b(necesito|necesitamos)\b/,
                        /\bme\s+(pones|traes|das|puedes\s+traer)\b/,
                    ];
                    /* Pedido implícito: mensaje corto que empieza por cantidad + nombre */
                    const quantityOnlyIntent = lower.trim().length < 45
                        && /^(un[ao]?|dos|tres|cuatro|cinco|[1-5])\s+\w/.test(lower.trim());
                    if (!intentPatterns.some(p => p.test(lower)) && !quantityOnlyIntent) return false;

                    /* 3. Buscar producto con word-boundary cuando sea posible */
                    const allProducts = this.menuData.categories.flatMap(c =>
                        c.products.map(p => ({ id: p.id, name: p.name, price: p.price,
                            destination: c.destination,
                            emoji: this.getCategoryEmoji(c.name) }))
                    );
                    const found = allProducts.find(p => {
                        const name = p.name.toLowerCase();
                        const esc  = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        return new RegExp(`\\b${esc}\\b`).test(lower) || lower === name;
                    });
                    if (!found) return false;

                    /* 4. Parsear cantidad con word-boundary */
                    const numWords = { uno: 1, una: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5 };
                    let qty = 1;
                    const digitMatch = lower.match(/\b([1-5])\b/);
                    if (digitMatch) {
                        qty = parseInt(digitMatch[1]);
                    } else {
                        for (const [word, num] of Object.entries(numWords)) {
                            if (new RegExp(`\\b${word}\\b`).test(lower)) { qty = num; break; }
                        }
                    }

                    /* 5. Añadir al carrito unificado */
                    const existingStore = Alpine.store('cart').items.find(i => i.productId === found.id);
                    if (existingStore) {
                        existingStore.quantity += qty;
                    } else {
                        for (let _i = 0; _i < qty; _i++) {
                            Alpine.store('cart').add({ id: found.id, name: found.name, price: found.price, destination: found.destination || 'kitchen', removable: [], extras: [] });
                        }
                    }

                    /* 6. Respuesta inmediata del bot */
                    const cats = this.menuData.categories;
                    this.pushMsg({
                        type: 'bot',
                        text: '✅ ' + (qty > 1 ? qty + '× ' : '') + found.name + ' añadido al pedido. ¿Algo más?',
                        quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Ver mi pedido', 'Confirmar pedido'],
                    });
                    return true;
                },

                /* ── Llamada a la IA ── */
                async sendToAI(text) {
                    if (!this.conversationId || this.closed) {
                        const cats = this.menuData?.categories ?? [];
                        this.botDelay('Por favor elige una categoría para empezar 😊',
                            { quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Ver mi pedido'] });
                        return;
                    }
                    this.sending  = true;
                    this.isTyping = true;
                    this.error    = null;

                    try {
                        const res = await fetch('/api/v1/chat/' + this.conversationId + '/message', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                            body: JSON.stringify({ message: text }),
                        });
                        const data = await res.json();
                        this.isTyping = false;

                        if (res.ok && data.success) {
                            const cats  = this.menuData?.categories ?? [];
                            const qrs   = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                            const cards = (data.data.cards ?? []).map(c => {
                                const matchedCat = cats.find(cat => cat.products.some(p => p.id === c.id));
                                return {
                                    id:          c.id,
                                    name:        c.name,
                                    price:       c.price,
                                    description: c.description,
                                    image:       c.image ?? null,
                                    allergens:   (c.allergens ?? []).map(a => ({
                                        name: a,
                                        ...this.getAllergenIcon(a),
                                    })),
                                    foodIcon: this.getFoodIcon(matchedCat?.name ?? '', c.name),
                                    emoji:    this.getCategoryEmoji(matchedCat?.name ?? ''),
                                };
                            });
                            this.pushMsg({
                                type:         'bot',
                                text:         data.data.reply,
                                cards:        cards.length ? cards : undefined,
                                quickReplies: [...qrs, 'Confirmar pedido'],
                            });
                            if (data.data.closed) this.closed = true;
                        } else {
                            this.error = data.message ?? 'Error al enviar el mensaje.';
                        }
                    } catch {
                        this.isTyping = false;
                        this.error = 'Error de conexión. Inténtalo de nuevo.';
                    } finally {
                        this.sending = false;
                        this.$nextTick(() => this.scrollBottom());
                    }
                },

                /* ── Emoji representativo por nombre de categoría ── */
                getCategoryEmoji(name) {
                    const s = (name || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                    if (/pizza/.test(s))                                          return '🍕';
                    if (/burger|hamburgues/.test(s))                              return '🍔';
                    if (/pasta|espaguet|fettuccin|lasana|canelones|carbonara/.test(s)) return '🍝';
                    if (/paella|arroz|risotto/.test(s))                           return '🍚';
                    if (/ensalada|salad/.test(s))                                 return '🥗';
                    if (/sopa|crema|caldo|gazpacho/.test(s))                      return '🥣';
                    if (/postre|dulce|helado|tarta|bizcocho|mousse|flan|brownie/.test(s)) return '🍰';
                    if (/cerveza|beer|cana|birra|caña/.test(s))                   return '🍺';
                    if (/vino|wine|cava|champan|prosecco|sangria/.test(s))        return '🍷';
                    if (/coctel|cocktail|mojito|margarita|daiquiri|gin/.test(s))  return '🍹';
                    if (/bebida|refresco|agua|zumo|juice|batido|cola|limonad|cafe/.test(s)) return '🥤';
                    if (/sushi|maki|nigiri|temaki/.test(s))                       return '🍣';
                    if (/bocadillo|sandwich|bocata|baguet|wrap/.test(s))          return '🥪';
                    if (/tapa|tapas|pincho|montadito|racion/.test(s))             return '🍢';
                    if (/entrante|aperitivo|starter/.test(s))                     return '🫔';
                    if (/carne|ternera|pollo|pavo|cordero|parrilla|filete|chulet/.test(s)) return '🥩';
                    if (/pescado|merluza|bacalao|salmon|atun|dorada|lubina/.test(s)) return '🐟';
                    if (/marisco|gamba|langosta|pulpo|calamar|mejillo/.test(s))   return '🦞';
                    if (/vegano|vegetarian|vegan|veggie/.test(s))                 return '🥦';
                    if (/combo|menu del dia|menu|oferta/.test(s))                 return '🎯';
                    return '🍽️';
                },

                /* ── Icono por tipo de alimento (categoría del producto) ── */
                getFoodIcon(categoryName, productName) {
                    const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                    const s = norm(categoryName) + ' ' + norm(productName);
                    if (/pizza/.test(s))                                                               return { svgId: 'pizza',     label: 'Pizza' };
                    if (/burger|hamburgues/.test(s))                                                   return { svgId: 'burger',    label: 'Hamburguesa' };
                    if (/pasta|espaguet|fettuccin|lasana|canelones|macarron|carbonara|bolonesa/.test(s)) return { svgId: 'pasta',   label: 'Pasta' };
                    if (/paella|arroz|risotto/.test(s))                                                return { svgId: 'rice',      label: 'Arroz' };
                    if (/ensalada|salad/.test(s))                                                      return { svgId: 'salad',     label: 'Ensalada' };
                    if (/sopa|crema|caldo|gazpacho|vichyssoise|consomme/.test(s))                      return { svgId: 'soup',      label: 'Sopa' };
                    if (/tapa|tapas|pincho|montadito|ración|racion/.test(s))                           return { svgId: 'tapas',     label: 'Tapas' };
                    if (/entrante|aperitivo|starter/.test(s))                                          return { svgId: 'starter',   label: 'Entrante' };
                    if (/postre|dulce|helado|tarta|bizcocho|mousse|flan|brownie|crepe|tiramisu/.test(s)) return { svgId: 'dessert', label: 'Postre' };
                    if (/cerveza|beer|cana|birra|copa de|caña/.test(s))                                return { svgId: 'beer',      label: 'Cerveza' };
                    if (/vino|wine|cava|champan|prosecco|sangria/.test(s))                             return { svgId: 'wine',      label: 'Vino' };
                    if (/coctel|cocktail|mojito|margarita|daiquiri|gin|combinado|destilado/.test(s))   return { svgId: 'cocktail',  label: 'Cóctel' };
                    if (/bebida|refresco|agua|zumo|juice|batido|smoothie|cola|limonad|infusion|cafe/.test(s)) return { svgId: 'drink', label: 'Bebida' };
                    if (/sushi|maki|nigiri|temaki|onigiri|japon/.test(s))                              return { svgId: 'sushi',     label: 'Sushi' };
                    if (/bocadillo|sandwich|bocata|baguet|wrap|sub/.test(s))                           return { svgId: 'sandwich',  label: 'Bocadillo' };
                    if (/carne|ternera|vaca|cerdo|pollo|pavo|cordero|parrilla|grill|asado|filete|costill|entrecot|chulet/.test(s)) return { svgId: 'meat', label: 'Carne' };
                    if (/pescado|merluza|bacalao|salmon|atun|dorada|lubina|lenguado|rodaballo/.test(s)) return { svgId: 'fish-dish', label: 'Pescado' };
                    if (/marisco|gamba|langosta|pulpo|calamar|sepia|almeja|mejillo|ostra|bogavante|chipiro/.test(s)) return { svgId: 'seafood', label: 'Marisco' };
                    if (/vegano|vegetarian|vegan|veggie/.test(s))                                      return { svgId: 'vegan',     label: 'Vegano' };
                    return null;
                },

                /* ── Mapeo de alérgenos UE (Reglamento 1169/2011) ── */
                getAllergenIcon(name) {
                    const n = name.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                    if (/gluten|trigo|cebada|centeno|avena|espelta|kamut|harina/.test(n))
                        return { svgId: 'gluten',      label: 'Gluten' };
                    if (/crustaceo|gamba|langosta|langostino|cangrejo|bogavante|cigala/.test(n))
                        return { svgId: 'crustaceans', label: 'Crustáceos' };
                    if (/huevo|yema|clara|ovoalbumina/.test(n))
                        return { svgId: 'egg',         label: 'Huevo' };
                    if (/pescado|merluza|bacalao|salmon|atun|anchoa|sardina|boqueron|lenguado|dorada|lubina/.test(n))
                        return { svgId: 'fish',        label: 'Pescado' };
                    if (/cacahuete|mani/.test(n))
                        return { svgId: 'peanuts',     label: 'Cacahuetes' };
                    if (/soja|soya|tofu|edamame/.test(n))
                        return { svgId: 'soy',         label: 'Soja' };
                    if (/leche|lacteo|lactosa|queso|mantequilla|nata|yogur|suero|caseina/.test(n))
                        return { svgId: 'milk',        label: 'Lácteos' };
                    if (/nuez|nueces|almendra|avellana|pistacho|anacardo|macadamia|pacana|brasil|castana/.test(n))
                        return { svgId: 'nuts',        label: 'Frutos secos' };
                    if (/apio/.test(n))
                        return { svgId: 'celery',      label: 'Apio' };
                    if (/mostaza/.test(n))
                        return { svgId: 'mustard',     label: 'Mostaza' };
                    if (/sesamo|tahini|tahina/.test(n))
                        return { svgId: 'sesame',      label: 'Sésamo' };
                    if (/sulfit|azufre|so2|dioxido/.test(n))
                        return { svgId: 'sulphites',   label: 'Sulfitos' };
                    if (/altramuz|lupino|lupina/.test(n))
                        return { svgId: 'lupin',       label: 'Altramuces' };
                    if (/molusco|calamar|pulpo|ostra|almeja|mejillon|sepia|chipiro/.test(n))
                        return { svgId: 'molluscs',    label: 'Moluscos' };
                    return { svgId: null, label: name };
                },

                /* ── Estilos diferenciados para quick replies ── */
                getQrStyle(qr) {
                    const base = 'border-radius:9999px; padding:5px 12px; font-size:12px; font-family:\'Space Grotesk\',sans-serif; font-weight:600; cursor:pointer; transition:all 150ms ease; border:1px solid; ';
                    if (qr === 'Confirmar pedido')
                        return base + 'background:rgba(34,197,94,0.18); color:#22C55E; border-color:rgba(34,197,94,0.5);';
                    if (qr === 'Ver mi pedido')
                        return base + 'background:rgba(34,211,238,0.15); color:#22D3EE; border-color:rgba(34,211,238,0.45);';
                    return base + 'background:rgba(46,80,176,0.22); color:#8FA8E8; border-color:rgba(46,80,176,0.5);';
                },

                onQrEnter(el, qr) {
                    if (qr === 'Confirmar pedido') { el.style.background = '#16A34A'; el.style.color = '#fff'; }
                    else if (qr === 'Ver mi pedido') { el.style.background = '#0891B2'; el.style.color = '#fff'; }
                    else { el.style.background = '#1A3380'; el.style.color = '#fff'; }
                },

                onQrLeave(el, qr) {
                    if (qr === 'Confirmar pedido') { el.style.background = 'rgba(34,197,94,0.18)'; el.style.color = '#22C55E'; }
                    else if (qr === 'Ver mi pedido') { el.style.background = 'rgba(34,211,238,0.15)'; el.style.color = '#22D3EE'; }
                    else { el.style.background = 'rgba(46,80,176,0.22)'; el.style.color = '#8FA8E8'; }
                },

                scrollBottom() {
                    this.$nextTick(() => {
                        requestAnimationFrame(() => {
                            this.$refs.chatEnd?.scrollIntoView({ block: 'end' });
                        });
                    });
                },
            }));
        });
    </script>
</head>

<body class="font-sans antialiased bg-gray-200 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">

    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
              bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300
              px-4 py-2 rounded font-medium z-50 shadow">
        Saltar al contenido principal
    </a>

    {{-- ── Componente Alpine raíz ──────────────────────────────────── --}}
    <div x-data="menuFilters()">

        {{-- ── Header ──────────────────────────────────────────────── --}}
        <header class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                        Carta digital
                    </p>
                    <h1 class="text-lg sm:text-xl font-bold leading-tight text-gray-900 dark:text-white">
                        {{ $table->user->name }}
                    </h1>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 text-xs font-medium
                                 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300
                                 px-2.5 py-1 rounded-full">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h11M9 21V3M19 14l2 2-2 2m2-2H13"/>
                        </svg>
                        {{ $table->name }}
                    </span>
                </div>
            </div>
        </header>

        {{-- ── FAB Chat IA (Zampi Design System) ─────────────────── --}}
        <div x-data="chatWidget()">

            {{-- Mascot SVG symbol (Official Zampi Design System — zm- prefixed IDs) --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                <symbol id="zampi-mascot" viewBox="0 0 120 110">
                    <defs>
                        <radialGradient id="zm-bT" cx="38%" cy="28%" r="62%">
                            <stop offset="0%" stop-color="#FBDF6A"/>
                            <stop offset="45%" stop-color="#E8980C"/>
                            <stop offset="100%" stop-color="#A05500"/>
                        </radialGradient>
                        <radialGradient id="zm-bB" cx="38%" cy="22%" r="65%">
                            <stop offset="0%" stop-color="#F5C830"/>
                            <stop offset="55%" stop-color="#CC7008"/>
                            <stop offset="100%" stop-color="#8B4000"/>
                        </radialGradient>
                        <linearGradient id="zm-ch" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#FFD740"/>
                            <stop offset="100%" stop-color="#F59000"/>
                        </linearGradient>
                        <linearGradient id="zm-mt" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#7B3010"/>
                            <stop offset="100%" stop-color="#4A1800"/>
                        </linearGradient>
                        <linearGradient id="zm-lt" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#5CC830"/>
                            <stop offset="100%" stop-color="#348010"/>
                        </linearGradient>
                        <radialGradient id="zm-sc" cx="50%" cy="38%" r="55%">
                            <stop offset="0%" stop-color="#0C0620"/>
                            <stop offset="100%" stop-color="#04000E"/>
                        </radialGradient>
                        <filter id="zm-sh"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#3A1800" flood-opacity="0.45"/></filter>
                        <filter id="zm-gP"><feGaussianBlur stdDeviation="3.5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                        <filter id="zm-gC"><feGaussianBlur stdDeviation="2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>
                    <ellipse cx="60" cy="107" rx="40" ry="4" fill="#2A0800" opacity="0.25"/>
                    <line x1="60" y1="2" x2="60" y2="22" stroke="#7A5010" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M60 3 L78 9 L60 16 Z" fill="#D80E1A"/>
                    <ellipse cx="68" cy="8" rx="4" ry="1.8" fill="white" opacity="0.25" transform="rotate(-10,68,8)"/>
                    <ellipse cx="60" cy="28" rx="48" ry="22" fill="url(#zm-bT)" filter="url(#zm-sh)"/>
                    <ellipse cx="44" cy="20" rx="16" ry="8" fill="white" opacity="0.25" transform="rotate(-10,44,20)"/>
                    <ellipse cx="42" cy="16" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(-18,42,16)"/>
                    <ellipse cx="60" cy="12" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9"/>
                    <ellipse cx="78" cy="17" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(18,78,17)"/>
                    <ellipse cx="32" cy="26" rx="4" ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(-22,32,26)"/>
                    <ellipse cx="88" cy="27" rx="4" ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(22,88,27)"/>
                    <ellipse cx="60" cy="48" rx="48" ry="6" fill="#B06010"/>
                    <path d="M12 51 Q28 46 42 53 Q54 59 60 52 Q66 45 78 53 Q92 60 108 51 L108 59 Q92 66 78 60 Q66 54 60 60 Q54 66 42 60 Q28 53 12 59 Z" fill="url(#zm-ch)"/>
                    <path d="M16 55 C11 60 11 68 16 71 L19 71 C15 66 16 55 16 55 Z" fill="#FBBF24"/>
                    <ellipse cx="16.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                    <path d="M104 55 C109 60 109 68 104 71 L101 71 C105 66 104 55 104 55 Z" fill="#FBBF24"/>
                    <ellipse cx="103.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                    <path d="M12 59 Q22 55 34 62 Q46 68 60 61 Q74 54 86 62 Q98 68 108 59 L108 65 Q98 73 86 66 Q74 60 60 66 Q46 72 34 66 Q22 60 12 65 Z" fill="url(#zm-lt)"/>
                    <ellipse cx="60" cy="70" rx="48" ry="9" fill="url(#zm-mt)" filter="url(#zm-sh)"/>
                    <ellipse cx="60" cy="84" rx="48" ry="14" fill="url(#zm-bB)" filter="url(#zm-sh)"/>
                    <ellipse cx="60" cy="95" rx="43" ry="8" fill="#8B4000"/>
                    <ellipse cx="60" cy="101" rx="36" ry="5" fill="#6A3000"/>
                    <ellipse cx="46" cy="80" rx="16" ry="5" fill="white" opacity="0.15"/>
                    <ellipse cx="60" cy="66" rx="34" ry="28" fill="#6010B0" opacity="0.45" filter="url(#zm-gP)"/>
                    <ellipse cx="60" cy="66" rx="32" ry="26" fill="#40087A" stroke="#CC60F8" stroke-width="3"/>
                    <ellipse cx="60" cy="66" rx="29" ry="23" fill="#2C0660" stroke="#7828B8" stroke-width="1.2"/>
                    <ellipse cx="60" cy="66" rx="27" ry="21" fill="url(#zm-sc)"/>
                    <rect x="34" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#zm-gC)"/>
                    <rect x="35" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                    <rect x="36" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                    <ellipse cx="39" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                    <rect x="35" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                    <rect x="68" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#zm-gC)"/>
                    <rect x="69" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                    <rect x="70" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                    <ellipse cx="73" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                    <rect x="69" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                    <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="4" stroke-linecap="round" opacity="0.2"/>
                    <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="2" stroke-linecap="round"/>
                </symbol>
            </svg>

            {{-- Sprite SVG: 14 alérgenos Reglamento UE 1169/2011 --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                {{-- 1. Gluten (cereales con gluten) --}}
                <symbol id="al-gluten" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="11.2" y="4" width="1.6" height="16" rx="0.8"/>
                    <ellipse cx="12" cy="6.5" rx="2.8" ry="4"/>
                    <ellipse cx="7" cy="11" rx="2.2" ry="3.2" transform="rotate(-30 7 11)"/>
                    <ellipse cx="17" cy="11" rx="2.2" ry="3.2" transform="rotate(30 17 11)"/>
                    <ellipse cx="7.5" cy="16.5" rx="2" ry="2.8" transform="rotate(-25 7.5 16.5)"/>
                    <ellipse cx="16.5" cy="16.5" rx="2" ry="2.8" transform="rotate(25 16.5 16.5)"/>
                </symbol>
                {{-- 2. Crustáceos --}}
                <symbol id="al-crustaceans" viewBox="0 0 24 24">
                    <circle cx="18" cy="5" r="2.5" fill="currentColor"/>
                    <path d="M18 3 L22 1.5 M18.8 2 L21.5 0" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    <path d="M18 5 C21 8 21 15 17 19 C14 22 9 22 6 19 C3 16 4 12 7 11 C10 10 12 12 11 15 C10.5 16.5 9.5 16.5 9 15.5"
                          stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                </symbol>
                {{-- 3. Huevo --}}
                <symbol id="al-egg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C8 2 5 6 5 11 C5 16.5 8.1 22 12 22 C15.9 22 19 16.5 19 11 C19 6 16 2 12 2 Z"/>
                </symbol>
                {{-- 4. Pescado --}}
                <symbol id="al-fish" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="9.5" cy="12" rx="7.5" ry="5.5"/>
                    <path d="M17 6.5 L24 4 L24 20 L17 17.5 Z"/>
                    <circle cx="5" cy="11" r="1.4" fill="#050B1F"/>
                </symbol>
                {{-- 5. Cacahuetes --}}
                <symbol id="al-peanuts" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C7 2 4 4.5 4 7.5 C4 10.5 6.5 12 9.5 12.3 Q9 12.8 9 13.5 Q9 14 9.5 14.2 C6.5 14.8 4 16.5 4 19 C4 21.5 7.5 23 12 23 C16.5 23 20 21.5 20 19 C20 16.5 17.5 14.8 14.5 14.2 Q15 14 15 13.5 Q15 12.8 14.5 12.3 C17.5 12 20 10.5 20 7.5 C20 4.5 17 2 12 2 Z"/>
                </symbol>
                {{-- 6. Soja --}}
                <symbol id="al-soy" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="6.5" r="4"/>
                    <circle cx="7" cy="15.5" r="4"/>
                    <circle cx="17" cy="15.5" r="4"/>
                    <path d="M12 10 L7 12 M12 10 L17 12" stroke="currentColor" stroke-width="1.5" fill="none"/>
                </symbol>
                {{-- 7. Leche / Lácteos --}}
                <symbol id="al-milk" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 L19 13 C19 18.5 15.9 22 12 22 C8.1 22 5 18.5 5 13 Z"/>
                </symbol>
                {{-- 8. Frutos secos (nueces, almendras…) --}}
                <symbol id="al-nuts" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C10 2 8.5 3.5 8 5 L6 8 C5 9 5 10 5 11 C5 15 8.1 20 12 20 C15.9 20 19 15 19 11 C19 10 19 9 18 8 L16 5 C15.5 3.5 14 2 12 2 Z"/>
                    <path d="M12 20 L12 22 M10 21.5 L14 21.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                </symbol>
                {{-- 9. Apio --}}
                <symbol id="al-celery" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="4.5" y="11" width="3" height="10" rx="1.5"/>
                    <rect x="10.5" y="8" width="3" height="13" rx="1.5"/>
                    <rect x="16.5" y="11" width="3" height="10" rx="1.5"/>
                    <path d="M6 11 C5 7 3.5 5 6 3 C8 4 8 8.5 6 11 Z"/>
                    <path d="M12 8 C11 4 9.5 2 12 1 C14 2 14 5.5 12 8 Z"/>
                    <path d="M18 11 C19 7 20.5 5 18 3 C16 4 16 8.5 18 11 Z"/>
                </symbol>
                {{-- 10. Mostaza --}}
                <symbol id="al-mustard" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="8" y="11" width="8" height="10" rx="1.5"/>
                    <rect x="9" y="8" width="6" height="3.5" rx="1"/>
                    <rect x="9.5" y="5.5" width="5" height="3" rx="1.5"/>
                    <circle cx="12" cy="4" r="1.2"/>
                </symbol>
                {{-- 11. Sésamo --}}
                <symbol id="al-sesame" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="4.5" rx="2" ry="3.5"/>
                    <ellipse cx="17.5" cy="7.5" rx="2" ry="3.5" transform="rotate(60 17.5 7.5)"/>
                    <ellipse cx="19.5" cy="14" rx="2" ry="3.5" transform="rotate(120 19.5 14)"/>
                    <ellipse cx="15" cy="19.5" rx="2" ry="3.5" transform="rotate(180 15 19.5)"/>
                    <ellipse cx="9" cy="19.5" rx="2" ry="3.5" transform="rotate(240 9 19.5)"/>
                    <ellipse cx="4.5" cy="14" rx="2" ry="3.5" transform="rotate(300 4.5 14)"/>
                </symbol>
                {{-- 12. Dióxido de azufre / Sulfitos --}}
                <symbol id="al-sulphites" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 3 L17 3 C17 8.5 14.5 10.5 13 12 L13 17 L15 17 L15 20 L9 20 L9 17 L11 17 L11 12 C9.5 10.5 7 8.5 7 3 Z"/>
                </symbol>
                {{-- 13. Altramuces --}}
                <symbol id="al-lupin" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="11.2" y="4" width="1.6" height="16" rx="0.8"/>
                    <ellipse cx="12" cy="6" rx="3.5" ry="2.2"/>
                    <ellipse cx="12" cy="10.5" rx="4" ry="2.5"/>
                    <ellipse cx="12" cy="15" rx="3.5" ry="2.2"/>
                    <ellipse cx="12" cy="18.5" rx="2.5" ry="1.8"/>
                </symbol>
                {{-- 14. Moluscos --}}
                <symbol id="al-molluscs" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22 L3 9 Q3 4 12 3 Q21 4 21 9 Z"/>
                    <path d="M12 22 L4.5 8.5 M12 22 L7.5 4.5 M12 22 L12 3 M12 22 L16.5 4.5 M12 22 L19.5 8.5"
                          stroke="#050B1F" stroke-width="0.9" fill="none"/>
                </symbol>
            </svg>

            {{-- Sprite SVG: categorías de tipo de alimento --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                <symbol id="fc-pizza" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3 L21 20 L3 20 Z"/>
                    <ellipse cx="12" cy="20" rx="9" ry="2" opacity="0.5"/>
                    <circle cx="12" cy="15" r="1.8" opacity="0.45"/>
                    <circle cx="9"  cy="11" r="1.3" opacity="0.45"/>
                    <circle cx="15" cy="11" r="1.3" opacity="0.45"/>
                </symbol>
                <symbol id="fc-burger" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 9 Q12 5 20 9 L20 11 L4 11 Z"/>
                    <rect x="3" y="12" width="18" height="3"   rx="1.5"/>
                    <rect x="3" y="16" width="18" height="4.5" rx="2.5"/>
                </symbol>
                <symbol id="fc-pasta" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 22 12 22 Q21 22 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <rect x="11" y="3" width="1.8" height="9" rx="0.9"/>
                    <rect x="8.2" y="3" width="1.4" height="5.5" rx="0.7"/>
                    <rect x="14.4" y="3" width="1.4" height="5.5" rx="0.7"/>
                    <path d="M9.6 8.5 Q12 10.5 14.4 8.5" fill="none" stroke="currentColor" stroke-width="1.3"/>
                </symbol>
                <symbol id="fc-salad" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 21 12 21 Q21 21 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <path d="M8 12 C6 7 10 4 12 8 C14 4 18 7 16 12"/>
                    <path d="M10.5 12 C11.5 9.5 12.5 9.5 13.5 12" opacity="0.6"/>
                </symbol>
                <symbol id="fc-soup" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 22 12 22 Q21 22 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <path d="M8  10 C9  7 8  5 9  3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M12  9 C13 6 12 4 13 2" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M16 10 C17 7 16 5 17 3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </symbol>
                <symbol id="fc-starter" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="20" rx="10" ry="2.5"/>
                    <path d="M2 20 Q2 9 12 9 Q22 9 22 20"/>
                    <circle cx="12" cy="9" r="2.2"/>
                </symbol>
                <symbol id="fc-tapas" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="21" rx="10" ry="2.5"/>
                    <circle cx="8"  cy="14" r="3.5"/>
                    <circle cx="12" cy="12" r="3.5" opacity="0.8"/>
                    <circle cx="16" cy="14" r="3.5" opacity="0.6"/>
                    <rect x="11.3" y="6" width="1.4" height="7" rx="0.7"/>
                </symbol>
                <symbol id="fc-dessert" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 12 Q12 5 18 12 Z"/>
                    <path d="M5 12 L7 21 L17 21 L19 12 Z"/>
                    <rect x="5" y="15.5" width="14" height="1.2" rx="0.6" opacity="0.35"/>
                    <rect x="5" y="18"   width="14" height="1.2" rx="0.6" opacity="0.35"/>
                    <circle cx="12" cy="7" r="2.5" opacity="0.7"/>
                </symbol>
                <symbol id="fc-drink" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 4 L6 21 L18 21 L17 4 Z"/>
                    <rect x="14" y="2" width="2.2" height="13" rx="1.1" opacity="0.55"/>
                </symbol>
                <symbol id="fc-beer" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 9 L5 22 Q5 23 7 23 L16 23 Q18 23 18 22 L18 9 Z"/>
                    <path d="M18 11 Q23 11 23 15 Q23 19 18 19" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/>
                    <path d="M5 9 Q7 5 9 9 Q11 5 13 9 Q15 5 17 9 Q18 7 18 9"/>
                </symbol>
                <symbol id="fc-wine" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 3 L17 3 C17 9.5 14 12 13 13.5 L13 18 L15 18 L15 21 L9 21 L9 18 L11 18 L11 13.5 C10 12 7 9.5 7 3 Z"/>
                </symbol>
                <symbol id="fc-cocktail" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 3 L21 3 L12 15 Z"/>
                    <rect x="11" y="15" width="2"  height="5"/>
                    <rect x="8"  y="20" width="8"  height="2.5" rx="1.25"/>
                    <circle cx="17" cy="4" r="1.8" opacity="0.65"/>
                </symbol>
                <symbol id="fc-meat" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 15 C3 10 6 6 10 6 C10 4 12 3 14 5 C17 4 21 7 21 12 C21 17 18 21 12 21 C7 21 5 19 4 15 Z"/>
                    <path d="M10 6 Q7 10 8 15" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3"/>
                </symbol>
                <symbol id="fc-fish-dish" viewBox="0 0 26 22" fill="currentColor">
                    <ellipse cx="10" cy="11" rx="8" ry="5.5"/>
                    <path d="M18 6 L26 3 L26 19 L18 16 Z"/>
                    <circle cx="4.5" cy="10" r="1.6" opacity="0.3"/>
                </symbol>
                <symbol id="fc-seafood" viewBox="0 0 24 24">
                    <circle cx="18" cy="5" r="2.5" fill="currentColor"/>
                    <path d="M18 3 L22 1.5 M18.8 2 L21.5 0" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    <path d="M18 5 C21 8 21 15 17 19 C14 22 9 22 6 19 C3 16 4 12 7 11 C10 10 12 12 11 15 C10.5 16.5 9.5 16.5 9 15.5"
                          stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                </symbol>
                <symbol id="fc-sandwich" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 8 Q12 4 21 8 L21 11 L3 11 Z"/>
                    <rect x="3" y="11"   width="18" height="2.5" rx="0.5" opacity="0.7"/>
                    <rect x="3" y="13.5" width="18" height="2"   rx="0.5" opacity="0.5"/>
                    <rect x="3" y="16"   width="18" height="4.5" rx="2.5"/>
                </symbol>
                <symbol id="fc-sushi" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="9"/>
                    <circle cx="12" cy="12" r="5.5" opacity="0.45"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </symbol>
                <symbol id="fc-rice" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13 Q3 21 12 21 Q21 21 21 13 Z"/>
                    <rect x="2" y="11" width="20" height="2.5" rx="1.25"/>
                    <circle cx="9"  cy="9"  r="1.3" opacity="0.8"/>
                    <circle cx="13" cy="8"  r="1.3" opacity="0.8"/>
                    <circle cx="11" cy="6"  r="1.3" opacity="0.8"/>
                    <circle cx="16" cy="10" r="1.1" opacity="0.6"/>
                    <circle cx="7"  cy="7"  r="1.1" opacity="0.6"/>
                </symbol>
                <symbol id="fc-vegan" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22 C12 22 4 15 4 9 C4 4 8 2 12 2 C16 2 20 4 20 9 C20 15 12 22 12 22 Z"/>
                    <path d="M12 22 L12 9" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                    <path d="M12 14 L8 10 M12 18 L16 14" fill="none" stroke="currentColor" stroke-width="1" opacity="0.25"/>
                </symbol>
            </svg>

            {{-- Botón flotante Zampi (se oculta cuando el chat está abierto) --}}
            <button type="button"
                    @click="openChat()"
                    aria-label="Abrir asistente Zampi"
                    x-show="!open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed bottom-20 right-4 z-40 flex items-center gap-2 px-4 py-2.5 rounded-full font-semibold focus:outline-none focus:ring-4"
                    style="background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; box-shadow:0 0 20px rgba(46,80,176,0.65),0 4px 16px rgba(15,31,88,0.55); transition:transform 200ms ease, box-shadow 200ms ease;"
                    onmouseenter="this.style.transform='scale(1.05)'; this.style.boxShadow='0 0 30px rgba(46,80,176,0.9),0 4px 20px rgba(15,31,88,0.65)';"
                    onmouseleave="this.style.transform='scale(1)'; this.style.boxShadow='0 0 20px rgba(46,80,176,0.65),0 4px 16px rgba(15,31,88,0.55)';">
                <div class="zampi-float flex-shrink-0">
                    <svg width="26" height="24" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                </div>
                <span style="font-family:'Nunito',sans-serif; font-weight:800; font-size:14px;">Zampi</span>
                <template x-if="cartCount > 0">
                    <span x-text="cartCount"
                          style="position:absolute; top:-8px; right:-8px; min-width:20px; height:20px; border-radius:9999px; background:#FBBF24; color:#050B1F; font-size:11px; font-weight:900; display:flex; align-items:center; justify-content:center; padding:0 4px; font-family:'Nunito',sans-serif; box-shadow:0 0 8px rgba(251,191,36,0.6); pointer-events:none;"></span>
                </template>
            </button>

            {{-- Overlay + Panel (layout responsive vía .zampi-overlay / .zampi-panel) --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="zampi-overlay"
                 @keydown.escape.window="closeChat()"
                 aria-modal="true" role="dialog" aria-label="Asistente virtual Zampi">

                {{-- Panel: full-screen en móvil / modal en tablet / flotante en desktop --}}
                <div class="zampi-panel zampi-scrollbar"
                     style="background:radial-gradient(ellipse at 50% 20%,#0E1A38 0%,#050B1F 60%,#01040E 100%);">

                    {{-- Cabecera --}}
                    <div style="flex-shrink:0; padding:12px 16px; background:rgba(10,20,48,0.9); backdrop-filter:blur(16px); border-bottom:1px solid rgba(46,80,176,0.4); display:flex; align-items:center; gap:10px;">
                        <div class="zampi-float" style="flex-shrink:0;">
                            <svg width="38" height="35" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                        </div>
                        <div style="flex:1;">
                            <h2 style="font-family:'Nunito',sans-serif; font-weight:900; font-size:16px; color:#fff; line-height:1.2; margin:0;">Zampi</h2>
                            <p style="font-size:11px; color:#8FA8E8; letter-spacing:0.03em; margin:0;">{{ $table->name }} · {{ $table->user->business_name ?: $table->user->name }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="display:flex; align-items:center; gap:5px;">
                                <div class="zampi-pulse" style="width:7px; height:7px; border-radius:50%; background:#22C55E; box-shadow:0 0 6px #22C55E;"></div>
                                <span style="font-size:11px; color:#22C55E; font-weight:600; font-family:'Space Grotesk',sans-serif;">En línea</span>
                            </div>
                            <button type="button"
                                    @click="closeChat()"
                                    aria-label="Cerrar asistente Zampi"
                                    style="padding:6px; border-radius:50%; border:1px solid rgba(46,80,176,0.4); background:transparent; color:#8FA8E8; cursor:pointer; transition:all 150ms ease; display:flex; align-items:center; justify-content:center;"
                                    onmouseenter="this.style.background='rgba(46,80,176,0.25)'; this.style.color='#fff';"
                                    onmouseleave="this.style.background='transparent'; this.style.color='#8FA8E8';">
                                <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Log de mensajes --}}
                    <div x-ref="chatLog"
                         role="log"
                         aria-live="polite"
                         aria-label="Conversación con Zampi"
                         @wheel.stop
                         @touchmove.stop
                         class="zampi-scrollbar"
                         style="flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px;">

                        <template x-for="msg in messages" :key="msg._id">
                            <div class="zampi-msg-appear">

                                {{-- Sistema --}}
                                <template x-if="msg.type === 'system'">
                                    <div style="display:flex; justify-content:center;">
                                        <div style="background:rgba(46,80,176,0.22); border:1px solid rgba(46,80,176,0.3); color:#8FA8E8; font-size:11px; padding:5px 14px; border-radius:9999px; backdrop-filter:blur(8px); font-family:'Space Grotesk',sans-serif;"
                                             x-text="msg.text"></div>
                                    </div>
                                </template>

                                {{-- Bot --}}
                                <template x-if="msg.type === 'bot'">
                                    <div style="display:flex; gap:8px; align-items:flex-end;">
                                        <div class="zampi-avatar" style="flex-shrink:0;">
                                            <svg width="24" height="22" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                                        </div>
                                        <div style="max-width:75%; min-width:0;">
                                            <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.35); color:#C8D8FF; font-size:14px; line-height:1.6; padding:10px 14px; border-radius:18px 18px 18px 4px; box-shadow:0 2px 12px rgba(0,0,0,0.3); font-family:'Space Grotesk',sans-serif; white-space:pre-line;"
                                                 x-text="msg.text"></div>
                                            {{-- Tarjetas de producto --}}
                                            <template x-if="msg.cards && msg.cards.length">
                                                <div style="display:flex; gap:8px; margin-top:8px; overflow-x:auto; padding-bottom:4px;">
                                                    <template x-for="card in msg.cards" :key="card.id">
                                                        <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.45); border-radius:16px; padding:10px; min-width:150px; max-width:160px; flex-shrink:0; box-shadow:0 4px 20px rgba(15,31,88,0.4); transition:transform 200ms ease; display:flex; flex-direction:column;"
                                                             onmouseenter="this.style.transform='translateY(-2px)'"
                                                             onmouseleave="this.style.transform='translateY(0)'">
                                                            {{-- Foto del plato o icono de categoría --}}
                                                            <template x-if="card.image">
                                                                <img :src="card.image"
                                                                     :alt="card.name"
                                                                     style="width:100%; height:72px; object-fit:cover; border-radius:10px; margin-bottom:8px; flex-shrink:0; display:block;"
                                                                     loading="lazy"/>
                                                            </template>
                                                            <template x-if="!card.image">
                                                                <div style="height:56px; border-radius:10px; background:linear-gradient(135deg,#162648,#0A1430); display:flex; align-items:center; justify-content:center; margin-bottom:8px; flex-shrink:0;">
                                                                    <template x-if="card.foodIcon">
                                                                        <svg width="34" height="34" viewBox="0 0 24 24"
                                                                             aria-hidden="true"
                                                                             style="color:rgba(139,168,232,0.6); overflow:visible;">
                                                                            <use :href="'#fc-' + card.foodIcon.svgId"/>
                                                                        </svg>
                                                                    </template>
                                                                    <template x-if="!card.foodIcon">
                                                                        <span x-text="card.emoji" style="font-size:26px;"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            {{-- Nombre --}}
                                                            <div style="font-size:12px; font-weight:700; color:#fff; margin-bottom:3px; font-family:'Space Grotesk',sans-serif; line-height:1.3;"
                                                                 x-text="card.name"></div>
                                                            {{-- Descripción --}}
                                                            <div x-show="card.description"
                                                                 style="font-size:10px; color:#8FA8E8; margin-bottom:5px; line-height:1.4; flex-grow:1;"
                                                                 x-text="card.description"></div>
                                                            {{-- Alérgenos UE encima del precio --}}
                                                            <template x-if="card.allergens && card.allergens.length">
                                                                <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:7px;">
                                                                    <template x-for="al in card.allergens" :key="al.name">
                                                                        <div :title="al.name"
                                                                             style="display:inline-flex; align-items:center; gap:3px; background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35); border-radius:5px; padding:2px 6px 2px 4px; cursor:default;">
                                                                            {{-- Icono SVG oficial UE, o fallback ⚠ si no hay mapeo --}}
                                                                            <template x-if="al.svgId">
                                                                                <svg :aria-label="al.label" width="13" height="13"
                                                                                     style="flex-shrink:0; color:#FCD34D; overflow:visible;">
                                                                                    <use :href="'#al-' + al.svgId"/>
                                                                                </svg>
                                                                            </template>
                                                                            <template x-if="!al.svgId">
                                                                                <span style="font-size:11px; line-height:1; color:#FCD34D;">⚠</span>
                                                                            </template>
                                                                            <span x-text="al.label"
                                                                                  style="font-size:8px; color:#FCD34D; font-weight:700; font-family:'Space Grotesk',sans-serif; letter-spacing:0.02em;"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            {{-- Precio + controles cantidad --}}
                                                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                                                                <span style="font-size:13px; font-weight:700; color:#FBBF24; font-family:'Nunito',sans-serif;"
                                                                      x-text="Number(card.price).toFixed(2).replace('.',',') + ' €'"></span>
                                                                {{-- Sin cantidad: solo botón + --}}
                                                                <template x-if="cartQty(card.id) === 0">
                                                                    <button type="button"
                                                                            @click.stop="addToCart(card)"
                                                                            :aria-label="'Añadir ' + card.name + ' al pedido'"
                                                                            style="width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:17px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 8px rgba(46,80,176,0.6); flex-shrink:0; transition:all 150ms ease;"
                                                                            onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                            onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='0 0 8px rgba(46,80,176,0.6)'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                            onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                            onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                </template>
                                                                {{-- Con cantidad: controles [-] [n] [+] --}}
                                                                <template x-if="cartQty(card.id) > 0">
                                                                    <div style="display:flex; align-items:center; gap:5px;">
                                                                        <button type="button"
                                                                                @click.stop="decreaseQty(card)"
                                                                                :aria-label="'Quitar uno de ' + card.name"
                                                                                style="width:24px; height:24px; border-radius:50%; background:rgba(46,80,176,0.35); border:2px solid transparent; color:#fff; font-size:16px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 150ms ease;"
                                                                                onmouseenter="this.style.background='#2E50B0'; this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';"
                                                                                onmouseleave="this.style.background='rgba(46,80,176,0.35)'; this.style.borderColor='transparent'; this.style.boxShadow='none'; this.style.transform='scale(1)';"
                                                                                onmousedown="this.style.borderColor='#B91C1C'; this.style.boxShadow='0 0 14px rgba(185,28,28,0.8)'; this.style.transform='scale(0.9)';"
                                                                                onmouseup="this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';">−</button>
                                                                        <span x-text="cartQty(card.id)"
                                                                              style="font-size:13px; font-weight:800; color:#fff; font-family:'Nunito',sans-serif; min-width:14px; text-align:center;"></span>
                                                                        <button type="button"
                                                                                @click.stop="addToCart(card)"
                                                                                :aria-label="'Añadir otro de ' + card.name"
                                                                                style="width:24px; height:24px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:17px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 8px rgba(46,80,176,0.6); flex-shrink:0; transition:all 150ms ease;"
                                                                                onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                                onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='0 0 8px rgba(46,80,176,0.6)'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                                onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                                onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- Quick replies --}}
                                            <template x-if="msg.quickReplies && msg.quickReplies.length">
                                                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:8px;">
                                                    <template x-for="(qr, qrIdx) in msg.quickReplies" :key="qrIdx">
                                                        <button type="button"
                                                                @click="handleQuickReply(qr)"
                                                                :style="getQrStyle(qr) + 'display:inline-flex;align-items:center;gap:4px;'"
                                                                @mouseenter="onQrEnter($el, qr)"
                                                                @mouseleave="onQrLeave($el, qr)">
                                                                <template x-if="qr === 'Ver mi pedido'">
                                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                                </template>
                                                                <template x-if="qr === 'Confirmar pedido'">
                                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                </template>
                                                                <span x-text="qr"></span>
                                                            </button>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- Resumen del pedido en vivo (reactivo al store) --}}
                                            <template x-if="msg.cartLive">
                                                <div style="background:rgba(14,26,56,0.9); border:1px solid rgba(46,80,176,0.45); border-radius:16px; padding:12px; margin-top:8px; backdrop-filter:blur(12px);">
                                                    <div style="font-size:13px; font-weight:800; font-family:'Nunito',sans-serif; color:#fff; margin-bottom:8px;">🛒 Tu pedido</div>
                                                    <template x-if="$store.cart.items.length === 0">
                                                        <p style="font-size:12px; color:#8FA8E8; text-align:center; padding:8px 0;">El pedido está vacío.</p>
                                                    </template>
                                                    <template x-for="item in $store.cart.items" :key="item.productId">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid rgba(255,255,255,0.06);">
                                                            <span style="font-size:12px; color:#C8D8FF; flex:1;" x-text="item.name"></span>
                                                            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                                                {{-- Controles cantidad --}}
                                                                <button type="button"
                                                                        @click.stop="decreaseQty(item)"
                                                                        :aria-label="'Quitar uno de ' + item.name"
                                                                        style="width:22px; height:22px; border-radius:50%; background:rgba(46,80,176,0.35); border:2px solid transparent; color:#fff; font-size:15px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#2E50B0'; this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='rgba(46,80,176,0.35)'; this.style.borderColor='transparent'; this.style.boxShadow='none'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.borderColor='#B91C1C'; this.style.boxShadow='0 0 14px rgba(185,28,28,0.8)'; this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';">−</button>
                                                                <span x-text="item.quantity"
                                                                      style="font-size:12px; font-weight:800; color:#fff; min-width:14px; text-align:center; font-family:'Nunito',sans-serif;"></span>
                                                                <button type="button"
                                                                        @click.stop="addToCart(item)"
                                                                        :aria-label="'Añadir otro de ' + item.name"
                                                                        style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:15px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='none'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                {{-- Precio y eliminar --}}
                                                                <span style="font-size:11px; font-weight:600; color:#FBBF24; min-width:48px; text-align:right; font-family:'Nunito',sans-serif;"
                                                                      x-text="Number(item.price * item.quantity).toFixed(2).replace('.',',') + ' €'"></span>
                                                                <button type="button"
                                                                        @click.stop="removeCartItem(item.productId)"
                                                                        :aria-label="'Eliminar ' + item.name + ' del pedido'"
                                                                        style="width:20px; height:20px; border-radius:50%; background:rgba(220,38,38,0.2); border:1px solid rgba(220,38,38,0.4); color:#F87171; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#DC2626'; this.style.borderColor='#EF4444'; this.style.color='#fff'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='rgba(220,38,38,0.2)'; this.style.borderColor='rgba(220,38,38,0.4)'; this.style.color='#F87171'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.transform='scale(1.15)';">✕</button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="$store.cart.items.length > 0">
                                                        <div style="display:flex; justify-content:space-between; margin-top:8px; padding-top:8px; border-top:1px solid rgba(46,80,176,0.4);">
                                                            <span style="font-size:13px; font-weight:700; color:#fff;">Total</span>
                                                            <span style="font-size:16px; font-weight:900; color:#FBBF24; font-family:'Nunito',sans-serif;"
                                                                  x-text="Number($store.cart.total).toFixed(2).replace('.',',') + ' €'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Usuario --}}
                                <template x-if="msg.type === 'user'">
                                    <div style="display:flex; justify-content:flex-end;">
                                        <div style="max-width:78%;">
                                            <div style="background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; font-size:14px; line-height:1.5; padding:10px 14px; border-radius:18px 18px 4px 18px; box-shadow:0 4px 16px rgba(15,31,88,0.55); font-family:'Space Grotesk',sans-serif;"
                                                 x-text="msg.text"></div>
                                            <div style="font-size:10px; color:#3A5090; text-align:right; margin-top:3px; padding-right:4px;"
                                                 x-text="formatTime(msg.time) + ' ✓✓'"></div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        {{-- Indicador de escritura --}}
                        <template x-if="isTyping">
                            <div style="display:flex; gap:8px; align-items:flex-end;" aria-label="Zampi está escribiendo">
                                <div class="zampi-avatar" style="flex-shrink:0;">
                                    <svg width="24" height="22" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                                </div>
                                <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.35); border-radius:18px 18px 18px 4px; padding:12px 16px; display:flex; gap:5px; align-items:center;">
                                    <div class="zampi-typing-1" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                    <div class="zampi-typing-2" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                    <div class="zampi-typing-3" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                </div>
                            </div>
                        </template>

                        {{-- Error --}}
                        <template x-if="error">
                            <div style="display:flex; justify-content:center;" role="alert">
                                <div style="background:rgba(208,14,26,0.15); border:1px solid rgba(208,14,26,0.4); color:#F04040; font-size:12px; padding:5px 14px; border-radius:9999px; font-family:'Space Grotesk',sans-serif;"
                                     x-text="error"></div>
                            </div>
                        </template>

                        {{-- Conversación cerrada --}}
                        <template x-if="closed">
                            <div style="display:flex; justify-content:center;">
                                <div style="background:rgba(46,80,176,0.15); border:1px solid rgba(46,80,176,0.25); color:#8FA8E8; font-size:11px; padding:5px 14px; border-radius:9999px; font-family:'Space Grotesk',sans-serif;">
                                    Conversación finalizada. Inicia una nueva para seguir pidiendo.
                                </div>
                            </div>
                        </template>

                        {{-- Centinela de scroll -- siempre al final del log --}}
                        <div x-ref="chatEnd" style="height:1px; flex-shrink:0;" aria-hidden="true"></div>

                    </div>

                    {{-- Barra de carrito flotante --}}
                    <div x-show="chatCart.length > 0"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         style="flex-shrink:0; width:100%;">
                        <div style="width:100%; box-sizing:border-box; background:linear-gradient(135deg,#0f3d2a,#0a2e1f); border-top:2px solid #22c55e; box-shadow:0 -4px 20px rgba(34,197,94,0.2);">
                        {{-- Fila principal --}}
                        <div class="zampi-cartbar-row" style="padding:14px 16px; display:flex; align-items:center; gap:12px; overflow:visible;">
                        {{-- Resumen del carrito --}}
                        <div class="zampi-cart-left" style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                            <div style="position:relative; flex-shrink:0;">
                                <svg aria-hidden="true" width="26" height="26" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span x-text="cartCount"
                                      style="position:absolute; top:-6px; right:-6px; min-width:17px; height:17px; border-radius:9999px; background:#EF4444; color:#fff; font-size:10px; font-weight:900; display:flex; align-items:center; justify-content:center; padding:0 3px; font-family:'Nunito',sans-serif;"></span>
                            </div>
                            <span :class="cartNotifs.length > 0 ? 'zampi-hidden-mobile' : ''"
                                  style="font-size:13px; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:600;"
                                  x-text="cartCount + (cartCount === 1 ? ' artículo' : ' artículos')"></span>
                            <span :class="cartNotifs.length > 0 ? 'zampi-hidden-mobile' : ''"
                                  style="font-size:15px; font-weight:900; color:#FBBF24; font-family:'Nunito',sans-serif; flex-shrink:0;"
                                  x-text="Number(cartTotal).toFixed(2).replace('.',',') + ' €'"></span>
                            {{-- Notificaciones de eliminación acumulables (máx 3) --}}
                            <div class="zampi-notif-bar" :class="cartNotifs.length > 0 ? 'zampi-notif-bar-active' : ''"
                                 style="display:flex; gap:5px; flex-shrink:0; flex-wrap:nowrap;" role="status" aria-live="polite">
                                <template x-for="notif in cartNotifs" :key="notif.id">
                                    <div :class="notif.leaving ? 'zampi-notif-pill zampi-notif-pill-out' : 'zampi-notif-pill zampi-notif-pill-in'">
                                        <span style="font-size:12px;" aria-hidden="true">🗑️</span>
                                        <span class="zampi-notif-name" style="font-size:11px; font-weight:600; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap;" x-text="notif.name"></span>
                                        <span class="zampi-notif-suffix" style="font-size:11px; font-weight:600; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap;"> eliminado</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        {{-- Botón único: abre el panel de pedido del menú digital --}}
                        <div style="display:flex; gap:8px; flex-shrink:0;">
                            <button type="button"
                                    @click="$store.cart.open = true"
                                    aria-label="Ver y confirmar pedido"
                                    style="padding:0; border-radius:9999px; background:#16A34A; border:none; color:#fff; font-size:13px; font-weight:700; font-family:'Space Grotesk',sans-serif; cursor:pointer; white-space:nowrap; transition:background 150ms ease; box-shadow:0 4px 14px rgba(22,163,74,0.45);"
                                    onmouseenter="this.style.background='#15803D';"
                                    onmouseleave="this.style.background='#16A34A';">
                                <span style="display:flex; flex-direction:row; align-items:center; gap:6px; padding:8px 18px; line-height:1;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Ver pedido</span>
                                </span>
                            </button>
                        </div>
                        </div>{{-- /Fila principal --}}
                        </div>{{-- /Cart bar inner --}}
                    </div>

                    {{-- Área de input --}}
                    <div style="flex-shrink:0; padding:10px 12px; background:rgba(5,11,31,0.95); backdrop-filter:blur(16px); border-top:1px solid rgba(46,80,176,0.3);">
                        <div style="display:flex; gap:8px; align-items:center; background:rgba(14,26,56,0.7); border:1px solid rgba(96,152,248,0.3); border-radius:9999px; padding:8px 8px 8px 16px;">
                            <label for="zampi-chat-input" class="sr-only">Escribe tu mensaje a Zampi</label>
                            <input id="zampi-chat-input"
                                   type="text"
                                   x-ref="chatInput"
                                   x-model="input"
                                   @keydown.enter="sendMessage()"
                                   :disabled="sending || closed"
                                   placeholder="Escribe tu pedido..."
                                   style="flex:1; background:none; border:none; outline:none; font-family:'Space Grotesk',sans-serif; font-size:14px; color:#C8D8FF; min-width:0;">
                            <button type="button"
                                    @click="sendMessage()"
                                    :disabled="!input.trim() || sending || closed"
                                    aria-label="Enviar mensaje a Zampi"
                                    style="width:38px; height:38px; border-radius:50%; border:none; cursor:pointer; background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; font-size:18px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 14px rgba(46,80,176,0.65); flex-shrink:0; transition:transform 150ms cubic-bezier(0.34,1.56,0.64,1);"
                                    onmousedown="this.style.transform='scale(0.92)'"
                                    onmouseup="this.style.transform='scale(1)'"
                                    onmouseleave="this.style.transform='scale(1)'">↑</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>{{-- /chatWidget --}}

        {{-- ── FAB Solicitar cuenta ────────────────────────────────── --}}
        <div class="fixed bottom-6 left-4 z-50"
             x-show="$store.bill.active && !$store.chat.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-75">
            <button type="button"
                    @click="$store.bill.open()"
                    :disabled="$store.bill.requested || $store.bill.sending"
                    aria-label="Solicitar la cuenta"
                    class="flex items-center gap-2 px-4 py-3 rounded-full shadow-xl font-bold text-sm
                           transition-colors focus:outline-none focus:ring-4 focus:ring-indigo-400
                           disabled:opacity-70 disabled:cursor-not-allowed"
                    :class="$store.bill.requested
                        ? 'bg-green-600 text-white'
                        : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
                <template x-if="!$store.bill.requested && !$store.bill.sending">
                    <span class="flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        Pedir la cuenta
                    </span>
                </template>
                <template x-if="$store.bill.sending">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Enviando...
                    </span>
                </template>
                <template x-if="$store.bill.requested">
                    <span class="flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="$store.bill.paymentDone ? '¡Pago completado!' : ($store.bill.method === 'cash' ? 'Cuenta solicitada · Efectivo' : 'Cuenta solicitada · Tarjeta')"></span>
                    </span>
                </template>
            </button>
            <template x-if="$store.bill.error">
                <p class="mt-1 text-xs text-red-600 bg-white rounded px-2 py-1 shadow"
                   role="alert"
                   x-text="$store.bill.error"></p>
            </template>
        </div>

        {{-- ── Sheet: elección de método de pago ───────────────────── --}}
        <div x-show="$store.bill.choosing"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.close()"
             @keydown.escape.window="$store.bill.close()"
             aria-modal="true" role="dialog" aria-label="Elige cómo pagar">

            <div x-show="$store.bill.choosing"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-1">
                    Solicitar la cuenta
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    ¿Cómo quieres pagar?
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            @click="$store.bill.requestCash()"
                            class="flex flex-col items-center justify-center gap-2 py-5 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="text-3xl" aria-hidden="true">💵</span>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Efectivo</span>
                    </button>

                    <button type="button"
                            @click="$store.bill.openCardPayment()"
                            class="flex flex-col items-center justify-center gap-2 py-5 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="text-3xl" aria-hidden="true">💳</span>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Tarjeta</span>
                    </button>
                </div>

                <button type="button"
                        @click="$store.bill.close()"
                        class="w-full mt-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /sheet --}}

        {{-- ── Sheet: propina (antes del pago con tarjeta) ───────── --}}
        <div x-show="$store.bill.showingTip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeTip()"
             @keydown.escape.window="$store.bill.closeTip()"
             aria-modal="true" role="dialog" aria-label="Añadir propina">

            <div x-show="$store.bill.showingTip"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                {{-- Cabecera --}}
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">¿Quieres dejar propina?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">La propina es completamente opcional</p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeTip()"
                            aria-label="Cancelar propina"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Resumen del pedido --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total del pedido</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                {{-- Botones de porcentaje --}}
                <div class="grid grid-cols-4 gap-2 mb-4" role="group" aria-label="Porcentaje de propina">
                    <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                        <button type="button"
                                @click="$store.bill.setTipPercent(pct)"
                                :aria-pressed="$store.bill.tipPercent === pct"
                                :class="$store.bill.tipPercent === pct
                                    ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-400'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                                class="flex flex-col items-center py-3 rounded-xl border-2 font-semibold text-sm
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span x-text="pct + '%'"></span>
                            <span class="text-xs font-normal mt-0.5 text-gray-500 dark:text-gray-400"
                                  x-text="(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.', ',') + ' €'"></span>
                        </button>
                    </template>
                </div>

                {{-- Input de propina personalizada --}}
                <div class="relative mb-5">
                    <label for="custom-tip-input" class="sr-only">Propina personalizada en euros</label>
                    <input type="number"
                           id="custom-tip-input"
                           min="0"
                           step="0.50"
                           placeholder="Otro importe"
                           @input="$store.bill.updateCustomTip($event.target.value)"
                           :value="$store.bill.tipPercent === null && $store.bill.tipAmount > 0 ? $store.bill.tipAmount : ''"
                           aria-describedby="custom-tip-hint"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                  px-4 py-3 pr-10 text-right placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium pointer-events-none"
                          aria-hidden="true">€</span>
                </div>
                <p id="custom-tip-hint" class="sr-only">Escribe un importe personalizado en euros</p>

                {{-- Desglose total + propina --}}
                <div class="space-y-1 mb-5">
                    <template x-if="$store.bill.tipAmount > 0">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"
                                  x-text="'+ ' + $store.bill.tipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-white">Total a pagar</span>
                        <span class="text-indigo-600 dark:text-indigo-400"
                              x-text="$store.bill.grandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                {{-- Botón continuar --}}
                <button type="button"
                        @click="$store.bill.proceedToStripe()"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="$store.bill.tipAmount > 0">
                        💳 Pagar <span x-text="$store.bill.grandTotal.toFixed(2).replace('.', ',') + ' €'"></span> con propina
                    </span>
                    <span x-show="$store.bill.tipAmount <= 0">
                        💳 Pagar sin propina
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeTip()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /tip sheet --}}

        {{-- ── Sheet: pago con tarjeta (Stripe Elements) ──────────── --}}
        <div x-show="$store.bill.payingCard"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @keydown.escape.window="$store.bill.closeCardPayment()"
             aria-modal="true" role="dialog" aria-label="Pago con tarjeta">

            <div x-show="$store.bill.payingCard"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4 max-h-[92dvh] overflow-y-auto">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                {{-- Cabecera --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pago con tarjeta</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Total:
                            <span class="font-semibold text-gray-900 dark:text-white"
                                  x-text="$store.bill.stripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                        </p>
                        <template x-if="$store.bill.tipAmount > 0">
                            <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">
                                Incluye propina de
                                <span x-text="$store.bill.tipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                            </p>
                        </template>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeCardPayment()"
                            aria-label="Cerrar pago"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Spinner mientras carga Stripe Elements --}}
                <div x-show="!$store.bill.stripeReady && !$store.bill.stripeError"
                     class="flex items-center justify-center py-10">
                    <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" aria-label="Cargando formulario de pago">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- Formulario Stripe Elements --}}
                <div id="stripe-payment-element" class="mb-4"></div>

                {{-- Error de Stripe --}}
                <template x-if="$store.bill.stripeError">
                    <p class="mb-3 text-sm text-red-600 dark:text-red-400 font-medium text-center"
                       role="alert"
                       x-text="$store.bill.stripeError"></p>
                </template>

                {{-- Botón pagar --}}
                <button type="button"
                        x-show="$store.bill.stripeReady"
                        @click="$store.bill.submitCardPayment()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="!$store.bill.sending">
                        💳 Pagar <span x-text="$store.bill.stripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
                    Pago seguro procesado por
                    <span class="font-semibold text-indigo-500">Stripe</span>
                    &middot; Tarjeta de prueba: 4242 4242 4242 4242
                </p>
            </div>
        </div>{{-- /stripe sheet --}}

        {{-- ── FAB Carrito ─────────────────────────────────────────── --}}
        <div class="fixed bottom-6 right-4 z-50"
             x-show="$store.cart.count > 0 && !$store.chat.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-75">
            <button type="button"
                    @click="$store.cart.open = true"
                    class="relative flex items-center gap-2 px-5 py-3 rounded-full
                           bg-green-600 hover:bg-green-700 text-white font-bold shadow-xl
                           transition-colors focus:outline-none focus:ring-4 focus:ring-green-400">
                <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Ver pedido
                <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"
                      x-text="$store.cart.count"></span>
            </button>
        </div>

        {{-- ── Drawer del carrito ───────────────────────────────────── --}}
        <div x-show="$store.cart.open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.cart.open = false"
             aria-modal="true" role="dialog" aria-label="Tu pedido">

            <div x-show="$store.cart.open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 max-h-[90dvh] flex flex-col
                        bg-white dark:bg-gray-900 rounded-t-2xl shadow-2xl overflow-hidden">

                {{-- Handle + cabecera --}}
                <div class="flex-shrink-0 px-4 pt-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-3"></div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            Tu pedido
                            <span class="ml-1 text-sm font-normal text-gray-400">
                                (<span x-text="$store.cart.count"></span> <span x-text="$store.cart.count === 1 ? 'artículo' : 'artículos'"></span>)
                            </span>
                        </h2>
                        <button type="button"
                                @click="$store.cart.open = false"
                                class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800
                                       focus:outline-none focus:ring-2 focus:ring-gray-400">
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Lista de items --}}
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
                    <template x-for="item in $store.cart.items" :key="item.productId">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-3">

                            {{-- Fila producto --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm leading-snug" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="$store.cart.fmt(item.price) + ' / ud.'"></p>
                                </div>

                                {{-- Cantidad --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button type="button" @click="$store.cart.dec(item.productId)"
                                            class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600
                                                   flex items-center justify-center text-gray-600 dark:text-gray-300
                                                   hover:border-red-400 hover:text-red-500 transition-colors
                                                   focus:outline-none focus:ring-2 focus:ring-red-400">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-5 text-center font-bold text-gray-900 dark:text-white text-sm" x-text="item.quantity"></span>
                                    <button type="button" @click="$store.cart.inc(item.productId)"
                                            class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600
                                                   flex items-center justify-center text-gray-600 dark:text-gray-300
                                                   hover:border-green-500 hover:text-green-600 transition-colors
                                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Subtotal --}}
                                <span class="flex-shrink-0 font-bold text-green-600 dark:text-green-400 text-sm"
                                      x-text="$store.cart.fmt($store.cart.lineTotal(item))"></span>
                            </div>

                            {{-- Modificaciones --}}
                            <template x-if="item.removable.length > 0 || item.extras.length > 0">
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-2">

                                    <template x-if="item.removable.length > 0">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Quitar</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ing in item.removable" :key="ing.id">
                                                    <button type="button"
                                                            @click="$store.cart.toggleRemove(item.productId, ing)"
                                                            :class="$store.cart.hasMod(item.productId, ing.id, 'remove')
                                                                ? 'bg-red-100 dark:bg-red-900/40 border-red-400 text-red-700 dark:text-red-300'
                                                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium transition-colors
                                                                   focus:outline-none focus:ring-2 focus:ring-red-400">
                                                        <span x-text="'Sin ' + ing.name"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="item.extras.length > 0">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Extra</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ing in item.extras" :key="ing.id">
                                                    <button type="button"
                                                            @click="$store.cart.toggleExtra(item.productId, ing)"
                                                            :class="$store.cart.hasMod(item.productId, ing.id, 'add')
                                                                ? 'bg-green-100 dark:bg-green-900/40 border-green-500 text-green-700 dark:text-green-300'
                                                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium transition-colors
                                                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                                                        <span x-text="'+ ' + ing.name + (ing.price > 0 ? ' (+' + ing.price.toFixed(2).replace(\'.\',\',\') + \' €)\' : \'\'  )"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200 dark:border-gray-700 space-y-3 bg-white dark:bg-gray-900">

                    <template x-if="$store.cart.error">
                        <p class="text-sm text-red-600 dark:text-red-400 text-center font-medium" x-text="$store.cart.error"></p>
                    </template>

                    <div class="flex items-center justify-between">
                        <span class="text-base font-semibold text-gray-700 dark:text-gray-300">Total</span>
                        <span class="text-xl font-bold text-gray-900 dark:text-white" x-text="$store.cart.fmt($store.cart.total)"></span>
                    </div>

                    <button type="button"
                            @click="$store.cart.send()"
                            :disabled="$store.cart.sending"
                            class="w-full py-3.5 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-60
                                   text-white font-bold text-base shadow-sm transition-colors
                                   focus:outline-none focus:ring-4 focus:ring-green-400">
                        <span x-show="!$store.cart.sending">🍽️ Enviar pedido a cocina</span>
                        <span x-show="$store.cart.sending" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Enviando...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Confirmación pedido enviado ─────────────────────────── --}}
        <div x-show="$store.cart.sent"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <svg class="w-9 h-9 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">¡Pedido enviado!</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tu pedido está en camino a cocina. En breve lo tendrás listo.</p>
                <button type="button"
                        @click="$store.cart.sent = false"
                        class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold transition-colors
                               focus:outline-none focus:ring-4 focus:ring-green-400">
                    Aceptar
                </button>
            </div>
        </div>

        {{-- ── Barra de filtros ─────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm"
             role="region" aria-label="Filtros de la carta">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-2">

                {{-- Grupo: Destino --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1"
                     role="group" aria-label="Filtrar por origen">
                    <span class="flex-shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Origen
                    </span>

                    <button type="button"
                            @click="setDestination('kitchen')"
                            :aria-pressed="activeDestination === 'kitchen'"
                            :class="activeDestination === 'kitchen'
                                ? 'bg-orange-500 dark:bg-orange-600 text-white border-orange-500 dark:border-orange-600'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-orange-400 hover:text-orange-600 dark:hover:text-orange-400'"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-sm font-medium border transition-colors duration-150
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Cocina
                    </button>

                    <button type="button"
                            @click="setDestination('bar')"
                            :aria-pressed="activeDestination === 'bar'"
                            :class="activeDestination === 'bar'
                                ? 'bg-amber-500 dark:bg-amber-600 text-white border-amber-500 dark:border-amber-600'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-amber-400 hover:text-amber-600 dark:hover:text-amber-400'"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-sm font-medium border transition-colors duration-150
                                   focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 3a1 1 0 000 2h1.5l1.4 7H3a1 1 0 000 2h1.2l.4 2H3a1 1 0 100 2h14a1 1 0 100-2h-1.6l.4-2H17a1 1 0 000-2h-3.9L14.5 5H16a1 1 0 000-2H3z"/>
                        </svg>
                        Barra
                    </button>
                </div>

                {{-- Grupo: Alérgenos --}}
                @if ($allergens->isNotEmpty())
                    <div x-show="visibleAllergenKeys.length > 0"
                         class="flex items-center flex-wrap gap-2 py-1"
                         role="group" aria-label="Filtrar por alérgenos ausentes">
                        <span class="flex-shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Sin
                        </span>

                        @foreach ($allergens as $allergen)
                            @php
                                $allergenKey  = $allergen->allergen_type ? "'{$allergen->allergen_type}'" : "'name:{$allergen->name}'";
                                $allergenName = $allergen->allergenTypeName() ?? $allergen->name;
                            @endphp
                            <div class="relative" x-data="{ tip: false }"
                                 x-show="visibleAllergenKeys.includes({{ $allergenKey }})">
                                <button type="button"
                                        @click="toggleAllergen({{ $allergenKey }})"
                                        @mouseenter="tip = true"
                                        @mouseleave="tip = false"
                                        @touchstart.passive="tip = true; setTimeout(() => tip = false, 1400)"
                                        :aria-pressed="activeAllergens.includes({{ $allergenKey }})"
                                        :class="activeAllergens.includes({{ $allergenKey }})
                                            ? 'border-orange-500 opacity-100 ring-2 ring-orange-400 ring-offset-2 ring-offset-gray-900'
                                            : 'border-transparent opacity-60 hover:opacity-100'"
                                        class="p-1 rounded-full border-2 border-transparent transition-all duration-200 cursor-pointer
                                               focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                               dark:focus:ring-offset-gray-900"
                                        aria-label="{{ $allergenName }}">
                                    @if($allergen->allergen_type)
                                        <div class="h-12 w-12 rounded-full overflow-hidden">
                                            <img src="{{ $allergen->allergenIconPath() }}"
                                                 alt="{{ $allergenName }}"
                                                 class="h-full w-full object-contain">
                                        </div>
                                    @else
                                        <div class="h-12 w-12 flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                            <span class="text-2xl" aria-hidden="true">⚠️</span>
                                        </div>
                                    @endif
                                </button>

                                <div x-show="tip"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1
                                            text-xs font-medium text-white bg-gray-900 dark:bg-gray-700
                                            rounded-md whitespace-nowrap shadow-lg z-20 pointer-events-none"
                                     role="tooltip">
                                    {{ $allergenName }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Contador + limpiar --}}
                <div class="flex items-center justify-between min-h-[1.5rem]">
                    <p class="text-xs text-gray-400 dark:text-gray-500"
                       role="status" aria-live="polite" aria-atomic="true">
                        <span x-text="visibleCount"></span>&nbsp;<span x-text="visibleCount === 1 ? 'producto' : 'productos'"></span> visibles
                    </p>
                    <button type="button"
                            x-show="hasActiveFilters"
                            x-transition
                            @click="clearAll()"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400
                                   hover:text-indigo-800 dark:hover:text-indigo-200
                                   focus:outline-none focus:underline transition-colors">
                        Limpiar filtros
                    </button>
                </div>

            </div>
        </div>

        {{-- ── Filtro de categorías ────────────────────────────────────── --}}
        @if ($categories->isNotEmpty())
            <nav aria-label="Filtrar por categoría"
                 class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    <ul class="flex gap-1 py-2" role="list">

                        {{-- Chip "Todas" --}}
                        <li>
                            <button type="button"
                                    @click="setCategory(null)"
                                    :aria-pressed="activeCategory === null"
                                    :class="activeCategory === null
                                        ? 'bg-indigo-600 text-white'
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300'"
                                    class="inline-block whitespace-nowrap px-3 py-1.5 rounded-full text-sm font-medium
                                           transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500
                                           focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                Todas
                            </button>
                        </li>

                        @foreach ($categories as $category)
                            <li x-show="isCategoryVisible({{ $category->id }})">
                                <button type="button"
                                        @click="setCategory({{ $category->id }})"
                                        :aria-pressed="activeCategory === {{ $category->id }}"
                                        :class="activeCategory === {{ $category->id }}
                                            ? 'bg-indigo-600 text-white'
                                            : 'text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300'"
                                        class="inline-block whitespace-nowrap px-3 py-1.5 rounded-full text-sm font-medium
                                               transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500
                                               focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    {{ $category->name }}
                                </button>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </nav>
        @endif

        {{-- ── Contenido principal ──────────────────────────────────── --}}
        <main id="main-content" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-10">

            {{-- ── Menú del Día ──────────────────────────────────────── --}}
            <x-daily-menu-banner :hash="$table->unique_hash" />

            {{-- ── Banner cocina cerrada ──────────────────────────────── --}}
            @if(!$kitchenOpen)
            <div role="status"
                 class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span aria-hidden="true" class="text-2xl leading-none">🕐</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                        {{ __('La cocina está cerrada en este momento') }}
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">
                        {{ __('Ahora solo se sirven bebidas.') }}
                        @if($nextOpeningTime)
                            {{ __('La cocina abre a las') }}
                            <span class="font-semibold">{{ $nextOpeningTime }}</span>.
                        @endif
                    </p>
                </div>
            </div>
            @endif

            {{-- Estado vacío cuando los filtros excluyen todo --}}
            <div x-show="hasActiveFilters && visibleCount === 0"
                 x-transition
                 class="text-center py-16"
                 role="status" aria-live="polite">
                <svg aria-hidden="true" class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <p class="text-lg font-medium text-gray-400 dark:text-gray-500">
                    Ningún producto coincide con los filtros seleccionados.
                </p>
                <button type="button"
                        @click="clearAll()"
                        class="mt-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline
                               focus:outline-none focus:underline">
                    Ver todos los productos
                </button>
            </div>

            @forelse ($categories as $category)
                <section x-show="isCategoryVisible({{ $category->id }})"
                         x-transition
                         aria-labelledby="titulo-categoria-{{ $category->id }}"
                         id="categoria-{{ $category->id }}">

                    {{-- Encabezado de categoría --}}
                    <div class="flex items-center gap-3 mb-4 px-3 py-2 rounded-lg
                                bg-white dark:bg-gray-800
                                border-l-4 border-indigo-500 dark:border-indigo-400
                                shadow-sm">
                        <h2 id="titulo-categoria-{{ $category->id }}"
                            class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $category->name }}
                        </h2>

                        @if ($category->destination === 'bar')
                            <span class="inline-flex items-center gap-1 text-xs font-medium
                                         bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300
                                         px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-700"
                                  aria-label="Servida desde la barra">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 3a1 1 0 000 2h1.5l1.4 7H3a1 1 0 000 2h1.2l.4 2H3a1 1 0 100 2h14a1 1 0 100-2h-1.6l.4-2H17a1 1 0 000-2h-3.9L14.5 5H16a1 1 0 000-2H3z"/>
                                </svg>
                                Barra
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium
                                         bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300
                                         px-2 py-0.5 rounded-full border border-orange-200 dark:border-orange-700"
                                  aria-label="Preparada en cocina">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                          clip-rule="evenodd"/>
                                </svg>
                                Cocina
                            </span>
                        @endif
                    </div>

                    {{-- Lista de productos --}}
                    <ul class="space-y-3" role="list" aria-label="Productos de {{ $category->name }}">
                        @foreach ($category->products as $product)
                            <li x-show="isProductVisible({{ $product->id }})"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700
                                       shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                                <div class="flex gap-3 sm:gap-4 p-3 sm:p-4">

                                    {{-- Imagen --}}
                                    @if ($product->image)
                                        <div class="flex-shrink-0">
                                            <img src="{{ Storage::url($product->image) }}"
                                                 alt="Foto de {{ $product->name }}"
                                                 class="h-20 w-20 sm:h-24 sm:w-24 object-cover rounded-lg">
                                        </div>
                                    @endif

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-semibold text-base sm:text-lg leading-snug text-gray-900 dark:text-gray-100">
                                                {{ $product->name }}
                                            </h3>
                                            <span class="flex-shrink-0 font-bold text-base sm:text-lg
                                                         text-indigo-600 dark:text-indigo-400"
                                                  aria-label="Precio: {{ number_format($product->price, 2, ',', '.') }} euros">
                                                {{ number_format($product->price, 2, ',', '.') }}&nbsp;€
                                            </span>
                                        </div>

                                        @if ($product->description)
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                {{ $product->description }}
                                            </p>
                                        @endif

                                        {{-- Alérgenos --}}
                                        @if ($product->ingredients->where('is_allergen', true)->isNotEmpty())
                                            <div class="mt-2" aria-label="Alérgenos de {{ $product->name }}">
                                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">
                                                    Alérgenos
                                                </p>
                                                <ul class="flex flex-wrap gap-3" role="list">
                                                    @foreach ($product->ingredients->where('is_allergen', true)->unique('allergen_type') as $allergen)
                                                        <li><x-allergen-badge :ingredient="$allergen" /></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- Botón añadir al carrito --}}
                                        <div class="mt-3 flex justify-end">
                                            <button type="button"
                                                    @click="$store.cart.add(products.find(p => p.id === {{ $product->id }}))"
                                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full
                                                           bg-green-600 hover:bg-green-700 active:scale-95
                                                           text-white text-sm font-semibold shadow-sm
                                                           transition-all duration-150
                                                           focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Añadir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <div class="text-center py-20" role="status">
                    <svg aria-hidden="true" class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-400 dark:text-gray-500">
                        La carta no está disponible en este momento.
                    </p>
                </div>
            @endforelse

        </main>

        {{-- ── Banner de Tapas disponibles ─────────────────────────── --}}
        @if($tapaConfig && $barItemsCount > 0)
        <section
            aria-label="Tapas disponibles"
            class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mb-8"
        >
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span aria-hidden="true" class="text-2xl leading-none">🍽️</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ __('¡Tienes') }}
                        <span class="font-bold text-base">{{ $barItemsCount }}</span>
                        {{ Str::plural('tapa', $barItemsCount) }} {{ __('disponible') }}{{ $barItemsCount > 1 ? 's' : '' }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        @if($tapaConfig->tapas_free)
                            {{ __('Las tapas son gratuitas. Al añadir una bebida te sugeriremos tu tapa.') }}
                        @else
                            {{ __('Precio por tapa:') }}
                            <span class="font-semibold">{{ number_format($tapaConfig->tapa_price ?? 0, 2) }} €</span>
                        @endif
                        &bull; {{ __('Máximo') }} {{ $tapaConfig->max_tapa_variants }} {{ Str::plural('variante', $tapaConfig->max_tapa_variants) }} {{ __('distintas.') }}
                        @if($tapaConfig->extra_tapa_enabled)
                            &bull; {{ __('Tapa extra disponible por') }}
                            <span class="font-semibold">{{ number_format($tapaConfig->extra_tapa_price ?? 0, 2) }} €</span>.
                        @endif
                    </p>
                </div>
            </div>
        </section>
        @endif

        {{-- ── Modal sugerencia de tapa ────────────────────────────── --}}
        <div x-show="$store.cart.showTapaModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.cart.closeTapaModal()"
             @keydown.escape.window="$store.cart.closeTapaModal()"
             aria-modal="true"
             role="dialog"
             aria-label="Elige tu tapa"
             style="display:none">

            <div x-show="$store.cart.showTapaModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4 max-h-[85dvh] overflow-y-auto">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-4"></div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    🍽️ {{ __('¿Quieres una tapa?') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                   x-text="'Te quedan ' + ($store.cart.tapaConfig.maxVariants - $store.cart._variantsUsed) + ' variante(s) disponible(s).'">
                </p>

                <ul class="space-y-2 mb-4" aria-label="Tapas disponibles">
                    <template x-for="tapa in $store.cart.tapaProducts" :key="tapa.id">
                        <li>
                            <button type="button"
                                    @click="$store.cart.addTapa(tapa)"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl
                                           bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700
                                           hover:bg-amber-100 dark:hover:bg-amber-800/30 transition-colors
                                           focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <span class="font-medium text-gray-900 dark:text-white" x-text="tapa.name"></span>
                                <span class="text-sm font-semibold text-amber-700 dark:text-amber-300"
                                      x-text="$store.cart.tapaConfig.free ? 'Gratis' : ($store.cart.tapaConfig.tapaPrice.toFixed(2).replace('.', ',') + ' €')">
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>

                {{-- Tapa extra (si está habilitada) --}}
                <template x-if="$store.cart.tapaConfig.extraEnabled">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('Tapa extra (no consume variante)') }} —
                            <span x-text="$store.cart.tapaConfig.extraPrice.toFixed(2).replace('.', ',') + ' €'"></span>
                        </p>
                        <template x-for="tapa in $store.cart.tapaProducts" :key="'extra-' + tapa.id">
                            <button type="button"
                                    @click="$store.cart.add({
                                        id: tapa.id,
                                        name: tapa.name + ' (extra)',
                                        price: $store.cart.tapaConfig.extraPrice,
                                        destination: 'kitchen',
                                        removable: [],
                                        extras: []
                                    }); $store.cart.closeTapaModal()"
                                    class="w-full mb-2 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600
                                           text-sm text-left text-gray-700 dark:text-gray-300
                                           hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400
                                           transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    x-text="tapa.name + ' (+' + $store.cart.tapaConfig.extraPrice.toFixed(2).replace('.', ',') + ' €)'">
                            </button>
                        </template>
                    </div>
                </template>

                <button type="button"
                        @click="$store.cart.closeTapaModal()"
                        class="w-full py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200
                               focus:outline-none focus:underline transition-colors">
                    {{ __('Ahora no') }}
                </button>
            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────────── --}}
        <footer class="mt-12 border-t border-gray-200 dark:border-gray-800 py-6 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-600">
                Carta digital generada con
                <span class="font-semibold text-indigo-500 dark:text-indigo-400">Zampa</span>
            </p>
        </footer>

    </div>{{-- /x-data --}}
</body>
</html>
