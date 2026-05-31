{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Indicador de disponibilidad del menú del día.
     Se renderiza dentro del banner (sin x-data propio), hereda el scope Alpine del banner. --}}

<div x-show="menuData?.menu?.max_per_day !== null" style="margin-top:6px">

    <template x-if="menuData?.menu?.available_count === 0">
        <span class="dm-banner__agotado" role="status">
            <span class="dm-banner__agotadoDot" aria-hidden="true"></span>
            Agotado por hoy
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 0 && menuData?.menu?.available_count <= 3">
        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:9999px;background:var(--color-amber-100);color:var(--color-amber-800);font-family:var(--font-body);font-size:11px;font-weight:700"
              role="status">
            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0" aria-hidden="true"></span>
            Quedan <span x-text="menuData?.menu?.available_count"></span>
        </span>
    </template>

</div>
