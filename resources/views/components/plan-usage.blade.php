@props(['planUsage'])

@php
    /**
     * Calcula el porcentaje de uso para una barra de progreso.
     * Devuelve 0 si el recurso es ilimitado.
     */
    $pct = function (array $resource): int {
        if ($resource['unlimited'] || $resource['limit'] === null) {
            return 0;
        }
        return (int) min(100, round($resource['current'] / $resource['limit'] * 100));
    };

    /**
     * Devuelve las clases de color según el porcentaje de uso.
     * ≥100 → rojo, ≥80 → naranja, <80 → indigo
     */
    $barColor = function (int $percent): string {
        if ($percent >= 100) {
            return 'bg-red-500';
        }
        if ($percent >= 80) {
            return 'bg-amber-500';
        }
        return 'bg-indigo-500';
    };

    $textColor = function (int $percent): string {
        if ($percent >= 100) {
            return 'text-red-500 dark:text-red-400';
        }
        if ($percent >= 80) {
            return 'text-amber-500 dark:text-amber-400';
        }
        return 'text-gray-600 dark:text-gray-400';
    };

    $resources = [
        'tables' => ['label' => 'Mesas',    'icon' => '🪑', 'data' => $planUsage['tables']],
        'staff'  => ['label' => 'Personal', 'icon' => '👥', 'data' => $planUsage['staff']],
        'floors' => ['label' => 'Plantas',  'icon' => '🏢', 'data' => $planUsage['floors']],
    ];
@endphp

<section aria-labelledby="plan-usage-heading"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">

    <div class="flex items-center justify-between mb-4">
        <h2 id="plan-usage-heading" class="text-sm font-semibold text-gray-900 dark:text-white">
            Plan
            @if($planUsage['plan'])
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                    {{ $planUsage['plan']->name }}
                </span>
            @endif
        </h2>
        @if($planUsage['plan'])
            <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ number_format($planUsage['plan']->price_monthly, 2, ',', '.') }}&nbsp;€/mes
            </span>
        @endif
    </div>

    <div class="space-y-4">
        @foreach($resources as $key => $resource)
            @php
                $data    = $resource['data'];
                $percent = $pct($data);
                $color   = $barColor($percent);
                $tColor  = $textColor($percent);
            @endphp

            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                        <span aria-hidden="true">{{ $resource['icon'] }}</span>
                        {{ $resource['label'] }}
                    </span>
                    <span class="text-xs {{ $tColor }} tabular-nums">
                        @if($data['unlimited'])
                            <span class="text-emerald-500 dark:text-emerald-400 font-medium">Ilimitado</span>
                        @else
                            {{ $data['current'] }} / {{ $data['limit'] }}
                        @endif
                    </span>
                </div>

                @if(!$data['unlimited'])
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5"
                         role="progressbar"
                         aria-valuenow="{{ $data['current'] }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $data['limit'] }}"
                         aria-label="{{ $resource['label'] }}: {{ $data['current'] }} de {{ $data['limit'] }}">
                        <div class="{{ $color }} h-1.5 rounded-full transition-all duration-300"
                             style="width: {{ $percent }}%"></div>
                    </div>

                    @if($percent >= 100)
                        <p class="mt-1 text-xs text-red-500 dark:text-red-400">
                            Límite alcanzado. Actualiza tu plan para continuar.
                        </p>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

</section>
