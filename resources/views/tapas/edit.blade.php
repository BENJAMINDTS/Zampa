{{-- @author SebastianBCF --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuración de Tapas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash de éxito --}}
            @if(session('success'))
            <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 sm:p-8">

                <form
                    method="POST"
                    action="{{ route('tapas.update') }}"
                    x-data="{
                        tapas_enabled: {{ $tapaConfig->tapas_enabled ? 'true' : 'false' }},
                        tapas_free: {{ $tapaConfig->tapas_free ? 'true' : 'false' }}
                    }"
                >
                    @csrf
                    @method('PUT')

                    {{-- ── Activar tapas ── --}}
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

                    {{-- ── Configuración detallada (visible solo si tapas activas) ── --}}
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
                            </label>
                            <input
                                type="number"
                                id="tapa_price"
                                name="tapa_price"
                                value="{{ old('tapa_price', $tapaConfig->tapa_price) }}"
                                min="0"
                                max="999.99"
                                step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="0.00"
                                aria-describedby="error-tapa_price"
                            >
                            @error('tapa_price')
                                <p id="error-tapa_price" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Máximo de variantes distintas --}}
                        <div>
                            <label for="max_tapa_variants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Máximo de variantes de tapa por mesa') }}
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                {{ __('Número máximo de tapas distintas que se pueden pedir en un mismo pedido (p. ej. 3).') }}
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

                    {{-- Botón guardar --}}
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
