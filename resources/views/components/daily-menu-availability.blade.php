{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Indicador de disponibilidad del menú del día.
     Se renderiza dentro del banner (sin x-data propio), hereda el scope Alpine del banner. --}}

<div x-show="menuData?.menu?.max_per_day !== null" class="mt-[6px]">

    <template x-if="menuData?.menu?.available_count === 0">
        <span class="dm-banner__agotado" role="status">
            <span class="dm-banner__agotadoDot" aria-hidden="true"></span>
            Agotado por hoy
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 0 && menuData?.menu?.available_count <= 3">
        <span class="dm-banner__availWarn" role="status">
            <span class="dm-banner__availWarnDot" aria-hidden="true"></span>
            Quedan <span x-text="menuData?.menu?.available_count"></span>
        </span>
    </template>

</div>
