{{-- @author BenjaminDTS --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cobro Partido') }}
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

                <form
                    method="POST"
                    action="{{ route('split-payment.update') }}"
                    x-data="{
                        split_enabled: {{ $user->split_payment_enabled ? 'true' : 'false' }}
                    }"
                >
                    @csrf
                    @method('PUT')

                    {{-- ── Toggle: Activar cobro partido ───────────────────── --}}
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <label for="split_enabled_btn" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Activar cobro partido') }}
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('Permite a los clientes dividir la cuenta entre varios comensales desde la carta digital.') }}
                            </p>
                        </div>
                        <button
                            id="split_enabled_btn"
                            type="button"
                            role="switch"
                            :aria-checked="split_enabled.toString()"
                            @click="split_enabled = !split_enabled"
                            :class="split_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            <span
                                :class="split_enabled ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform bg-white rounded-full transition-transform"
                            ></span>
                        </button>
                        <input type="hidden" name="split_payment_enabled" :value="split_enabled ? '1' : '0'">
                    </div>

                    {{-- ── Campo: Máximo de partes (visible solo si activo) ── --}}
                    <div x-show="split_enabled" x-transition class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <label for="split_payment_max_parts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Máximo de partes por mesa') }}
                        </label>
                        <p id="max-parts-desc" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('Deja vacío para no limitar el número de partes en que se puede dividir la cuenta.') }}
                        </p>
                        <input
                            type="number"
                            id="split_payment_max_parts"
                            name="split_payment_max_parts"
                            value="{{ old('split_payment_max_parts', $user->split_payment_max_parts > 0 ? $user->split_payment_max_parts : '') }}"
                            min="2"
                            max="20"
                            :disabled="!split_enabled"
                            :aria-required="split_enabled ? 'false' : undefined"
                            aria-describedby="max-parts-desc error-split_payment_max_parts"
                            class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            placeholder="{{ __('Sin límite') }}"
                        >
                        @error('split_payment_max_parts')
                            <p id="error-split_payment_max_parts" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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
