{{-- @author SebastianBCF --}}
{{-- @author BenjaminDTS --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuración del negocio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
            <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 sm:p-8">

                @php
                    $kitchenSchedulesForAlpine = $tapaConfig->kitchenSchedules->map(function ($s) {
                        return ['opens_at' => substr($s->opens_at, 0, 5), 'closes_at' => substr($s->closes_at, 0, 5)];
                    })->values();

                    $businessSchedulesForAlpine = $tapaConfig->businessSchedules->map(function ($s) {
                        return ['opens_at' => substr($s->opens_at, 0, 5), 'closes_at' => substr($s->closes_at, 0, 5)];
                    })->values();
                @endphp

                <form
                    method="POST"
                    action="{{ route('negocio.config.update') }}"
                    x-data="{
                        tapas_enabled:      {{ $tapaConfig->tapas_enabled ? 'true' : 'false' }},
                        tapas_free:         {{ $tapaConfig->tapas_free ? 'true' : 'false' }},
                        price_mode:         '{{ old('price_mode', $tapaConfig->price_mode ?? 'fixed') }}',
                        extra_tapa_enabled: {{ $tapaConfig->extra_tapa_enabled ? 'true' : 'false' }},
                        kitchen_schedules:  {{ json_encode($kitchenSchedulesForAlpine, JSON_HEX_QUOT) }},
                        business_schedules: {{ json_encode($businessSchedulesForAlpine, JSON_HEX_QUOT) }},
                        addKitchenSchedule()     { if (this.kitchen_schedules.length < 4) this.kitchen_schedules.push({ opens_at: '', closes_at: '' }); },
                        removeKitchenSchedule(i) { this.kitchen_schedules.splice(i, 1); },
                        addBusinessSchedule()     { if (this.business_schedules.length < 4) this.business_schedules.push({ opens_at: '', closes_at: '' }); },
                        removeBusinessSchedule(i) { this.business_schedules.splice(i, 1); }
                    }"
                >
                    @csrf
                    @method('PUT')

                    {{-- ══════════════════════════════════════════════════════ --}}
                    {{-- SECCIÓN 1 — Horarios del negocio                      --}}
                    {{-- ══════════════════════════════════════════════════════ --}}
                    <section aria-labelledby="section-business-title">
                        <h3 id="section-business-title" class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            {{ __('¿Cuándo está abierto tu negocio?') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('Añade los tramos en los que el negocio está abierto (máx. 4). Fuera de ellos la carta pública mostrará un aviso de negocio cerrado y no se podrán realizar pedidos. Sin tramos configurados el negocio se considera siempre abierto.') }}
                        </p>

                        @error('business_schedules')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('business_schedules.*.opens_at')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('business_schedules.*.closes_at')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3 mb-4" role="list" aria-label="{{ __('Tramos horarios del negocio') }}">
                            <template x-for="(slot, i) in business_schedules" :key="i">
                                <div class="flex items-end gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                                     role="listitem">
                                    <div class="flex-1">
                                        <label :for="'business_opens_at_' + i"
                                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            {{ __('Abre') }}
                                        </label>
                                        <input type="time"
                                               :id="'business_opens_at_' + i"
                                               :name="'business_schedules[' + i + '][opens_at]'"
                                               x-model="slot.opens_at"
                                               aria-required="true"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'business_closes_at_' + i"
                                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            {{ __('Cierra') }}
                                        </label>
                                        <input type="time"
                                               :id="'business_closes_at_' + i"
                                               :name="'business_schedules[' + i + '][closes_at]'"
                                               x-model="slot.closes_at"
                                               aria-required="true"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <button type="button"
                                            @click="removeBusinessSchedule(i)"
                                            :aria-label="'Eliminar tramo de negocio ' + (i + 1)"
                                            class="mb-0.5 flex-shrink-0 p-1.5 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20
                                                   focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                                @click="addBusinessSchedule()"
                                :disabled="business_schedules.length >= 4"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium
                                       border border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Añadir tramo') }}
                        </button>

                        <p x-show="business_schedules.length === 0"
                           class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                            {{ __('Sin tramos: el negocio se considera siempre abierto.') }}
                        </p>
                    </section>

                    {{-- ── Cierre de pedidos ────────────────────────────── --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                        <label for="ordering_cutoff_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Minutos antes del cierre sin nuevos pedidos') }}
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('Si configuras 15, desde 15 minutos antes del cierre de cada tramo los clientes no podrán añadir productos al carrito pero sí solicitar la cuenta. Pon 0 para aceptar pedidos hasta el cierre.') }}
                        </p>
                        <input
                            type="number"
                            id="ordering_cutoff_minutes"
                            name="ordering_cutoff_minutes"
                            value="{{ old('ordering_cutoff_minutes', $tapaConfig->ordering_cutoff_minutes ?? 0) }}"
                            min="0"
                            max="120"
                            aria-required="false"
                            aria-describedby="error-ordering_cutoff_minutes"
                            class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('ordering_cutoff_minutes')
                            <p id="error-ordering_cutoff_minutes" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ══════════════════════════════════════════════════════ --}}
                    {{-- SECCIÓN 2 — Horarios de cocina                        --}}
                    {{-- ══════════════════════════════════════════════════════ --}}
                    <section aria-labelledby="section-kitchen-title" class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                        <h3 id="section-kitchen-title" class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            {{ __('¿Cuándo está abierta la cocina?') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('Añade los tramos en los que la cocina está abierta (máx. 4). Fuera de estos tramos la carta solo mostrará bebidas. Sin tramos configurados la cocina se considera siempre disponible.') }}
                        </p>

                        @error('kitchen_schedules')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('kitchen_schedules.*.opens_at')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('kitchen_schedules.*.closes_at')
                            <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3 mb-4" role="list" aria-label="{{ __('Tramos horarios de cocina') }}">
                            <template x-for="(slot, i) in kitchen_schedules" :key="i">
                                <div class="flex items-end gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                                     role="listitem">
                                    <div class="flex-1">
                                        <label :for="'kitchen_opens_at_' + i"
                                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            {{ __('Abre') }}
                                        </label>
                                        <input type="time"
                                               :id="'kitchen_opens_at_' + i"
                                               :name="'kitchen_schedules[' + i + '][opens_at]'"
                                               x-model="slot.opens_at"
                                               aria-required="true"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'kitchen_closes_at_' + i"
                                               class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            {{ __('Cierra') }}
                                        </label>
                                        <input type="time"
                                               :id="'kitchen_closes_at_' + i"
                                               :name="'kitchen_schedules[' + i + '][closes_at]'"
                                               x-model="slot.closes_at"
                                               aria-required="true"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <button type="button"
                                            @click="removeKitchenSchedule(i)"
                                            :aria-label="'Eliminar tramo de cocina ' + (i + 1)"
                                            class="mb-0.5 flex-shrink-0 p-1.5 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20
                                                   focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                                @click="addKitchenSchedule()"
                                :disabled="kitchen_schedules.length >= 4"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium
                                       border border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Añadir tramo') }}
                        </button>

                        <p x-show="kitchen_schedules.length === 0"
                           class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                            {{ __('Sin tramos: la cocina se considera siempre disponible.') }}
                        </p>
                    </section>

                    {{-- ══════════════════════════════════════════════════════ --}}
                    {{-- SECCIÓN 3 — Tapas                                     --}}
                    {{-- ══════════════════════════════════════════════════════ --}}
                    <section aria-labelledby="section-tapas-title" class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                        <h3 id="section-tapas-title" class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Sistema de tapas') }}
                        </h3>

                        {{-- Activar tapas --}}
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <label for="tapas_enabled_btn" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Activar sistema de tapas') }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ __('Cuando está activo, los clientes pueden elegir tapas según las bebidas consumidas.') }}
                                </p>
                            </div>
                            <button
                                id="tapas_enabled_btn"
                                type="button"
                                role="switch"
                                :aria-checked="tapas_enabled.toString()"
                                @click="tapas_enabled = !tapas_enabled"
                                :class="tapas_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <span
                                    :class="tapas_enabled ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-4 w-4 transform bg-white rounded-full transition-transform"
                                ></span>
                            </button>
                            <input type="hidden" name="tapas_enabled" :value="tapas_enabled ? '1' : '0'">
                        </div>

                        <div x-show="tapas_enabled" x-transition class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">

                            {{-- Gratuitas / de pago --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="tapas_free_btn" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('Tapas gratuitas') }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ __('Desactiva para cobrar un precio fijo por tapa.') }}
                                    </p>
                                </div>
                                <button
                                    id="tapas_free_btn"
                                    type="button"
                                    role="switch"
                                    :aria-checked="tapas_free.toString()"
                                    @click="tapas_free = !tapas_free"
                                    :class="tapas_free ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span
                                        :class="tapas_free ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform bg-white rounded-full transition-transform"
                                    ></span>
                                </button>
                                <input type="hidden" name="tapas_free" :value="tapas_free ? '1' : '0'">
                            </div>

                            {{-- Modalidad de precio (solo si de pago) --}}
                            <div x-show="!tapas_free" x-transition>
                                <fieldset>
                                    <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Modalidad de precio') }}
                                        <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                    </legend>
                                    <p id="price-mode-desc" class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        {{ __('Elige cómo se calcula el precio que ve el cliente para cada tapa.') }}
                                    </p>
                                    <div class="space-y-3" aria-describedby="price-mode-desc">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="price_mode"
                                                value="fixed"
                                                x-model="price_mode"
                                                class="mt-0.5 h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span>
                                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ __('Precio fijo para todas las tapas') }}
                                                </span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ __('Configuras un único precio que aplica a todas las tapas del menú.') }}
                                                </span>
                                            </span>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="price_mode"
                                                value="per_product"
                                                x-model="price_mode"
                                                class="mt-0.5 h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span>
                                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ __('Precio individual por producto') }}
                                                </span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ __('Cada tapa usa el precio configurado en su ficha de producto.') }}
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                    @error('price_mode')
                                        <p id="error-price_mode" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </fieldset>
                            </div>

                            {{-- Precio fijo global --}}
                            <div x-show="!tapas_free && price_mode === 'fixed'" x-transition>
                                <label for="tapa_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('Precio fijo por tapa (€)') }}
                                    <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="tapa_price"
                                    name="tapa_price"
                                    value="{{ old('tapa_price', $tapaConfig->tapa_price) }}"
                                    min="0"
                                    max="999.99"
                                    step="0.01"
                                    aria-required="true"
                                    aria-describedby="error-tapa_price"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="0.00"
                                >
                                @error('tapa_price')
                                    <p id="error-tapa_price" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Aviso precio por producto --}}
                            <div x-show="!tapas_free && price_mode === 'per_product'" x-transition>
                                <p class="text-sm text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 rounded-md px-3 py-2">
                                    {{ __('El precio de cada tapa se configura en la ficha del producto dentro de la categoría "Tapas".') }}
                                </p>
                            </div>

                            {{-- Tapa extra --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <label for="extra_tapa_enabled_btn" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('Permitir tapa extra de pago') }}
                                        </label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ __('El cliente puede pedir una tapa adicional con precio fijo aunque las tapas base sean gratuitas. No consume variante del límite.') }}
                                        </p>
                                    </div>
                                    <button
                                        id="extra_tapa_enabled_btn"
                                        type="button"
                                        role="switch"
                                        :aria-checked="extra_tapa_enabled.toString()"
                                        @click="extra_tapa_enabled = !extra_tapa_enabled"
                                        :class="extra_tapa_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        <span
                                            :class="extra_tapa_enabled ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-4 w-4 transform bg-white rounded-full transition-transform"
                                        ></span>
                                    </button>
                                    <input type="hidden" name="extra_tapa_enabled" :value="extra_tapa_enabled ? '1' : '0'">
                                </div>

                                <div x-show="extra_tapa_enabled" x-transition>
                                    <label for="extra_tapa_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('Precio de la tapa extra (€)') }}
                                        <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        id="extra_tapa_price"
                                        name="extra_tapa_price"
                                        value="{{ old('extra_tapa_price', $tapaConfig->extra_tapa_price) }}"
                                        min="0"
                                        max="999.99"
                                        step="0.01"
                                        aria-required="true"
                                        aria-describedby="error-extra_tapa_price"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="0.00"
                                    >
                                    @error('extra_tapa_price')
                                        <p id="error-extra_tapa_price" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Máximo de variantes --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <label for="max_tapa_variants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('Máximo de variantes de tapa por mesa') }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ __('Número máximo de tapas distintas que se pueden elegir en los pedidos activos de una mesa (ej. 3 significa que pueden elegir hasta 3 tapas diferentes).') }}
                                </p>
                                <input
                                    type="number"
                                    id="max_tapa_variants"
                                    name="max_tapa_variants"
                                    value="{{ old('max_tapa_variants', $tapaConfig->max_tapa_variants) }}"
                                    min="1"
                                    max="20"
                                    aria-required="true"
                                    aria-describedby="error-max_tapa_variants"
                                    class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('max_tapa_variants')
                                    <p id="error-max_tapa_variants" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </section>

                    <div class="mt-8 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-white text-sm font-medium rounded-md transition-colors"
                        >
                            {{ __('Guardar configuración') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
