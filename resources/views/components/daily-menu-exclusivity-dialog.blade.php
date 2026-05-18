{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Dialog de exclusividad: se muestra cuando el usuario tiene ítems en el carrito
     y quiere abrir el menú del día. Hereda el scope Alpine del banner (sin x-data propio). --}}

<div
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="exclusivity-title"
    aria-describedby="exclusivity-desc"
    x-show="showExclusivityWarning"
    @keydown.escape.window="if (showExclusivityWarning) showExclusivityWarning = false"
    @keydown.tab.window="
        if (!showExclusivityWarning || !$el.contains(document.activeElement)) return;
        $event.preventDefault();
        const focusable = [...$el.querySelectorAll('button:not([disabled]),[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex=\'-1\'])')];
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        focusable[$event.shiftKey
            ? (idx - 1 + focusable.length) % focusable.length
            : (idx + 1) % focusable.length
        ].focus();
    "
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4"
    x-cloak
>
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"
         @click="showExclusivityWarning = false"
         aria-hidden="true"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-sm rounded-2xl
                bg-[#0E1A38] border border-blue-600/40
                shadow-2xl shadow-blue-900/50 p-5 z-10">

        <h3 id="exclusivity-title"
            class="text-white font-bold text-base mb-2">
            ¿Quieres pedir el Menú del Día?
        </h3>

        <p id="exclusivity-desc" class="text-blue-200 text-sm leading-relaxed mb-4">
            Tienes
            <span class="font-semibold text-white"
                  x-text="Alpine.store('cart').items.length"></span><span
                  x-text="Alpine.store('cart').items.length === 1 ? ' producto' : ' productos'"></span>
            en tu carrito à la carte. El Menú del Día y el pedido normal
            no pueden combinarse en la misma comanda.
            <br><br>
            Si continúas, <strong class="text-white">tu carrito actual se vaciará.</strong>
        </p>

        <div class="flex flex-col gap-2">

            {{-- Botón primario visual: camino seguro (cancelar) --}}
            <button
                @click="showExclusivityWarning = false"
                class="w-full py-2.5 px-4 rounded-lg font-semibold text-sm
                       bg-[#2E50B0] text-white hover:bg-[#3660CC]
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:ring-offset-2 focus:ring-offset-[#0E1A38]
                       transition-colors"
            >
                Volver a la carta
            </button>

            {{-- Botón destructivo: vaciar carrito y continuar --}}
            <button
                @click="clearCartAndOpen()"
                class="w-full py-2.5 px-4 rounded-lg font-medium text-sm
                       border border-red-500/60 text-red-400
                       hover:bg-red-500/10 hover:text-red-300
                       focus:outline-none focus:ring-2 focus:ring-red-500/50
                       focus:ring-offset-2 focus:ring-offset-[#0E1A38]
                       transition-colors"
            >
                Vaciar carrito y ver el menú
            </button>

        </div>
    </div>
</div>
