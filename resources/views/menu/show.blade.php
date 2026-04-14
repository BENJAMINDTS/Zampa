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
                'allergenIds' => $p->ingredients->where('is_allergen', true)->pluck('id')->values()->toArray(),
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
    @endphp

    {{-- Los datos se inyectan en un <script> separado para evitar conflictos
         de escapado al pasar JSON como argumento en x-data. --}}
    <script id="menu-products" type="application/json">@json($productsForAlpine)</script>

    <script>
        document.addEventListener('alpine:init', () => {
            const raw  = document.getElementById('menu-products');
            const list = raw ? JSON.parse(raw.textContent) : [];

            // ── Carrito global ──────────────────────────────────────────
            Alpine.store('cart', {
                items:   [],
                open:    false,
                sending: false,
                sent:    false,
                error:   null,
                tableHash: '{{ $table->unique_hash }}',

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
                    if (this.activeAllergens.some(id => p.allergenIds.includes(id))) return false;
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
                    <div class="flex items-center gap-2 overflow-x-auto pb-1"
                         role="group" aria-label="Filtrar por alérgenos ausentes">
                        <span class="flex-shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Sin
                        </span>

                        @foreach ($allergens as $allergen)
                            <button type="button"
                                    @click="toggleAllergen({{ $allergen->id }})"
                                    :aria-pressed="activeAllergens.includes({{ $allergen->id }})"
                                    :class="activeAllergens.includes({{ $allergen->id }})
                                        ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30'
                                        : 'bg-white dark:bg-gray-800 opacity-70 hover:opacity-100'"
                                    class="flex flex-col items-center gap-1 p-2 rounded-lg border border-gray-200
                                           dark:border-gray-700 transition-all duration-200 cursor-pointer
                                           focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                           dark:focus:ring-offset-gray-900"
                                    aria-label="{{ $allergen->allergenTypeName() ?? $allergen->name }}">
                                @if($allergen->allergen_type)
                                    <img src="{{ $allergen->allergenIconPath() }}"
                                         alt="{{ $allergen->allergenTypeName() }}"
                                         class="h-10 w-10 rounded-full">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 text-center leading-tight max-w-[4rem]">
                                        {{ $allergen->allergenTypeName() }}
                                    </span>
                                @else
                                    <span class="h-10 w-10 flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 rounded-full text-xl" aria-hidden="true">⚠️</span>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 text-center leading-tight max-w-[4rem]">
                                        {{ $allergen->name }}
                                    </span>
                                @endif
                            </button>
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
                            <li>
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
