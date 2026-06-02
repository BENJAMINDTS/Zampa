{{-- @author SebastianBCF --}}
{{-- @author AyrtonAlania --}}
{{-- Banner del Menú del Día — estructura idéntica al Design System (dm-banner).
     Se inserta al inicio del carta__body. Gestiona el stepper vía Alpine.
     Alpine component → resources/js/alpine/daily-menu-banner.js --}}
@props(['hash'])

{{-- ── Wrapper Alpine ──────────────────────────────────────────── --}}
<div
    x-data="dailyMenuBanner('{{ $hash }}')"
    x-show="!loading && menuData !== null"
    x-cloak
>
    {{-- ── Banner DS ────────────────────────────────────────────── --}}
    <div
        :class="'dm-banner' +
            (exiting   ? ' dm-banner--exit'      : '') +
            (stuck     ? ' dm-banner--stuck'     : '') +
            (agotado   ? ''                       : ' dm-banner--clickable')"
        :role="agotado ? 'status' : 'button'"
        :tabindex="agotado ? undefined : 0"
        :aria-label="agotado ? undefined : 'Abrir ' + (menuData?.menu?.title ?? 'Menú del día')"
        aria-live="polite"
        @click="!agotado && openStepper()"
        @keydown.enter.prevent="!agotado && openStepper()"
        @keydown.space.prevent="!agotado && openStepper()"
    >
        <div class="dm-banner__stamp" aria-hidden="true">
            <span class="dm-banner__stampMark">◇</span>
            <span class="dm-banner__stampLabel">MENÚ DEL DÍA</span>
        </div>

        <div class="dm-banner__body">
            <div class="dm-banner__date" x-text="dateLabel"></div>
            <div class="dm-banner__title" x-text="menuData?.menu?.title"></div>
            <div class="dm-banner__tagline"
                 x-text="menuData?.menu?.description"
                 x-show="menuData?.menu?.description"></div>
        </div>

        <div class="dm-banner__side">
            <template x-if="agotado">
                <span class="dm-banner__agotado" aria-label="Menú del día agotado por hoy">
                    <span class="dm-banner__agotadoDot" aria-hidden="true"></span>
                    Agotado por hoy
                </span>
            </template>
            <template x-if="!agotado">
                <span class="flex flex-col items-end gap-1.5">
                    <span class="dm-banner__price"
                          x-text="parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' €'"
                          :aria-label="'Precio ' + parseFloat(menuData?.menu?.price ?? 0).toFixed(2).replace('.', ',') + ' euros'">
                    </span>
                    <button type="button" class="dm-banner__cta"
                            @click.stop="openStepper()">
                        Ver menú
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.4"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 5l7 7-7 7"/>
                        </svg>
                    </button>
                </span>
            </template>
        </div>

        <button type="button" class="dm-banner__close"
                @click.stop="dismiss()"
                aria-label="Descartar anuncio del menú del día">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Diálogo de exclusividad ────────────────────────────── --}}
    <x-daily-menu-exclusivity-dialog />

    {{-- ── Stepper modal ──────────────────────────────────────── --}}
    <x-daily-menu-stepper :hash="$hash" />
</div>
