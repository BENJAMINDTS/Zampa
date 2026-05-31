{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Diálogo de exclusividad del menú del día — clases DS (modal + dm-excl).
     Sin x-data propio: hereda el scope Alpine de dailyMenuBanner. --}}

<div
    class="scrim modal--center"
    x-show="showExclusivityWarning"
    @click="showExclusivityWarning = false; $nextTick(() => {})"
    @keydown.escape.window="if (showExclusivityWarning) showExclusivityWarning = false"
    @keydown.tab.window="
        if (!showExclusivityWarning || !$el.contains(document.activeElement)) return;
        $event.preventDefault();
        const focusable = [...$el.querySelectorAll('button:not([disabled]),[href],input:not([disabled])')];
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        focusable[$event.shiftKey ? (idx - 1 + focusable.length) % focusable.length : (idx + 1) % focusable.length].focus();
    "
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="dm-excl-title"
    aria-describedby="dm-excl-desc"
    x-cloak
>
    <div class="modal__panel modal__panel--narrow" @click.stop>
        <div class="dm-excl">
            <div class="dm-excl__ic" aria-hidden="true">!</div>

            <div class="dm-excl__title" id="dm-excl-title">
                El menú del día va aparte
            </div>

            <div class="dm-excl__copy" id="dm-excl-desc">
                Para pedir el menú del día tu carrito debe estar vacío. Tienes
                <strong x-text="Alpine.store('cart').items.length + (Alpine.store('cart').items.length === 1 ? ' artículo' : ' artículos')"></strong>
                añadidos à la carte.
            </div>

            <div class="dm-excl__actions">
                <button type="button" class="btn-secondary"
                        @click="showExclusivityWarning = false">
                    Cancelar
                </button>
                <button type="button" class="btn-primary dm-excl__btnPrimary"
                        @click="clearCartAndOpen()">
                    Vaciar carrito y continuar
                </button>
            </div>
        </div>
    </div>
</div>
