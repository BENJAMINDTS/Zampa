{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Indicador de disponibilidad del menú del día.
     Se renderiza dentro del banner (sin x-data propio), hereda el scope Alpine del banner. --}}

<div x-show="menuData?.menu?.max_per_day !== null" class="mt-1.5">

    <template x-if="menuData?.menu?.available_count === 0">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-red-500/20 text-red-300 border border-red-500/30"
              role="status">
            <span class="w-1.5 h-1.5 rounded-full bg-red-400" aria-hidden="true"></span>
            Agotado
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 0 && menuData?.menu?.available_count <= 3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30"
              role="status"
              :aria-label="`Últimos ${menuData.menu.available_count} menús disponibles`">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" aria-hidden="true"></span>
            <span x-text="`Últimos ${menuData.menu.available_count}`"></span>
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30"
              role="status"
              :aria-label="`Quedan ${menuData.menu.available_count} menús disponibles`">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400" aria-hidden="true"></span>
            <span x-text="`Quedan ${menuData.menu.available_count}`"></span>
        </span>
    </template>

</div>
