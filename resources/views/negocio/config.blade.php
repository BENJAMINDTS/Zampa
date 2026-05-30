{{-- @author SebastianBCF --}}
{{-- @author BenjaminDTS --}}
{{-- @author Ayrtonalania --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center h-9 w-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 shrink-0">
                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" aria-hidden="true"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Configuración del negocio') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ─── ERRORES GLOBALES ─── --}}
            @if($errors->any())
                <div role="alert" aria-live="assertive"
                     class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200
                            dark:border-red-700 p-4 flex items-start gap-3">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full
                                bg-red-100 dark:bg-red-800/50 shrink-0 mt-0.5">
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400" aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="pt-0.5">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            {{ $errors->count() === 1 ? 'Hay un campo que necesita tu atención:' : 'Revisa estos campos antes de guardar:' }}
                        </p>
                        <ul class="mt-1 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ─── RESUMEN DE CAMBIOS GUARDADOS ─── --}}
            @if(session('success') && session('success') !== 'Sin cambios detectados.')
            @php
                $savedParts = array_filter(array_map('trim', explode('·', session('success'))));
            @endphp
            <div x-data="{ open: true }"
                 x-show="open"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="rounded-xl border border-emerald-200 dark:border-emerald-700
                        bg-emerald-50 dark:bg-emerald-900/20
                        px-4 py-4 flex items-start gap-4"
                 role="status" aria-live="polite">
                <div class="flex items-center justify-center h-9 w-9 rounded-full
                            bg-emerald-100 dark:bg-emerald-800/50 shrink-0">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" aria-hidden="true"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                        Cambios guardados correctamente
                    </p>
                    <ul class="mt-2 space-y-1">
                        @foreach($savedParts as $part)
                            <li class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-400">
                                <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" aria-hidden="true"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $part }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button @click="open = false"
                        aria-label="Cerrar resumen de cambios"
                        class="shrink-0 text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-200
                               focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2
                               dark:focus:ring-offset-gray-900 rounded-md p-1 transition-colors">
                    <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endif

            @php
                $kitchenSchedulesForAlpine = $tapaConfig->kitchenSchedules->map(function ($s) {
                    return ['opens_at' => substr($s->opens_at, 0, 5), 'closes_at' => substr($s->closes_at, 0, 5)];
                })->values();

                $businessSchedulesForAlpine = $tapaConfig->businessSchedules->map(function ($s) {
                    return ['opens_at' => substr($s->opens_at, 0, 5), 'closes_at' => substr($s->closes_at, 0, 5)];
                })->values();

                $configInit = [
                    'tapas_enabled'      => (bool) $tapaConfig->tapas_enabled,
                    'tapas_free'         => (bool) $tapaConfig->tapas_free,
                    'price_mode'         => old('price_mode', $tapaConfig->price_mode ?? 'fixed'),
                    'extra_tapa_enabled' => (bool) $tapaConfig->extra_tapa_enabled,
                    'kitchen_schedules'  => $kitchenSchedulesForAlpine,
                    'business_schedules' => $businessSchedulesForAlpine,
                    'split_enabled'      => (bool) auth()->user()->split_payment_enabled,
                ];
            @endphp

            <script id="config-init" type="application/json">@json($configInit)</script>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- SECCIÓN STRIPE CONNECT — fuera del form (redirige a Stripe)     --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <section aria-labelledby="section-stripe-title"
                     class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                            ring-1 ring-gray-200 dark:ring-gray-700">

                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                            flex items-start gap-4">
                    <div class="flex items-center justify-center h-10 w-10 rounded-lg
                                bg-violet-100 dark:bg-violet-900/40 shrink-0">
                        <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="section-stripe-title"
                            class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Cuenta bancaria (Stripe Connect)') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ __('Conecta tu cuenta bancaria para recibir los pagos con tarjeta directamente. Sin esto, los cobros van a la cuenta de la plataforma.') }}
                        </p>
                    </div>
                </div>

                <div class="px-4 py-5 sm:px-6">

                    @if(auth()->user()->stripe_onboarding_completed)
                        {{-- Estado: conectado --}}
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                             bg-emerald-100 dark:bg-emerald-900/30
                                             text-emerald-700 dark:text-emerald-400
                                             text-xs font-semibold">
                                    <svg class="h-3 w-3" aria-hidden="true" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    {{ __('Cuenta conectada') }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ auth()->user()->stripe_account_id }}
                                </span>
                            </div>
                            <form method="POST"
                                  action="{{ route('stripe.connect.disconnect') }}"
                                  onsubmit="return confirm('{{ __('¿Seguro que quieres desconectar tu cuenta de Stripe? Los pagos dejarán de ingresarse en tu cuenta bancaria.') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                               text-sm font-medium text-red-600 dark:text-red-400
                                               border border-red-300 dark:border-red-700
                                               hover:bg-red-50 dark:hover:bg-red-900/20
                                               focus:outline-none focus:ring-2 focus:ring-red-500
                                               focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                               transition-colors">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('Desconectar') }}
                                </button>
                            </form>
                        </div>

                    @elseif(auth()->user()->stripe_account_id)
                        {{-- Estado: cuenta creada pero onboarding pendiente --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 rounded-lg
                                        bg-amber-50 dark:bg-amber-900/20
                                        border border-amber-200 dark:border-amber-700
                                        px-4 py-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0" aria-hidden="true"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm text-amber-700 dark:text-amber-400">
                                    {{ __('Onboarding pendiente. Completa tus datos bancarios en Stripe para activar los cobros.') }}
                                </p>
                            </div>
                            <a href="{{ route('stripe.connect') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                      bg-violet-600 text-white shadow-sm
                                      hover:bg-violet-500 active:bg-violet-700
                                      focus:outline-none focus:ring-2 focus:ring-violet-500
                                      focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                {{ __('Completar onboarding en Stripe') }}
                            </a>
                        </div>

                    @else
                        {{-- Estado: sin cuenta --}}
                        <div class="space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ __('Serás redirigido a Stripe para introducir tus datos bancarios. El proceso tarda menos de 5 minutos.') }}
                            </p>
                            <a href="{{ route('stripe.connect') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold
                                      bg-violet-600 text-white shadow-sm
                                      hover:bg-violet-500 active:bg-violet-700
                                      focus:outline-none focus:ring-2 focus:ring-violet-500
                                      focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                {{ __('Conectar cuenta bancaria con Stripe') }}
                            </a>
                        </div>
                    @endif

                </div>
            </section>

            <form method="POST" action="{{ route('negocio.config.update') }}"
                  x-data="businessConfig()"
                  class="space-y-6">
                @csrf
                @method('PUT')

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 1 — Horarios del negocio                              --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <section aria-labelledby="section-business-title"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700">

                    {{-- Cabecera de sección --}}
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-start gap-4">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg
                                    bg-sky-100 dark:bg-sky-900/40 shrink-0">
                            <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-business-title"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Horario del negocio') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ __('Tramos en los que el negocio está abierto (máx. 4). Fuera de ellos la carta mostrará un aviso de cierre y no se podrán realizar pedidos. Sin tramos configurados el negocio se considera siempre abierto.') }}
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:px-6 space-y-4">

                        @error('business_schedules')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('business_schedules.*.opens_at')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('business_schedules.*.closes_at')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        {{-- Lista de tramos --}}
                        <div class="space-y-3" role="list" aria-label="{{ __('Tramos horarios del negocio') }}">
                            <template x-for="(slot, i) in business_schedules" :key="i">
                                <div class="flex items-end gap-3 p-4 rounded-lg
                                            bg-gray-50 dark:bg-gray-700/50
                                            border border-gray-200 dark:border-gray-600"
                                     role="listitem">
                                    <div class="flex-1">
                                        <label :for="'business_opens_at_' + i"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            {{ __('Abre') }}
                                        </label>
                                        <input type="time"
                                               :id="'business_opens_at_' + i"
                                               :name="'business_schedules[' + i + '][opens_at]'"
                                               x-model="slot.opens_at"
                                               aria-required="true"
                                               class="block w-full h-10 rounded-lg border-gray-300
                                                      dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                      shadow-sm text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                      focus:border-indigo-500 focus:ring-offset-2
                                                      dark:focus:ring-offset-gray-800">
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'business_closes_at_' + i"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            {{ __('Cierra') }}
                                        </label>
                                        <input type="time"
                                               :id="'business_closes_at_' + i"
                                               :name="'business_schedules[' + i + '][closes_at]'"
                                               x-model="slot.closes_at"
                                               aria-required="true"
                                               class="block w-full h-10 rounded-lg border-gray-300
                                                      dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                      shadow-sm text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                      focus:border-indigo-500 focus:ring-offset-2
                                                      dark:focus:ring-offset-gray-800">
                                    </div>
                                    <button type="button"
                                            @click="removeBusinessSchedule(i)"
                                            :aria-label="'Eliminar tramo de negocio ' + (i + 1)"
                                            class="shrink-0 p-2 rounded-lg text-red-500
                                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                                   focus:outline-none focus:ring-2 focus:ring-red-500
                                                   focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                                   transition-colors">
                                        <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                             stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Aviso sin tramos --}}
                        <div x-show="business_schedules.length === 0"
                             class="flex items-center gap-2 rounded-lg
                                    bg-amber-50 dark:bg-amber-900/20
                                    border border-amber-200 dark:border-amber-700
                                    px-4 py-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                {{ __('Sin tramos: el negocio se considera siempre abierto.') }}
                            </p>
                        </div>

                        <button type="button"
                                @click="addBusinessSchedule()"
                                :disabled="business_schedules.length >= 4"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                                       border border-indigo-300 dark:border-indigo-600
                                       text-indigo-700 dark:text-indigo-300
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       dark:focus:ring-offset-gray-800
                                       disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                 stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Añadir tramo') }}
                        </button>

                        {{-- Cierre de pedidos --}}
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label for="ordering_cutoff_minutes"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Minutos antes del cierre sin nuevos pedidos') }}
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                                {{ __('Si configuras 15, desde 15 min antes del cierre los clientes no podrán añadir productos, pero sí solicitar la cuenta. Pon 0 para aceptar pedidos hasta el cierre.') }}
                            </p>
                            <input type="number"
                                   id="ordering_cutoff_minutes"
                                   name="ordering_cutoff_minutes"
                                   value="{{ old('ordering_cutoff_minutes', $tapaConfig->ordering_cutoff_minutes ?? 0) }}"
                                   min="0" max="120"
                                   aria-required="false"
                                   aria-describedby="error-ordering_cutoff_minutes"
                                   class="block w-32 h-10 rounded-lg border-gray-300
                                          dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                          shadow-sm text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                                          focus:border-indigo-500 focus:ring-offset-2
                                          dark:focus:ring-offset-gray-800">
                            @error('ordering_cutoff_minutes')
                                <p id="error-ordering_cutoff_minutes" role="alert"
                                   class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </section>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 2 — Horarios de cocina                                --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <section aria-labelledby="section-kitchen-title"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-start gap-4">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg
                                    bg-orange-100 dark:bg-orange-900/40 shrink-0">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-kitchen-title"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Horario de la cocina') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ __('Tramos en los que la cocina está abierta (máx. 4). Fuera de estos tramos la carta solo mostrará bebidas. Sin tramos configurados la cocina se considera siempre disponible.') }}
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:px-6 space-y-4">

                        @error('kitchen_schedules')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('kitchen_schedules.*.opens_at')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('kitchen_schedules.*.closes_at')
                            <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3" role="list" aria-label="{{ __('Tramos horarios de cocina') }}">
                            <template x-for="(slot, i) in kitchen_schedules" :key="i">
                                <div class="flex items-end gap-3 p-4 rounded-lg
                                            bg-gray-50 dark:bg-gray-700/50
                                            border border-gray-200 dark:border-gray-600"
                                     role="listitem">
                                    <div class="flex-1">
                                        <label :for="'kitchen_opens_at_' + i"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            {{ __('Abre') }}
                                        </label>
                                        <input type="time"
                                               :id="'kitchen_opens_at_' + i"
                                               :name="'kitchen_schedules[' + i + '][opens_at]'"
                                               x-model="slot.opens_at"
                                               aria-required="true"
                                               class="block w-full h-10 rounded-lg border-gray-300
                                                      dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                      shadow-sm text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                      focus:border-indigo-500 focus:ring-offset-2
                                                      dark:focus:ring-offset-gray-800">
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'kitchen_closes_at_' + i"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            {{ __('Cierra') }}
                                        </label>
                                        <input type="time"
                                               :id="'kitchen_closes_at_' + i"
                                               :name="'kitchen_schedules[' + i + '][closes_at]'"
                                               x-model="slot.closes_at"
                                               aria-required="true"
                                               class="block w-full h-10 rounded-lg border-gray-300
                                                      dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                      shadow-sm text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                      focus:border-indigo-500 focus:ring-offset-2
                                                      dark:focus:ring-offset-gray-800">
                                    </div>
                                    <button type="button"
                                            @click="removeKitchenSchedule(i)"
                                            :aria-label="'Eliminar tramo de cocina ' + (i + 1)"
                                            class="shrink-0 p-2 rounded-lg text-red-500
                                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                                   focus:outline-none focus:ring-2 focus:ring-red-500
                                                   focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                                   transition-colors">
                                        <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                             stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div x-show="kitchen_schedules.length === 0"
                             class="flex items-center gap-2 rounded-lg
                                    bg-amber-50 dark:bg-amber-900/20
                                    border border-amber-200 dark:border-amber-700
                                    px-4 py-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                {{ __('Sin tramos: la cocina se considera siempre disponible.') }}
                            </p>
                        </div>

                        <button type="button"
                                @click="addKitchenSchedule()"
                                :disabled="kitchen_schedules.length >= 4"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                                       border border-indigo-300 dark:border-indigo-600
                                       text-indigo-700 dark:text-indigo-300
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       dark:focus:ring-offset-gray-800
                                       disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none"
                                 stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Añadir tramo') }}
                        </button>

                    </div>
                </section>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 3 — Sistema de tapas                                   --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <section aria-labelledby="section-tapas-title"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-start gap-4">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg
                                    bg-amber-100 dark:bg-amber-900/40 shrink-0">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-tapas-title"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Sistema de tapas') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ __('Permite ofrecer tapas a los clientes según las bebidas consumidas. Configura si son gratuitas, el precio y el número máximo de variantes.') }}
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:px-6 space-y-6">

                        {{-- Toggle: Activar tapas --}}
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <label for="tapas_enabled_btn"
                                       class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Activar sistema de tapas') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                    {{ __('Los clientes podrán elegir tapas según las bebidas consumidas.') }}
                                </p>
                            </div>
                            <button id="tapas_enabled_btn"
                                    type="button"
                                    role="switch"
                                    :aria-checked="tapas_enabled.toString()"
                                    :aria-label="tapas_enabled ? 'Sistema de tapas activo, pulsa para desactivar' : 'Sistema de tapas inactivo, pulsa para activar'"
                                    @click="tapas_enabled = !tapas_enabled"
                                    :class="tapas_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full
                                           transition-colors focus:outline-none focus:ring-2
                                           focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 mt-0.5">
                                <span :class="tapas_enabled ? 'translate-x-6' : 'translate-x-1'"
                                      class="inline-block h-4 w-4 transform bg-white rounded-full
                                             shadow transition-transform"></span>
                            </button>
                            <input type="hidden" name="tapas_enabled" :value="tapas_enabled ? '1' : '0'">
                        </div>

                        {{-- Sub-opciones (solo si tapas activas) --}}
                        <div x-show="tapas_enabled" x-transition class="space-y-6">

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">

                                {{-- Toggle: Tapas gratuitas --}}
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div class="flex-1">
                                        <label for="tapas_free_btn"
                                               class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ __('Tapas gratuitas') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ __('Desactiva para cobrar un precio fijo por tapa.') }}
                                        </p>
                                    </div>
                                    <button id="tapas_free_btn"
                                            type="button"
                                            role="switch"
                                            :aria-checked="tapas_free.toString()"
                                            :aria-label="tapas_free ? 'Tapas gratuitas, pulsa para cobrar' : 'Tapas de pago, pulsa para hacerlas gratuitas'"
                                            @click="tapas_free = !tapas_free"
                                            :class="tapas_free ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full
                                                   transition-colors focus:outline-none focus:ring-2
                                                   focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 mt-0.5">
                                        <span :class="tapas_free ? 'translate-x-6' : 'translate-x-1'"
                                              class="inline-block h-4 w-4 transform bg-white rounded-full
                                                     shadow transition-transform"></span>
                                    </button>
                                    <input type="hidden" name="tapas_free" :value="tapas_free ? '1' : '0'">
                                </div>

                                {{-- Modalidad de precio (solo si de pago) --}}
                                <div x-show="!tapas_free" x-transition>
                                    <fieldset>
                                        <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Modalidad de precio') }}
                                            <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                            <span class="sr-only">(obligatorio)</span>
                                        </legend>
                                        <p id="price-mode-desc" class="text-sm text-gray-500 dark:text-gray-400
                                                                        leading-relaxed mb-4">
                                            {{ __('Elige cómo se calcula el precio que ve el cliente para cada tapa.') }}
                                        </p>
                                        <div class="space-y-3" aria-describedby="price-mode-desc">
                                            <label class="flex items-start gap-3 cursor-pointer group">
                                                <input type="radio"
                                                       name="price_mode"
                                                       value="fixed"
                                                       x-model="price_mode"
                                                       class="mt-0.5 h-4 w-4 border-gray-300 text-indigo-600
                                                              focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                                              dark:focus:ring-offset-gray-800">
                                                <span>
                                                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100
                                                                 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                                        {{ __('Precio fijo para todas las tapas') }}
                                                    </span>
                                                    <span class="block text-sm text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                                        {{ __('Configuras un único precio que aplica a todas las tapas del menú.') }}
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="flex items-start gap-3 cursor-pointer group">
                                                <input type="radio"
                                                       name="price_mode"
                                                       value="per_product"
                                                       x-model="price_mode"
                                                       class="mt-0.5 h-4 w-4 border-gray-300 text-indigo-600
                                                              focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                                              dark:focus:ring-offset-gray-800">
                                                <span>
                                                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100
                                                                 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                                        {{ __('Precio individual por producto') }}
                                                    </span>
                                                    <span class="block text-sm text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                                        {{ __('Cada tapa usa el precio configurado en su ficha de producto.') }}
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @error('price_mode')
                                            <p id="error-price_mode" role="alert"
                                               class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </fieldset>

                                    {{-- Precio fijo global --}}
                                    <div x-show="price_mode === 'fixed'" x-transition class="mt-5">
                                        <label for="tapa_price"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Precio fijo por tapa (€)') }}
                                            <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                        </label>
                                        <input type="number"
                                               id="tapa_price"
                                               name="tapa_price"
                                               value="{{ old('tapa_price', $tapaConfig->tapa_price) }}"
                                               min="0" max="999.99" step="0.01"
                                               aria-required="true"
                                               :disabled="tapas_free || price_mode !== 'fixed'"
                                               aria-describedby="error-tapa_price"
                                               placeholder="0.00"
                                               class="block w-40 h-10 rounded-lg border-gray-300
                                                      dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                      shadow-sm text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                      focus:border-indigo-500 focus:ring-offset-2
                                                      dark:focus:ring-offset-gray-800">
                                        @error('tapa_price')
                                            <p id="error-tapa_price" role="alert"
                                               class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Aviso precio por producto --}}
                                    <div x-show="price_mode === 'per_product'" x-transition class="mt-4">
                                        <div class="flex items-start gap-3 rounded-lg
                                                    bg-indigo-50 dark:bg-indigo-900/20
                                                    border border-indigo-200 dark:border-indigo-700
                                                    px-4 py-3">
                                            <svg class="h-4 w-4 text-indigo-500 shrink-0 mt-0.5" aria-hidden="true"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p class="text-sm text-indigo-700 dark:text-indigo-300 leading-relaxed">
                                                {{ __('El precio de cada tapa se configura en la ficha del producto dentro de la categoría «Tapas».') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Tapa extra --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">

                                <div class="flex items-start justify-between gap-4 mb-5">
                                    <div class="flex-1">
                                        <label for="extra_tapa_enabled_btn"
                                               class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ __('Permitir tapa extra de pago') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ __('El cliente puede pedir una tapa adicional con precio fijo aunque las tapas base sean gratuitas. No consume variante del límite.') }}
                                        </p>
                                    </div>
                                    <button id="extra_tapa_enabled_btn"
                                            type="button"
                                            role="switch"
                                            :aria-checked="extra_tapa_enabled.toString()"
                                            :aria-label="extra_tapa_enabled ? 'Tapa extra activa, pulsa para desactivar' : 'Tapa extra inactiva, pulsa para activar'"
                                            @click="extra_tapa_enabled = !extra_tapa_enabled"
                                            :class="extra_tapa_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full
                                                   transition-colors focus:outline-none focus:ring-2
                                                   focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 mt-0.5">
                                        <span :class="extra_tapa_enabled ? 'translate-x-6' : 'translate-x-1'"
                                              class="inline-block h-4 w-4 transform bg-white rounded-full
                                                     shadow transition-transform"></span>
                                    </button>
                                    <input type="hidden" name="extra_tapa_enabled" :value="extra_tapa_enabled ? '1' : '0'">
                                </div>

                                <div x-show="extra_tapa_enabled" x-transition>
                                    <label for="extra_tapa_price"
                                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('Precio de la tapa extra (€)') }}
                                        <span aria-hidden="true" class="text-red-500 ml-0.5">*</span>
                                    </label>
                                    <input type="number"
                                           id="extra_tapa_price"
                                           name="extra_tapa_price"
                                           value="{{ old('extra_tapa_price', $tapaConfig->extra_tapa_price) }}"
                                           min="0" max="999.99" step="0.01"
                                           aria-required="true"
                                           aria-describedby="error-extra_tapa_price"
                                           placeholder="0.00"
                                           :disabled="!extra_tapa_enabled"
                                           class="block w-40 h-10 rounded-lg border-gray-300
                                                  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                                  shadow-sm text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                                  focus:border-indigo-500 focus:ring-offset-2
                                                  dark:focus:ring-offset-gray-800">
                                    @error('extra_tapa_price')
                                        <p id="error-extra_tapa_price" role="alert"
                                           class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Máximo de variantes --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <label for="max_tapa_variants"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('Máximo de variantes por mesa') }}
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                                    {{ __('Número máximo de tapas distintas que se pueden elegir en los pedidos activos de una mesa.') }}
                                </p>
                                <input type="number"
                                       id="max_tapa_variants"
                                       name="max_tapa_variants"
                                       value="{{ old('max_tapa_variants', $tapaConfig->max_tapa_variants) }}"
                                       min="1" max="20"
                                       aria-required="true"
                                       aria-describedby="error-max_tapa_variants"
                                       class="block w-32 h-10 rounded-lg border-gray-300
                                              dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                              shadow-sm text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                                              focus:border-indigo-500 focus:ring-offset-2
                                              dark:focus:ring-offset-gray-800">
                                @error('max_tapa_variants')
                                    <p id="error-max_tapa_variants" role="alert"
                                       class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>
                </section>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 4 — Cobro partido                                      --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <section aria-labelledby="section-split-title"
                         class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-start gap-4">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg
                                    bg-emerald-100 dark:bg-emerald-900/40 shrink-0">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" aria-hidden="true"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-split-title"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Cobro partido') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ __('Permite a los clientes dividir la cuenta entre varios comensales directamente desde la carta digital.') }}
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:px-6 space-y-5">

                        {{-- Toggle --}}
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <label for="split_enabled_btn"
                                       class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Activar cobro partido') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                    {{ __('Los clientes podrán dividir la cuenta por ítems o en partes iguales.') }}
                                </p>
                            </div>
                            <button id="split_enabled_btn"
                                    type="button"
                                    role="switch"
                                    :aria-checked="split_enabled.toString()"
                                    :aria-label="split_enabled ? 'Cobro partido activo, pulsa para desactivar' : 'Cobro partido inactivo, pulsa para activar'"
                                    @click="split_enabled = !split_enabled"
                                    :class="split_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full
                                           transition-colors focus:outline-none focus:ring-2
                                           focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 mt-0.5">
                                <span :class="split_enabled ? 'translate-x-6' : 'translate-x-1'"
                                      class="inline-block h-4 w-4 transform bg-white rounded-full
                                             shadow transition-transform"></span>
                            </button>
                            <input type="hidden" name="split_payment_enabled" :value="split_enabled ? '1' : '0'">
                        </div>

                        {{-- Máximo de partes --}}
                        <div x-show="split_enabled" x-transition>
                            <label for="split_payment_max_parts"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Máximo de partes por mesa') }}
                            </label>
                            <p id="max-parts-desc" class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                                {{ __('Deja vacío para no limitar el número de partes en que se puede dividir la cuenta.') }}
                            </p>
                            <input type="number"
                                   id="split_payment_max_parts"
                                   name="split_payment_max_parts"
                                   value="{{ old('split_payment_max_parts', auth()->user()->split_payment_max_parts > 0 ? auth()->user()->split_payment_max_parts : '') }}"
                                   min="2" max="20"
                                   :disabled="!split_enabled"
                                   aria-describedby="max-parts-desc error-split_payment_max_parts"
                                   placeholder="{{ __('Sin límite') }}"
                                   class="block w-40 h-10 rounded-lg border-gray-300
                                          dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                          shadow-sm text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                                          focus:border-indigo-500 focus:ring-offset-2
                                          dark:focus:ring-offset-gray-800
                                          disabled:opacity-50 disabled:cursor-not-allowed">
                            @error('split_payment_max_parts')
                                <p id="error-split_payment_max_parts" role="alert"
                                   class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </section>



                {{-- ─── ACCIÓN ─── --}}
                <div class="flex justify-end pb-4">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg
                                   bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                                   hover:bg-indigo-500 active:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition duration-150">
                        <svg class="h-4 w-4 shrink-0" aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Guardar configuración') }}</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
