<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta — {{ $table->user->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="stripe-key" content="{{ $stripePublicKey }}">
    <script src="https://js.stripe.com/v3/" defer></script>

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

        $tapaProductsForAlpine = $tapaProducts->map(fn ($p) => [
            'id'    => $p->id,
            'name'  => $p->name,
            'price' => (float) $p->price,
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
                    const price = this.tapaConfig.free ? 0 : this.tapaConfig.tapaPrice;
                    this.add({
                        id:          tapaProduct.id,
                        name:        tapaProduct.name,
                        price:       price,
                        destination: 'kitchen',
                        removable:   [],
                        extras:      [],
                    });
                    this._variantsUsed++;
                    this.showTapaModal = false;
                },

                inc(idx) { this.items[idx].quantity++; },

                dec(idx) {
                    this.items[idx].quantity--;
                    if (this.items[idx].quantity <= 0) this.items.splice(idx, 1);
                },

                toggleRemove(idx, ing) {
                    const i = this.items[idx].mods.findIndex(m => m.ingredientId === ing.id && m.action === 'remove');
                    if (i === -1) this.items[idx].mods.push({ ingredientId: ing.id, name: ing.name, action: 'remove', amountCharged: 0 });
                    else          this.items[idx].mods.splice(i, 1);
                },

                toggleExtra(idx, ing) {
                    const i = this.items[idx].mods.findIndex(m => m.ingredientId === ing.id && m.action === 'add');
                    if (i === -1) this.items[idx].mods.push({ ingredientId: ing.id, name: ing.name, action: 'add', amountCharged: ing.price });
                    else          this.items[idx].mods.splice(i, 1);
                },

                hasMod(idx, ingId, action) {
                    return this.items[idx].mods.some(m => m.ingredientId === ingId && m.action === action);
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

            // ── Widget de chat IA ────────────────────────────────────────
            Alpine.data('chatWidget', () => ({
                open:           false,
                conversationId: null,
                messages:       [],
                input:          '',
                sending:        false,
                closed:         false,
                error:          null,

                get tableHash() {
                    return Alpine.store('cart').tableHash;
                },

                async openChat() {
                    this.open = true;
                    if (!this.conversationId) {
                        await this.startConversation();
                    }
                    this.$nextTick(() => {
                        this.$refs.chatInput?.focus();
                        this.scrollBottom();
                    });
                },

                closeChat() {
                    this.open = false;
                },

                async startConversation() {
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
                        if (res.ok && data.success) {
                            this.conversationId = data.data.conversation_id;
                            this.messages.push({
                                role:    'assistant',
                                content: '¡Hola! Soy el asistente virtual de este restaurante. ¿En qué puedo ayudarte con la carta?',
                            });
                        }
                    } catch {
                        this.error = 'No se pudo iniciar el chat. Inténtalo de nuevo.';
                    }
                },

                async sendMessage() {
                    const text = this.input.trim();
                    if (!text || this.sending || this.closed || !this.conversationId) return;

                    this.input = '';
                    this.messages.push({ role: 'user', content: text });
                    this.sending = true;
                    this.error   = null;
                    this.$nextTick(() => this.scrollBottom());

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
                        if (res.ok && data.success) {
                            this.messages.push({ role: 'assistant', content: data.data.reply });
                            if (data.data.closed) {
                                this.closed = true;
                            }
                        } else {
                            this.error = data.message ?? 'Error al enviar el mensaje.';
                        }
                    } catch {
                        this.error = 'Error de conexión. Inténtalo de nuevo.';
                    } finally {
                        this.sending = false;
                        this.$nextTick(() => this.scrollBottom());
                    }
                },

                scrollBottom() {
                    if (this.$refs.chatLog) {
                        this.$refs.chatLog.scrollTop = this.$refs.chatLog.scrollHeight;
                    }
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

        {{-- ── FAB Chat IA ─────────────────────────────────────────── --}}
        <div x-data="chatWidget()">
            <button type="button"
                    @click="openChat()"
                    aria-label="Abrir asistente virtual"
                    class="fixed bottom-20 right-4 z-40 flex items-center gap-2 px-4 py-2.5 rounded-full
                           bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-xl
                           transition-colors focus:outline-none focus:ring-4 focus:ring-indigo-400">
                <span aria-hidden="true" class="text-lg leading-none">💬</span>
                <span class="text-sm">Pregúntame</span>
            </button>

            {{-- Drawer del chat --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
                 @click.self="closeChat()"
                 @keydown.escape.window="closeChat()"
                 aria-modal="true" role="dialog" aria-label="Asistente virtual">

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     class="absolute bottom-0 left-0 right-0 h-[80dvh] flex flex-col
                            bg-white dark:bg-gray-900 rounded-t-2xl shadow-2xl overflow-hidden">

                    {{-- Cabecera --}}
                    <div class="flex-shrink-0 px-4 pt-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-3"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span aria-hidden="true" class="text-xl">💬</span>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Asistente virtual</h2>
                            </div>
                            <button type="button"
                                    @click="closeChat()"
                                    aria-label="Cerrar asistente"
                                    class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                           dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Log de mensajes --}}
                    <div x-ref="chatLog"
                         role="log"
                         aria-live="polite"
                         aria-label="Conversación con el asistente"
                         class="flex-1 overflow-y-auto px-4 py-4 space-y-3">

                        <template x-for="(msg, idx) in messages" :key="idx">
                            <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                                <div :class="msg.role === 'user'
                                        ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm'
                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl rounded-bl-sm'"
                                     class="max-w-[80%] px-4 py-2.5 text-sm leading-relaxed"
                                     x-text="msg.content">
                                </div>
                            </div>
                        </template>

                        {{-- Indicador de escritura --}}
                        <template x-if="sending">
                            <div class="flex justify-start" aria-label="El asistente está escribiendo">
                                <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl rounded-bl-sm px-4 py-3 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:-0.3s"></span>
                                    <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:-0.15s"></span>
                                    <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Error --}}
                        <template x-if="error">
                            <p class="text-center text-sm text-red-500 dark:text-red-400 py-1"
                               role="alert"
                               x-text="error"></p>
                        </template>

                        {{-- Conversación cerrada --}}
                        <template x-if="closed">
                            <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-2 italic">
                                Conversación finalizada. Confirma tu pedido o inicia una nueva.
                            </p>
                        </template>
                    </div>

                    {{-- Área de input --}}
                    <div class="flex-shrink-0 px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                        <div class="flex items-end gap-2">
                            <label for="chat-input" class="sr-only">Escribe tu mensaje</label>
                            <textarea id="chat-input"
                                      x-ref="chatInput"
                                      x-model="input"
                                      @keydown.enter.prevent="sendMessage()"
                                      :disabled="sending || closed"
                                      rows="1"
                                      placeholder="Escribe tu mensaje..."
                                      class="flex-1 resize-none max-h-20 rounded-xl border border-gray-300 dark:border-gray-600
                                             bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                             px-3 py-2 text-sm leading-snug placeholder:text-gray-400 dark:placeholder:text-gray-500
                                             focus:outline-none focus:ring-2 focus:ring-indigo-500
                                             disabled:opacity-50 disabled:cursor-not-allowed transition-colors"></textarea>
                            <button type="button"
                                    @click="sendMessage()"
                                    :disabled="!input.trim() || sending || closed"
                                    aria-label="Enviar mensaje"
                                    class="flex-shrink-0 p-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700
                                           disabled:opacity-40 disabled:cursor-not-allowed text-white
                                           transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>{{-- /chatWidget --}}

        {{-- ── FAB Solicitar cuenta ────────────────────────────────── --}}
        <div class="fixed bottom-6 left-4 z-50"
             x-show="$store.bill.active"
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
             x-show="$store.cart.count > 0"
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
                    <template x-for="(item, idx) in $store.cart.items" :key="idx">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-3">

                            {{-- Fila producto --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm leading-snug" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="$store.cart.fmt(item.price) + ' / ud.'"></p>
                                </div>

                                {{-- Cantidad --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button type="button" @click="$store.cart.dec(idx)"
                                            class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600
                                                   flex items-center justify-center text-gray-600 dark:text-gray-300
                                                   hover:border-red-400 hover:text-red-500 transition-colors
                                                   focus:outline-none focus:ring-2 focus:ring-red-400">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-5 text-center font-bold text-gray-900 dark:text-white text-sm" x-text="item.quantity"></span>
                                    <button type="button" @click="$store.cart.inc(idx)"
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
                                                            @click="$store.cart.toggleRemove(idx, ing)"
                                                            :class="$store.cart.hasMod(idx, ing.id, 'remove')
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
                                                            @click="$store.cart.toggleExtra(idx, ing)"
                                                            :class="$store.cart.hasMod(idx, ing.id, 'add')
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
                        @if($tapaConfig?->kitchen_opens_at)
                            {{ __('La cocina abre a las') }}
                            <span class="font-semibold">{{ substr($tapaConfig->kitchen_opens_at, 0, 5) }}</span>.
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
