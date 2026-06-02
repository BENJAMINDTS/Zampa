{{--
 | Alerta de límite de plan.
 | Muestra aviso ámbar (≥80 %) o rojo (100 %) cuando un recurso se acerca
 | o alcanza el tope del plan. No renderiza nada si es ilimitado o < 80 %.
 |
 | @props
 |   current   int        Unidades actualmente en uso
 |   limit     int|null   Máximo del plan (null = ilimitado)
 |   label     string     Nombre plural del recurso (ej. "mesas", "miembros de personal")
--}}
@props([
    'current' => 0,
    'limit'   => null,
    'label'   => 'elementos',
])

@php
    if ($limit === null) return;          // ilimitado → nada
    $pct     = $limit > 0 ? round($current / $limit * 100) : 100;
    if ($pct < 80) return;               // cómodo → nada
    $atLimit = $pct >= 100;
@endphp

<div role="alert"
     class="flex items-start gap-3 px-4 py-3 rounded-xl border text-sm
            {{ $atLimit
                ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
                : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' }}">

    {{-- Icono triángulo de advertencia --}}
    <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $atLimit ? 'text-red-500 dark:text-red-400' : 'text-amber-500 dark:text-amber-400' }}"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
         aria-hidden="true">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>

    <div class="{{ $atLimit ? 'text-red-800 dark:text-red-200' : 'text-amber-800 dark:text-amber-200' }}">
        @if($atLimit)
            <p class="font-semibold leading-tight">Límite alcanzado</p>
            <p class="mt-0.5 text-xs opacity-80">
                Has llegado al máximo de <strong>{{ $limit }} {{ $label }}</strong> de tu plan.
                Actualiza tu plan para añadir más.
            </p>
        @else
            <p class="font-semibold leading-tight">Próximo al límite</p>
            <p class="mt-0.5 text-xs opacity-80">
                Usas <strong>{{ $current }} de {{ $limit }} {{ $label }}</strong>.
                Considera actualizar tu plan antes de alcanzar el tope.
            </p>
        @endif
    </div>
</div>
