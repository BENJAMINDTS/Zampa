{{-- @author SebastianBCF --}}
{{-- @author BenjaminDTS --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuración de Tapas') }}
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
                    $schedulesForAlpine = $tapaConfig->schedules->map(function ($s) {
                        return ['opens_at' => substr($s->opens_at, 0, 5), 'closes_at' => substr($s->closes_at, 0, 5)];
                    })->values();
                @endphp

                <form
                    method="POST"
                    action="{{ route('tapas.update') }}"
                    x-data="{
                        tapas_enabled:      {{ $tapaConfig->tapas_enabled ? 'true' : 'false' }},
                        tapas_free:         {{ $tapaConfig->tapas_free ? 'true' : 'false' }},
                        extra_tapa_enabled: {{ $tapaConfig->extra_tapa_enabled ? 'true' : 'false' }},
                        schedules: @json($schedulesForAlpine),
                        addSchedule()    { this.schedules.push({ opens_at: '', closes_at: '' }); },
                        removeSchedule(i){ this.schedules.splice(i, 1); }
                    }"
                >
                    @csrf
                    @method('PUT')

                    {{-- ── SECCIÓN 1: Tapas básicas ─────────────────────── --}}
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

                        {{-- Precio por tapa (solo si de pago) --}}
                        <div x-show="!tapas_free" x-transition>
                            <label for="tapa_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Precio por tapa (€)') }}
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

                        {{-- ── SECCIÓN 2: Tapa extra ────────────────────── --}}
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

                        {{-- ── SECCIÓN 3: Máximo de variantes ──────────── --}}
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

                        {{-- ── SECCIÓN 4: Horario de cocina (tramos) ────── --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Horario de cocina') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                {{ __('Añade los tramos en los que la cocina está abierta (ej: mediodía 13:00–16:00 y noche 20:00–23:30). Fuera de estos tramos la carta solo muestra bebidas. Sin tramos configurados la cocina se considera siempre disponible.') }}
                            </p>

                            {{-- Errores de validación de tramos --}}
                            @error('schedules')
                                <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('schedules.*.opens_at')
                                <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('schedules.*.closes_at')
                                <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            {{-- Lista dinámica de tramos --}}
                            <div class="space-y-3 mb-4" role="list" aria-label="Tramos horarios de cocina">
                                <template x-for="(slot, i) in schedules" :key="i">
                                    <div class="flex items-end gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                                         role="listitem">
                                        <div class="flex-1">
                                            <label :for="'opens_at_' + i"
                                                   class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                {{ __('Abre') }}
                                            </label>
                                            <input type="time"
                                                   :id="'opens_at_' + i"
                                                   :name="'schedules[' + i + '][opens_at]'"
                                                   x-model="slot.opens_at"
                                                   aria-required="true"
                                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div class="flex-1">
                                            <label :for="'closes_at_' + i"
                                                   class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                {{ __('Cierra') }}
                                            </label>
                                            <input type="time"
                                                   :id="'closes_at_' + i"
                                                   :name="'schedules[' + i + '][closes_at]'"
                                                   x-model="slot.closes_at"
                                                   aria-required="true"
                                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <button type="button"
                                                @click="removeSchedule(i)"
                                                :aria-label="'Eliminar tramo ' + (i + 1)"
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
                                    @click="addSchedule()"
                                    :disabled="schedules.length >= 10"
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

                            <p x-show="schedules.length === 0"
                               class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                                {{ __('Sin tramos: la cocina se considera siempre disponible.') }}
                            </p>
                        </div>

                    </div>

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
