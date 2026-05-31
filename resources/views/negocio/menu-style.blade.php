{{-- @author Ayrtonalania --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center h-9 w-9 rounded-lg bg-violet-100 dark:bg-violet-900/40 shrink-0">
                <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Estilo de la carta digital') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ─── ERRORES ─── --}}
            @if($errors->any())
                <div role="alert" aria-live="assertive"
                     class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200
                            dark:border-red-700 p-4 flex items-start gap-3">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full
                                bg-red-100 dark:bg-red-800/50 shrink-0 mt-0.5">
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400" aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <ul class="pt-0.5 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ─── ÉXITO ─── --}}
            @if(session('success') && session('success') !== 'Sin cambios detectados.')
                <div x-data="{ open: true }"
                     x-show="open"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="rounded-xl border border-emerald-200 dark:border-emerald-700
                            bg-emerald-50 dark:bg-emerald-900/20 px-4 py-4 flex items-center gap-3"
                     role="status" aria-live="polite">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full
                                bg-emerald-100 dark:bg-emerald-800/50 shrink-0">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-medium text-emerald-800 dark:text-emerald-300">
                        {{ session('success') }}
                    </p>
                    <button @click="open = false" aria-label="Cerrar"
                            class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ══════════════════════════════════════════════════════════════════ --}}
            {{-- FORM + SELECTOR + PREVIEW (Alpine único para estado compartido)   --}}
            {{-- ══════════════════════════════════════════════════════════════════ --}}
            <div x-data="menuStylePage('{{ old('menu_style', $currentStyle) }}')">

                <form method="POST" action="{{ route('negocio.menu-style.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="menu_style" :value="menu_style">

                    {{-- ─── SELECTOR ─── --}}
                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                    ring-1 ring-gray-200 dark:ring-gray-700 mb-6"
                             aria-labelledby="section-menu-style">

                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                            <div class="flex items-center justify-center h-9 w-9 rounded-lg bg-violet-100 dark:bg-violet-900/40 shrink-0">
                                <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 id="section-menu-style" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    Estilo visual
                                </h3>
                                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    Elige el diseño que verán tus clientes al escanear el QR de la mesa.
                                </p>
                            </div>
                        </div>

                        <div class="px-4 py-5 sm:p-6">
                            <fieldset>
                                <legend class="sr-only">Selecciona un estilo de carta</legend>
                                @error('menu_style')
                                    <p role="alert" class="mb-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror

                                @php
                                    $styleMeta = [
                                        'modern'  => ['label' => 'Moderno',       'desc' => 'Limpio y luminoso. Tarjetas redondeadas y acento verde.',          'accent' => 'green'],
                                        'classic' => ['label' => 'Clásico',       'desc' => 'Cálido y elegante. Tipografía serif y tonos crema con dorado.',    'accent' => 'amber'],
                                        'minimal' => ['label' => 'Minimalista',   'desc' => 'Máximo blanco, mínimo ruido. Sin ornamentos ni sombras.',          'accent' => 'gray'],
                                    ];
                                    $ac = [
                                        'green' => ['border'=>'border-green-500','bg'=>'bg-green-50 dark:bg-green-900/20','iconBg'=>'bg-green-100 dark:bg-green-900/40','icon'=>'text-green-600 dark:text-green-400','stripe'=>'bg-green-500','ring'=>'ring-green-500','radioText'=>'text-green-600'],
                                        'amber' => ['border'=>'border-amber-500','bg'=>'bg-amber-50 dark:bg-amber-900/20','iconBg'=>'bg-amber-100 dark:bg-amber-900/40','icon'=>'text-amber-600 dark:text-amber-400','stripe'=>'bg-amber-500','ring'=>'ring-amber-500','radioText'=>'text-amber-600'],
                                        'gray'  => ['border'=>'border-gray-500', 'bg'=>'bg-gray-50 dark:bg-gray-700/40', 'iconBg'=>'bg-gray-100 dark:bg-gray-700',       'icon'=>'text-gray-600 dark:text-gray-400', 'stripe'=>'bg-gray-600', 'ring'=>'ring-gray-500', 'radioText'=>'text-gray-600'],
                                    ];
                                @endphp

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    @foreach($styleMeta as $val => $sm)
                                        @php $a = $ac[$sm['accent']]; $sel = old('menu_style', $currentStyle) === $val; @endphp
                                        <label for="ms_{{ $val }}"
                                               class="relative flex flex-col cursor-pointer rounded-xl border-2 overflow-hidden
                                                      transition duration-150 select-none
                                                      focus-within:ring-2 focus-within:ring-offset-2
                                                      dark:focus-within:ring-offset-gray-800 {{ $a['ring'] }}
                                                      {{ $sel ? $a['border'].' '.$a['bg'] : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                               :class="menu_style==='{{ $val }}' ? '{{ $a['border'] }} {{ $a['bg'] }}' : 'border-gray-200 dark:border-gray-600'"
                                               @click.prevent="menu_style='{{ $val }}'">

                                            <div class="h-1.5 w-full {{ $a['stripe'] }} transition-opacity duration-150"
                                                 :class="menu_style==='{{ $val }}' ? 'opacity-100' : 'opacity-0'"></div>

                                            <div class="p-4 flex flex-col gap-3 flex-1">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center justify-center h-8 w-8 rounded-lg shrink-0 {{ $a['iconBg'] }}">
                                                        <svg class="h-4 w-4 {{ $a['icon'] }}" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            @if($val==='modern')
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                            @elseif($val==='classic')
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                            @else
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                            @endif
                                                        </svg>
                                                    </div>
                                                    <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sm['label'] }}</span>
                                                    <input type="radio" id="ms_{{ $val }}" value="{{ $val }}"
                                                           {{ $sel ? 'checked' : '' }}
                                                           class="absolute top-0 left-0 opacity-0 w-px h-px" tabindex="-1" aria-hidden="true">
                                                    <span class="h-4 w-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors duration-150"
                                                          :class="menu_style==='{{ $val }}' ? '{{ $a['radioText'] }} border-current' : 'border-gray-300 dark:border-gray-500'">
                                                        <span class="h-2 w-2 rounded-full transition-opacity duration-150"
                                                              :class="menu_style==='{{ $val }}' ? '{{ str_replace('text-','bg-',$a['radioText']) }} opacity-100' : 'opacity-0'"></span>
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $sm['desc'] }}</p>

                                                {{-- Mini maqueta --}}
                                                @if($val==='modern')
                                                    <div aria-hidden="true" class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 overflow-hidden text-[9px] leading-tight">
                                                        <div class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700 px-2 py-1.5 flex items-center justify-between">
                                                            <span class="font-bold text-gray-900 dark:text-gray-100 text-[10px]">Mi Restaurante</span>
                                                            <span class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center"><span class="w-2 h-0.5 bg-white rounded-full block"></span></span>
                                                        </div>
                                                        <div class="px-2 py-1 flex gap-1">
                                                            <span class="rounded-full bg-green-600 text-white px-1.5 py-0.5">Todo</span>
                                                            <span class="rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5">Entrantes</span>
                                                        </div>
                                                        <div class="mx-2 mb-2 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-1.5 flex gap-1.5">
                                                            <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 shrink-0"></div>
                                                            <div class="flex-1 min-w-0"><div class="font-semibold text-gray-900 dark:text-gray-100">Pizza Margherita</div><div class="text-amber-500 font-bold mt-0.5">12,00 €</div></div>
                                                            <span class="w-5 h-5 rounded-lg bg-green-600 text-white flex items-center justify-center font-bold text-[11px] shrink-0 self-center">+</span>
                                                        </div>
                                                        <div class="bg-green-800 rounded-b-xl px-2 py-1.5 flex justify-between"><span class="text-white font-semibold">Ver pedido</span><span class="text-amber-400 font-bold">12,00 €</span></div>
                                                    </div>
                                                @elseif($val==='classic')
                                                    <div aria-hidden="true" class="rounded border border-amber-200 overflow-hidden text-[9px] leading-tight" style="background:#FBF4E4">
                                                        <div class="border-b border-amber-300 px-2 py-1.5 flex items-center justify-between" style="background:#F4ECDA">
                                                            <span class="font-bold text-[10px]" style="color:#2E2013">Mi Restaurante</span>
                                                            <span class="text-[8px] tracking-widest" style="color:#A6791E">✦</span>
                                                        </div>
                                                        <div class="px-2 py-1 flex gap-1">
                                                            <span class="rounded px-1.5 py-0.5 text-white" style="background:#7C5326">Todo</span>
                                                            <span class="rounded px-1.5 py-0.5" style="background:#E3D6BB;color:#5C4A33">Entrantes</span>
                                                        </div>
                                                        <div class="mx-2 mb-2 rounded border p-1.5 flex gap-1.5" style="border-color:#C9B690;background:#FBF4E4">
                                                            <div class="w-8 h-8 rounded shrink-0" style="background:#E3D6BB"></div>
                                                            <div class="flex-1 min-w-0"><div class="font-semibold" style="color:#2E2013">Pizza Margherita</div><div class="font-bold mt-0.5" style="color:#A6791E">12,00 €</div></div>
                                                            <span class="w-5 h-5 rounded text-white flex items-center justify-center font-bold text-[11px] shrink-0 self-center" style="background:linear-gradient(135deg,#7A5320,#463012)">+</span>
                                                        </div>
                                                        <div class="rounded-b px-2 py-1.5 flex justify-between" style="background:linear-gradient(180deg,#8A5A22,#5A3A12);border-top:1px solid #A6791E">
                                                            <span class="text-white font-semibold">Ver pedido</span><span class="font-bold" style="color:#E0B864">12,00 €</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div aria-hidden="true" class="rounded border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-950 overflow-hidden text-[9px] leading-tight">
                                                        <div class="border-b border-gray-200 dark:border-gray-700 px-2 py-1.5 flex items-center justify-between bg-white dark:bg-gray-950">
                                                            <span class="font-bold tracking-tight text-[10px] text-gray-900 dark:text-gray-100" class="font-mono">MI RESTAURANTE</span>
                                                            <span class="text-gray-400 text-[8px]">☰</span>
                                                        </div>
                                                        <div class="px-2 py-1 flex gap-1">
                                                            <span class="border border-gray-900 dark:border-gray-100 text-gray-900 dark:text-gray-100 px-1.5 py-0.5">Todo</span>
                                                            <span class="border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 px-1.5 py-0.5">Entrantes</span>
                                                        </div>
                                                        <div class="mx-2 mb-2 border border-gray-200 dark:border-gray-600 p-1.5 flex gap-1.5 bg-white dark:bg-gray-950">
                                                            <div class="w-8 h-8 shrink-0 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div>
                                                            <div class="flex-1 min-w-0"><div class="font-medium text-gray-900 dark:text-gray-100" class="font-mono">Pizza Margherita</div><div class="text-gray-900 dark:text-gray-100 font-bold mt-0.5">12,00 €</div></div>
                                                            <span class="w-5 h-5 border border-gray-900 dark:border-gray-100 text-gray-900 dark:text-gray-100 flex items-center justify-center font-bold text-[11px] shrink-0 self-center">+</span>
                                                        </div>
                                                        <div class="border-t border-gray-200 dark:border-gray-700 px-2 py-1.5 flex justify-between bg-gray-900 dark:bg-gray-950">
                                                            <span class="text-white font-medium" class="font-mono">VER PEDIDO</span>
                                                            <span class="text-white font-bold">12,00 €</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Badge seleccionado --}}
                                                <div class="mt-auto flex items-center gap-2 rounded-lg px-3 py-2
                                                            bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700
                                                            transition-opacity duration-150"
                                                     :class="menu_style==='{{ $val }}' ? 'opacity-100' : 'opacity-0'"
                                                     :aria-hidden="menu_style!=='{{ $val }}' ? 'true' : 'false'" aria-live="polite">
                                                    <div class="flex items-center justify-center h-5 w-5 rounded-full bg-emerald-500 shrink-0">
                                                        <svg class="h-3 w-3 text-white" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Estilo seleccionado</span>
                                                    <span class="sr-only">— {{ $sm['label'] }} está seleccionado</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Aviso sin guardar --}}
                                <div class="mt-4 flex items-center gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20
                                            border border-amber-200 dark:border-amber-700 px-4 py-3 transition-opacity duration-200"
                                     :class="menu_style !== savedMenuStyle ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                                     :aria-hidden="menu_style === savedMenuStyle ? 'true' : 'false'"
                                     role="status" aria-live="polite">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-amber-700 dark:text-amber-400">
                                        Has seleccionado el estilo <strong x-text="styleLabels[menu_style]"></strong>.
                                        Guarda los cambios para aplicarlo.
                                    </p>
                                </div>
                            </fieldset>
                        </div>
                    </section>


                {{-- ═══════════════════════════════════════════════════════════════ --}}
                {{-- PREVISUALIZACIÓN INTERACTIVA                                   --}}
                {{-- ═══════════════════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-preview">

                    {{-- Cabecera de la sección --}}
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center h-9 w-9 rounded-lg bg-teal-100 dark:bg-teal-900/40 shrink-0">
                                <svg class="h-5 w-5 text-teal-600 dark:text-teal-400" aria-hidden="true"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 id="section-preview" class="text-base font-semibold text-gray-900 dark:text-gray-100">Previsualización</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Demo interactiva — cambia en tiempo real al seleccionar un estilo.</p>
                            </div>
                        </div>
                        <button type="button" @click="demoReset()"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-700 dark:text-teal-400
                                       hover:text-teal-900 dark:hover:text-teal-200 focus:outline-none
                                       focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                       rounded px-1 transition"
                                aria-label="Reiniciar demo interactiva">
                            <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reiniciar demo
                        </button>
                    </div>

                    {{-- Área del preview --}}
                    <div class="px-4 py-8 sm:p-10 flex flex-col items-center gap-6 transition-colors duration-500"
                         :style="'background:' + theme.wrapBg"
                         role="region" aria-label="Vista previa interactiva de la carta digital" aria-live="polite" aria-atomic="true">

                        {{-- Nombre del estilo activo --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-widest transition-colors duration-300"
                                  :style="'color:' + theme.accent">
                                <span x-text="styleLabels[menu_style]"></span>
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full transition-colors duration-300"
                                  :style="'background:' + theme.accent"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Vista previa</span>
                        </div>

                        {{-- Marco de teléfono --}}
                        <div class="relative w-[300px] shrink-0">
                            {{-- Cuerpo del teléfono --}}
                            <div class="rounded-[2.5rem] border-[10px] border-gray-800 shadow-2xl overflow-hidden h-[580px]">

                                {{-- Pantalla de la carta --}}
                                <div class="h-full flex flex-col overflow-hidden transition-colors duration-500"
                                     :style="'background:' + theme.phoneBg + '; font-family:' + theme.font">

                                    {{-- HEADER --}}
                                    <div class="shrink-0 flex items-center justify-between px-4 py-3 border-b transition-colors duration-500"
                                         :style="'background:' + theme.headerBg + '; border-color:' + theme.border">
                                        <div>
                                            <div class="font-bold text-sm leading-tight transition-colors duration-300"
                                                 :style="'color:' + theme.text + '; font-family:' + theme.fontDisplay">
                                                {{ Auth::user()->business_name ?? Auth::user()->name }}
                                            </div>
                                            <div class="text-[10px] mt-0.5 transition-colors duration-300"
                                                 :style="'color:' + theme.textSec">Mesa 3</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            {{-- Clásico: ornamento --}}
                                            <span x-show="menu_style==='classic'" class="text-base transition-all"
                                                  :style="'color:' + theme.accent">✦</span>
                                            {{-- Moderno: luna --}}
                                            <span x-show="menu_style==='modern'"
                                                  class="w-7 h-7 rounded-full flex items-center justify-center transition-all"
                                                  :style="'background:' + theme.chipBg">
                                                <svg class="h-3.5 w-3.5" :style="'color:' + theme.textSec" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                                </svg>
                                            </span>
                                            {{-- Minimal: menú --}}
                                            <span x-show="menu_style==='minimal'"
                                                  class="w-7 h-7 flex items-center justify-center transition-all border"
                                                  :style="'border-color:' + theme.border">
                                                <svg class="h-3.5 w-3.5" :style="'color:' + theme.text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- FILTROS --}}
                                    <div class="shrink-0 flex gap-2 px-3 py-2.5 overflow-x-auto border-b scrollbar-none transition-colors duration-500"
                                         :style="'background:' + theme.headerBg + '; border-color:' + theme.border">
                                        <template x-for="cat in demoCategories" :key="cat.id">
                                            <button type="button"
                                                    @click="demoFilter = cat.id"
                                                    class="shrink-0 text-xs px-3 py-1 transition-all duration-200 whitespace-nowrap"
                                                    :style="demoFilter === cat.id
                                                        ? 'background:' + theme.chipActiveBg + '; color:' + theme.chipActiveTxt + '; border-radius:' + theme.chipRadius + '; border:1px solid ' + theme.chipActiveBg
                                                        : 'background:' + theme.chipBg + '; color:' + theme.textSec + '; border-radius:' + theme.chipRadius + '; border:1px solid ' + theme.border"
                                                    :aria-pressed="demoFilter === cat.id ? 'true' : 'false'"
                                                    x-text="cat.label"></button>
                                        </template>
                                    </div>

                                    {{-- PRODUCTOS --}}
                                    <div class="flex-1 overflow-y-auto p-3 space-y-2.5 scrollbar-none">
                                        <template x-for="p in demoFiltered" :key="p.id">
                                            <div class="flex gap-2.5 p-2.5 border transition-all duration-300"
                                                 :style="'background:' + theme.cardBg + '; border-color:' + theme.cardBorder + '; border-radius:' + theme.cardRadius + '; box-shadow:' + theme.shadow">

                                                {{-- Foto placeholder --}}
                                                <div class="shrink-0 w-14 h-14 flex items-center justify-center text-2xl transition-all duration-300"
                                                     :style="'background:' + theme.imageBg + '; border-radius:' + theme.imageRadius">
                                                    <span x-text="p.emoji"></span>
                                                </div>

                                                {{-- Info --}}
                                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                                    <div>
                                                        <div class="text-sm font-semibold leading-tight transition-colors duration-300"
                                                             :style="'color:' + theme.text + '; font-family:' + theme.fontDisplay"
                                                             x-text="p.name"></div>
                                                        <div class="text-[11px] mt-0.5 transition-colors duration-300"
                                                             :style="'color:' + theme.textSec"
                                                             x-text="p.desc"></div>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1.5">
                                                        <span class="text-sm font-bold transition-colors duration-300"
                                                              :style="'color:' + theme.price"
                                                              x-text="p.price.toFixed(2).replace('.', ',') + ' €'"></span>

                                                        {{-- Control cantidad --}}
                                                        <div class="flex items-center gap-1">
                                                            <template x-if="demoQty(p.id) > 0">
                                                                <button type="button" @click="demoRemove(p.id)"
                                                                        class="w-6 h-6 flex items-center justify-center text-sm font-bold transition-all duration-200 border"
                                                                        :style="'color:' + theme.btnText + '; background:#dc2626; border-color:#dc2626; border-radius:' + theme.btnRadius"
                                                                        :aria-label="'Quitar ' + p.name">−</button>
                                                            </template>
                                                            <template x-if="demoQty(p.id) > 0">
                                                                <span class="w-5 text-center text-xs font-bold transition-colors duration-300"
                                                                      :style="'color:' + theme.text"
                                                                      x-text="demoQty(p.id)"></span>
                                                            </template>
                                                            <button type="button" @click="demoAdd(p.id)"
                                                                    class="w-6 h-6 flex items-center justify-center text-sm font-bold transition-all duration-200 border"
                                                                    :style="'color:' + theme.btnText + '; background:' + theme.accent + '; border-color:' + theme.accent + '; border-radius:' + theme.btnRadius"
                                                                    :aria-label="'Añadir ' + p.name">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Estado vacío en filtro --}}
                                        <div x-show="demoFiltered.length === 0"
                                             class="text-center py-8 text-xs transition-colors duration-300"
                                             :style="'color:' + theme.textSec">
                                            No hay productos en esta categoría.
                                        </div>
                                    </div>

                                    {{-- CART BAR --}}
                                    <div class="shrink-0 transition-all duration-300"
                                         :class="demoCartCount > 0 ? 'translate-y-0 opacity-100' : 'translate-y-full opacity-0'"
                                         :style="'background:' + theme.cartBg + '; border-top: 1px solid ' + theme.cartBorder">
                                        <div class="flex items-center justify-between px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold"
                                                      x-text="demoCartCount"></span>
                                                <span class="text-white text-sm font-semibold transition-colors duration-300"
                                                      :style="menu_style === 'classic' ? 'font-family: Georgia, serif' : ''">
                                                    Ver pedido
                                                </span>
                                            </div>
                                            <span class="text-sm font-bold transition-colors duration-300"
                                                  :style="'color:' + theme.cartPrice"
                                                  x-text="demoCartTotal + ' €'"></span>
                                        </div>
                                    </div>

                                </div>{{-- /pantalla --}}
                            </div>{{-- /teléfono --}}

                            {{-- Botón home del teléfono --}}
                            <div class="mt-3 flex justify-center">
                                <div class="w-16 h-1 bg-gray-700 rounded-full opacity-60"></div>
                            </div>
                        </div>{{-- /marco teléfono --}}

                        {{-- Leyenda --}}
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center max-w-xs leading-relaxed">
                            Prueba los filtros de categoría y añade productos al carrito para ver cómo responde cada estilo.
                        </p>

                    </div>
                </section>

                <div class="flex justify-end pt-2 pb-4">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg
                                   bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                                   hover:bg-indigo-500 active:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition duration-150">
                        <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Guardar estilo</span>
                    </button>
                </div>

                </form>
            </div>{{-- /x-data --}}
        </div>
    </div>

    @push('scripts')
    <script>
    function menuStylePage(initialStyle) {
        return {
            menu_style:    initialStyle,
            savedMenuStyle: initialStyle,
            styleLabels:   { modern: 'Moderno', classic: 'Clásico', minimal: 'Minimalista' },

            /* ── demo state ── */
            demoFilter: 'all',
            demoCart:   {},
            demoCategories: [
                { id: 'all',    label: 'Todo' },
                { id: 'food',   label: 'Entrantes' },
                { id: 'drinks', label: 'Bebidas' },
            ],
            demoProducts: [
                { id: 1, name: 'Pizza Margherita', desc: 'Tomate, mozzarella, albahaca',  price: 12.00, cat: 'food',   emoji: '🍕' },
                { id: 2, name: 'Croquetas caseras', desc: 'Bechamel y jamón ibérico',      price: 8.50,  cat: 'food',   emoji: '🥘' },
                { id: 3, name: 'Agua mineral',      desc: '500 ml, con o sin gas',         price: 1.50,  cat: 'drinks', emoji: '💧' },
                { id: 4, name: 'Vino de la casa',   desc: 'Tinto, blanco o rosado',        price: 3.00,  cat: 'drinks', emoji: '🍷' },
            ],

            get demoFiltered() {
                return this.demoFilter === 'all'
                    ? this.demoProducts
                    : this.demoProducts.filter(p => p.cat === this.demoFilter);
            },
            get demoCartCount() {
                return Object.values(this.demoCart).reduce((s, q) => s + q, 0);
            },
            get demoCartTotal() {
                const total = Object.entries(this.demoCart).reduce((s, [id, q]) => {
                    const p = this.demoProducts.find(x => x.id == id);
                    return s + (p ? p.price * q : 0);
                }, 0);
                return total.toFixed(2).replace('.', ',');
            },
            demoQty(id)    { return this.demoCart[id] || 0; },
            demoAdd(id)    { this.demoCart = { ...this.demoCart, [id]: (this.demoCart[id] || 0) + 1 }; },
            demoRemove(id) {
                const q = (this.demoCart[id] || 0) - 1;
                const c = { ...this.demoCart };
                if (q <= 0) delete c[id]; else c[id] = q;
                this.demoCart = c;
            },
            demoReset() {
                this.demoCart   = {};
                this.demoFilter = 'all';
            },

            /* ── theme tokens (cambian con menu_style) ── */
            get theme() {
                const t = {
                    modern: {
                        wrapBg:        '#F3F4F6',
                        phoneBg:       '#FFFFFF',
                        headerBg:      '#FFFFFF',
                        border:        '#F3F4F6',
                        text:          '#111827',
                        textSec:       '#6B7280',
                        accent:        '#16A34A',
                        chipBg:        '#F3F4F6',
                        chipActiveBg:  '#16A34A',
                        chipActiveTxt: '#FFFFFF',
                        cardBg:        '#F9FAFB',
                        cardBorder:    '#F3F4F6',
                        cardRadius:    '16px',
                        imageBg:       '#DCFCE7',
                        imageRadius:   '12px',
                        price:         '#FBBF24',
                        btnText:       '#FFFFFF',
                        btnRadius:     '10px',
                        chipRadius:    '9999px',
                        cartBg:        'linear-gradient(180deg,#0F3D2A,#0A2E1F)',
                        cartBorder:    '#16A34A',
                        cartPrice:     '#FBBF24',
                        shadow:        '0 1px 2px rgba(0,0,0,.04), 0 4px 10px rgba(0,0,0,.05)',
                        font:          'system-ui, sans-serif',
                        fontDisplay:   'system-ui, sans-serif',
                    },
                    classic: {
                        wrapBg:        '#F4ECDA',
                        phoneBg:       '#FFFDF6',
                        headerBg:      '#F4ECDA',
                        border:        '#E3D6BB',
                        text:          '#2E2013',
                        textSec:       '#5C4A33',
                        accent:        '#7C5326',
                        chipBg:        '#E3D6BB',
                        chipActiveBg:  '#7C5326',
                        chipActiveTxt: '#FFFFFF',
                        cardBg:        '#FBF4E4',
                        cardBorder:    '#C9B690',
                        cardRadius:    '4px',
                        imageBg:       '#E3D6BB',
                        imageRadius:   '3px',
                        price:         '#A6791E',
                        btnText:       '#FFFFFF',
                        btnRadius:     '3px',
                        chipRadius:    '3px',
                        cartBg:        'linear-gradient(180deg,#8A5A22,#5A3A12)',
                        cartBorder:    '#A6791E',
                        cartPrice:     '#E0B864',
                        shadow:        '0 1px 2px rgba(0,0,0,.04)',
                        font:          'Georgia, serif',
                        fontDisplay:   'Georgia, serif',
                    },
                    minimal: {
                        wrapBg:        '#FAFAFA',
                        phoneBg:       '#FFFFFF',
                        headerBg:      '#FFFFFF',
                        border:        '#ECECEE',
                        text:          '#111111',
                        textSec:       '#52525B',
                        accent:        '#111111',
                        chipBg:        '#FFFFFF',
                        chipActiveBg:  '#111111',
                        chipActiveTxt: '#FFFFFF',
                        cardBg:        '#FFFFFF',
                        cardBorder:    '#ECECEE',
                        cardRadius:    '0px',
                        imageBg:       '#F4F4F5',
                        imageRadius:   '0px',
                        price:         '#111111',
                        btnText:       '#FFFFFF',
                        btnRadius:     '0px',
                        chipRadius:    '0px',
                        cartBg:        'linear-gradient(180deg,#18181B,#09090B)',
                        cartBorder:    '#232327',
                        cartPrice:     '#FAFAFA',
                        shadow:        'none',
                        font:          'Arial, sans-serif',
                        fontDisplay:   '"Courier New", monospace',
                    },
                };
                return t[this.menu_style] || t.modern;
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
