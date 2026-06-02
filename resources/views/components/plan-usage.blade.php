@props(['planUsage'])

@php
    $pct = function (array $resource): int {
        if ($resource['unlimited'] || $resource['limit'] === null) {
            return 0;
        }
        return (int) min(100, round($resource['current'] / $resource['limit'] * 100));
    };

    $barColor = function (int $percent): string {
        if ($percent >= 100) return 'bg-red-500';
        if ($percent >= 80)  return 'bg-amber-500';
        return 'bg-indigo-500';
    };

    $textColor = function (int $percent): string {
        if ($percent >= 100) return 'text-red-600 dark:text-red-400 font-semibold';
        if ($percent >= 80)  return 'text-amber-600 dark:text-amber-400 font-semibold';
        return 'text-gray-900 dark:text-white font-semibold';
    };

    $resources = [
        'tables' => [
            'label' => 'Mesas',
            'data'  => $planUsage['tables'],
            'icon'  => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  <rect x="3" y="3" width="7" height="7" rx="1.5"/>
  <rect x="14" y="3" width="7" height="7" rx="1.5"/>
  <rect x="3" y="14" width="7" height="7" rx="1.5"/>
  <rect x="14" y="14" width="7" height="7" rx="1.5"/>
</svg>
SVG,
        ],
        'staff' => [
            'label' => 'Personal',
            'data'  => $planUsage['staff'],
            'icon'  => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  <path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
</svg>
SVG,
        ],
        'floors' => [
            'label' => 'Plantas',
            'data'  => $planUsage['floors'],
            'icon'  => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  <path d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
</svg>
SVG,
        ],
    ];
@endphp

<section aria-labelledby="plan-usage-heading"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                border border-gray-200 dark:border-gray-700 px-5 py-4">

    <div class="flex flex-wrap items-center gap-5 sm:gap-8">

        {{-- ── Plan badge ─────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30
                        flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                </svg>
            </div>
            <div>
                <p id="plan-usage-heading"
                   class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                    @if($planUsage['plan'])
                        {{ $planUsage['plan']->name }}
                    @else
                        Sin plan
                    @endif
                </p>
                @if($planUsage['plan'])
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 tabular-nums">
                        {{ number_format($planUsage['plan']->price_monthly, 2, ',', '.') }}&nbsp;€/mes
                    </p>
                @endif
            </div>
        </div>

        {{-- ── Separator ───────────────────────────────────────────── --}}
        <div class="hidden sm:block self-stretch w-px bg-gray-200 dark:bg-gray-700"
             aria-hidden="true"></div>

        {{-- ── Resources ───────────────────────────────────────────── --}}
        <div class="flex flex-wrap gap-5 sm:gap-8 flex-1">
            @foreach($resources as $key => $resource)
                @php
                    $data    = $resource['data'];
                    $percent = $pct($data);
                    $color   = $barColor($percent);
                    $tColor  = $textColor($percent);
                @endphp

                <div class="flex items-center gap-2.5 min-w-[130px] flex-1">
                    {{-- Icon --}}
                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700
                                flex items-center justify-center shrink-0
                                {{ $percent >= 100 ? 'bg-red-50 dark:bg-red-900/20' : ($percent >= 80 ? 'bg-amber-50 dark:bg-amber-900/20' : '') }}">
                        <span class="w-4 h-4 {{ $percent >= 100 ? 'text-red-500 dark:text-red-400' : ($percent >= 80 ? 'text-amber-500 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400') }}">
                            {!! $resource['icon'] !!}
                        </span>
                    </div>

                    {{-- Label + bar --}}
                    <div class="flex-1 min-w-0"
                         role="group"
                         aria-label="{{ $resource['label'] }}">
                        <div class="flex items-baseline justify-between gap-2 mb-1.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $resource['label'] }}
                            </span>
                            <span class="text-xs {{ $tColor }} tabular-nums shrink-0">
                                @if($data['unlimited'])
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">∞</span>
                                @else
                                    {{ $data['current'] }}<span class="font-normal text-gray-400 dark:text-gray-500">/{{ $data['limit'] }}</span>
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
                                <div class="{{ $color }} h-1.5 rounded-full transition-all duration-500"
                                     style="width: {{ $percent }}%"></div>
                            </div>
                            @if($percent >= 100)
                                <p class="mt-1 text-xs text-red-500 dark:text-red-400 leading-snug">
                                    Límite alcanzado
                                </p>
                            @endif
                        @else
                            <div class="h-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30" aria-hidden="true"></div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</section>
