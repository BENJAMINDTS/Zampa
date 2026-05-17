# Diseño UI/UX — Menú del Día (Bloque 14.0)

> Documento de diseño aprobable. La implementación del Bloque 14.5 no debe
> iniciarse hasta que este documento esté mergeado y aprobado por el equipo.

---

## 1. Decisión de patrón de presentación

### Patrón elegido: B — Card destacada dentro del `<main>`, antes de la primera categoría

**Punto de inserción exacto:** `resources/views/menu/show.blade.php`, inmediatamente
después de la apertura de `<main id="main-content" ...>` y antes del primer `@foreach`
de categorías.

### Wireframe ASCII — Mobile (375px)

```
┌───────────────────────────────────────┐
│ ░ skip-to-content (sr-only)           │
├───────────────────────────────────────┤
│ HEADER  sticky top-0  z-40            │
│  Carta digital                        │
│  Bar La Esquina            Mesa 4     │
├───────────────────────────────────────┤
│ FILTROS ALÉRGENOS  (panel colapsable) │
│  [Gluten][Lactosa][Frutos secos] ...  │
├───────────────────────────────────────┤
│ CATEGORÍAS  (chips scroll horizontal) │
│  ‹ [Todo][Entrantes][Carnes][...] ›   │
├═══════════════════════════════════════┤
│ <main id="main-content">              │
│                                       │
│ ╔═══════════════════════════════════╗ │  ← MENÚ DEL DÍA (card full-width)
│ ║  MENÚ DEL DÍA · 15 MAY           ║ │
│ ║  gradiente azul #2E50B0→#1A3380  ║ │
│ ║                                  ║ │
│ ║  Primero · Segundo · Postre      ║ │
│ ║  Bebida incluida                 ║ │
│ ║                                  ║ │
│ ║  [ Ver el menú completo → ]      ║ │
│ ║                    [14,50 €]     ║ │
│ ╚═══════════════════════════════════╝ │
│                                       │
│  ── Entrantes ──────────────────────  │
│  ┌─────────────────────────────────┐  │
│  │ [img] Croquetas        4,50 €   │  │
│  └─────────────────────────────────┘  │
│  ...                                  │
│ </main>                               │
│                                       │
│              FAB Chat Zampi  ●        │  fixed bottom-20 right-4
│  FAB Cuenta          FAB Carrito 🛒   │  bottom-6 left-4 / right-4
└───────────────────────────────────────┘
```

Cuando el menú del día **no está activo**: la card no se renderiza. Cero impacto
en el layout existente.

### Wireframe ASCII — Desktop (1280px)

```
┌──────────────────────────────────────────────────────────────────────────┐
│ HEADER sticky    Carta digital — Bar La Esquina                  Mesa 4   │
├──────────────────────────────────────────────────────────────────────────┤
│ FILTROS ALÉRGENOS    [Gluten] [Lactosa] [Frutos secos] [Huevo] ...        │
├──────────────────────────────────────────────────────────────────────────┤
│ CATEGORÍAS   [Todo] [Entrantes] [Carnes] [Pescados] [Postres] [Bebidas]   │
├──────────────────────────────────────────────────────────────────────────┤
│            ┌───────────── main  max-w-3xl mx-auto ──────────────┐         │
│            │                                                    │         │
│            │ ╔════════════════════════════════════════════════╗ │  ← NUEVO│
│            │ ║ MENÚ DEL DÍA · 15 MAY                         ║ │         │
│            │ ║ Primero · Segundo · Postre · Bebida incluida   ║ │         │
│            │ ║                                                ║ │         │
│            │ ║ [ Ver el menú completo → ]         14,50 €    ║ │         │
│            │ ╚════════════════════════════════════════════════╝ │         │
│            │                                                    │         │
│            │  ── Entrantes ───────────────────────────────────  │         │
│            │  ┌────────────────┐  ┌────────────────┐            │         │
│            │  │[img] Croquetas │  │[img] Ensalada  │            │         │
│            │  └────────────────┘  └────────────────┘            │         │
│            └────────────────────────────────────────────────────┘         │
│   FAB Cuenta (bottom-left)                  FAB Chat / Carrito (right)    │
└──────────────────────────────────────────────────────────────────────────┘
```

### Justificación

El Menú del Día es una decisión excluyente ("menú O à la carte"), por lo que debe
presentarse como bifurcación de flujo en el punto exacto donde el usuario empieza a
decidir qué comer: el inicio del `<main>`, antes de las categorías. Una card full-width
con gradiente de marca gana jerarquía visual sin competir con elementos persistentes ni
añadir capas flotantes nuevas. Degrada limpiamente a cero: si no hay menú activo, no se
renderiza y la carta queda idéntica a hoy sin reservar espacio.

### Descartes

- **A) Hero banner sticky al top:** un segundo sticky roba viewport vertical permanente
  en 375–430px empujando filtros y carta hacia abajo incluso cuando el usuario ya eligió
  à la carte.
- **C) Floating action button (bottom-left):** la esquina inferior izquierda ya está
  ocupada por el FAB "Solicitar cuenta" (`fixed bottom-6 left-4 z-50`); un cuarto FAB
  provoca colisión de z-index y sobrecarga táctil.

---

## 2. Flujo de selección y timing

### 2.1 Construcción dinámica del stepper

Los pasos se generan a partir de las secciones configuradas por el gerente. Orden canónico:

| Posición | Clave interna | Nombre en la UI |
|----------|---------------|-----------------|
| 1 | `first_course` | Primer plato |
| 2 | `second_course` | Segundo plato |
| 3 | `dessert` | Postre |
| 4 | `coffee` | Café |
| 5 | `drink` | Bebida |
| 6 | `bread` | Pan |
| N | `timing_N` | ¿Cuándo quieres el [siguiente plato]? |
| Final | `confirm` | Confirmar pedido |

Las secciones no configuradas no aparecen en el array de pasos. Los pasos de timing
se intercalan entre rondas. Si solo hay una ronda, no hay pasos de timing.

### 2.2 Obligatorio vs opcional

- `first_course`, `second_course`: **obligatorios** — botón "Siguiente" deshabilitado hasta selección.
- `dessert`, `coffee`, `drink`, `bread`: **opcionales** — enlace "Sin [sección], gracias →" al pie del paso.
- Los pasos de timing siempre tienen un valor por defecto y nunca bloquean el avance.

### 2.3 Control de timing

- Incremento: **5 minutos por toque** con botones `[−]` y `[+]` (no slider).
- Justificación táctil: en mobile, un slider de 320px con rango de 120 min tiene
  precisión de ±9 min por toque humano. Los botones eliminan el error accidental.
- Rango: `[estimated_prep_minutes ... 120]` minutos.
- El botón `[−]` se bloquea al llegar al mínimo (`estimated_prep_minutes`).
- Hora estimada en tiempo real: `horaActual + delay_acumulado_rondas_anteriores + delay_ronda_actual`.
- Texto: `"Tu segundo plato llegará aprox. a las 14:32"`.

### 2.4 Exclusividad con el carrito

**Condición de activación:** `Alpine.store('cart').items.length > 0` al pulsar el botón de apertura.

**Texto del modal:**
> ¿Quieres pedir el Menú del Día?
>
> Tienes [N] producto[s] en tu carrito à la carte. El Menú del Día y el pedido
> normal no pueden combinarse en la misma comanda.
>
> Si continúas, tu carrito actual se vaciará.

**Botones (jerarquía invertida — el camino seguro tiene el peso visual mayor):**

| Botón | Etiqueta | Estilo |
|-------|----------|--------|
| Cancelar (primario visual) | "Volver a la carta" | Fondo `#2E50B0`, texto blanco |
| Destructivo (secundario visual) | "Vaciar carrito y ver el menú" | Borde rojo, fondo transparente |

**Acción al confirmar el vaciado:**
```javascript
Alpine.store('cart').items = []
Alpine.store('cart')._barItemsCount = 0
Alpine.store('cart')._variantsUsed  = 0
Alpine.store('cart').sent           = false
Alpine.store('cart').error          = null
this.showExclusivityWarning = false
this.open = true
```

### 2.5 Confirmación y estado post-envío

El resumen muestra selecciones agrupadas por ronda con hora estimada. El botón confirma:
`"Confirmar menú y enviar pedido"`. Tras éxito, el stepper muestra pantalla de confirmación
con los horarios. La card en el `<main>` pasa a estado "Pedido enviado ✓" (no clickeable).

---

## 3. Código de referencia Blade + Tailwind

### 3.1 Componente `<x-daily-menu-banner>`

```blade
{{-- resources/views/components/daily-menu-banner.blade.php --}}
@props(['hash'])

<div
    x-data="{
        menuData: null,
        loading: true,
        open: false,
        showExclusivityWarning: false,

        async init() {
            try {
                const res = await fetch('/api/v1/menu/{{ $hash }}/daily-menu');
                const json = await res.json();
                this.menuData = json.data;
            } catch (e) {
                this.menuData = null;
            } finally {
                this.loading = false;
            }
        },

        openStepper() {
            if (Alpine.store('cart').items.length > 0) {
                this.showExclusivityWarning = true;
            } else {
                this.open = true;
            }
        },

        clearCartAndOpen() {
            Alpine.store('cart').items = [];
            Alpine.store('cart')._barItemsCount = 0;
            Alpine.store('cart')._variantsUsed  = 0;
            Alpine.store('cart').sent           = false;
            Alpine.store('cart').error          = null;
            this.showExclusivityWarning = false;
            this.open = true;
        },

        close() {
            this.open = false;
            this.$nextTick(() => this.$refs.openButton.focus());
        }
    }"
    x-show="!loading && menuData !== null"
    x-cloak
    class="mb-4"
>
    {{-- Card del banner --}}
    <div class="rounded-xl border border-blue-600/40 bg-gradient-to-br
                from-[#2E50B0] to-[#1A3380]
                dark:from-[#1A3380] dark:to-[#0E1A38]
                p-4 shadow-lg shadow-blue-900/40">

        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
                         aria-hidden="true">
                        {{-- icono cubiertos --}}
                        <path d="M..."/>
                    </svg>
                    <span class="text-xs font-semibold uppercase tracking-widest
                                 text-blue-200 dark:text-blue-300">
                        Menú del día
                    </span>
                </div>
                <h2 class="text-white font-bold text-lg leading-snug font-['Space_Grotesk']"
                    x-text="menuData?.menu?.title">
                </h2>
                <p class="text-blue-200 text-sm mt-0.5"
                   x-text="menuData?.menu?.description">
                </p>

                {{-- Secciones disponibles como pills --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <template x-for="section in menuData?.sections" :key="section.id">
                        <span class="text-xs px-2 py-0.5 rounded-full
                                     bg-white/10 text-blue-100">
                            <span x-text="section.type_label"></span>
                            <span x-show="section.is_free"
                                  class="text-yellow-300"> · incluido</span>
                        </span>
                    </template>
                </div>
            </div>

            {{-- Badge de precio --}}
            <div class="flex-shrink-0 text-right">
                <span class="text-2xl font-bold text-white font-['Space_Grotesk']"
                      x-text="menuData?.menu?.price + ' €'">
                </span>
                {{-- Indicador de disponibilidad (componente 3.4) --}}
                <x-daily-menu-availability :menuData="menuData" />
            </div>
        </div>

        {{-- Botón de apertura --}}
        <button
            x-ref="openButton"
            @click="openStepper()"
            :disabled="menuData?.menu?.available_count === 0"
            aria-haspopup="dialog"
            :aria-expanded="open"
            aria-controls="daily-menu-stepper"
            class="mt-3 w-full py-2.5 px-4 rounded-lg font-semibold text-sm
                   bg-yellow-400 text-gray-900 hover:bg-yellow-300
                   focus:outline-none focus:ring-2 focus:ring-yellow-400
                   focus:ring-offset-2 focus:ring-offset-blue-900
                   disabled:opacity-40 disabled:cursor-not-allowed
                   transition-colors duration-150"
        >
            Ver el menú completo
        </button>
    </div>

    {{-- Dialog de exclusividad (componente 3.5) --}}
    <x-daily-menu-exclusivity-dialog
        x-show="showExclusivityWarning"
        @confirm="clearCartAndOpen()"
        @cancel="showExclusivityWarning = false"
    />

    {{-- Stepper (componente 3.2) --}}
    <x-daily-menu-stepper
        :hash="$hash"
        x-show="open"
        @close="close()"
    />
</div>
```

### 3.2 Contenedor del stepper (modal)

```blade
{{-- resources/views/components/daily-menu-stepper.blade.php --}}
@props(['hash'])

<div
    id="daily-menu-stepper"
    role="dialog"
    aria-modal="true"
    aria-labelledby="stepper-title"
    @keydown.escape.window="$dispatch('close')"
    @keydown.tab.window="
        if (!open || !$el.contains(document.activeElement)) return;
        $event.preventDefault();
        const focusable = [...$el.querySelectorAll('button:not([disabled]),[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex=-1])')];
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        focusable[$event.shiftKey ? (idx - 1 + focusable.length) % focusable.length : (idx + 1) % focusable.length].focus();
    "
    class="fixed inset-0 z-50 flex flex-col
           bg-[#050B1F]/95 backdrop-blur-sm
           sm:items-center sm:justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
>
    {{-- Panel interior --}}
    <div class="relative flex flex-col w-full h-full
                sm:h-auto sm:max-w-lg sm:max-h-[90vh]
                sm:rounded-2xl sm:border sm:border-blue-600/40
                bg-[#0E1A38] overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 pt-4 pb-3
                    border-b border-blue-800/40 flex-shrink-0">
            <h2 id="stepper-title"
                class="text-white font-bold text-base font-['Space_Grotesk']">
                Menú del Día
            </h2>

            {{-- Indicador de pasos (dots) --}}
            <nav aria-label="Pasos del menú del día"
                 class="flex items-center gap-1.5 mx-auto">
                <template x-for="(paso, idx) in pasos" :key="idx">
                    <button
                        @click="irAPaso(idx)"
                        :aria-current="pasoActual === idx ? 'step' : undefined"
                        :aria-label="`Paso ${idx + 1} de ${pasos.length}: ${paso.label}`"
                        :class="{
                            'w-2.5 h-2.5 rounded-full transition-all': true,
                            'bg-[#2E50B0] scale-125': pasoActual === idx,
                            'bg-[#2E50B0]/40': pasoActual !== idx && idx < pasoActual,
                            'bg-white/20': idx > pasoActual
                        }"
                    ></button>
                </template>
            </nav>

            {{-- Botón cerrar --}}
            <button
                @click="$dispatch('close')"
                aria-label="Cerrar menú del día"
                class="p-2 rounded-lg text-blue-300 hover:text-white
                       hover:bg-white/10 focus:outline-none
                       focus:ring-2 focus:ring-blue-400 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Barra de progreso --}}
        <div class="h-0.5 bg-white/10 flex-shrink-0" aria-hidden="true">
            <div class="h-full bg-[#2E50B0] transition-all duration-300"
                 :style="`width: ${((pasoActual + 1) / pasos.length) * 100}%`">
            </div>
        </div>

        {{-- Área de contenido scrollable --}}
        <div class="flex-1 overflow-y-auto px-4 py-4">
            {{-- Contenido del paso actual se renderiza aquí --}}
            <div x-show="pasoActualObj?.tipo === 'seleccion'">
                {{-- Paso de selección de producto --}}
            </div>
            <div x-show="pasoActualObj?.tipo === 'timing'">
                {{-- Paso de timing (componente 3.3) --}}
                <x-daily-menu-timing-control />
            </div>
            <div x-show="pasoActualObj?.tipo === 'confirmacion'">
                {{-- Paso de confirmación --}}
            </div>
        </div>

        {{-- Footer de navegación --}}
        <div class="flex items-center justify-between gap-3
                    px-4 py-3 border-t border-blue-800/40 flex-shrink-0">
            <button
                x-show="pasoActual > 0"
                @click="anterior()"
                class="px-4 py-2.5 rounded-lg text-sm font-medium
                       text-blue-300 hover:text-white hover:bg-white/10
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       transition-colors"
            >
                ← Anterior
            </button>
            <div x-show="pasoActual === 0" class="w-10"></div>

            <button
                x-show="pasoActualObj?.tipo !== 'confirmacion'"
                @click="siguiente()"
                :disabled="!puedeAvanzar"
                :aria-disabled="!puedeAvanzar"
                :aria-label="`Ir al paso siguiente: ${pasos[pasoActual + 1]?.label ?? ''}`"
                :class="{
                    'px-5 py-2.5 rounded-lg text-sm font-semibold transition-all': true,
                    'bg-[#2E50B0] text-white hover:bg-[#3660CC]': puedeAvanzar,
                    'bg-white/10 text-white/40 cursor-not-allowed': !puedeAvanzar
                }"
            >
                Siguiente →
            </button>

            <button
                x-show="pasoActualObj?.tipo === 'confirmacion'"
                @click="submitOrder()"
                :disabled="enviando"
                :aria-busy="enviando"
                aria-describedby="confirmation-summary"
                class="flex-1 py-2.5 px-4 rounded-lg font-semibold text-sm
                       bg-yellow-400 text-gray-900 hover:bg-yellow-300
                       focus:outline-none focus:ring-2 focus:ring-yellow-400
                       disabled:opacity-50 disabled:cursor-not-allowed
                       transition-colors"
            >
                <span x-show="!enviando">Confirmar menú y enviar pedido</span>
                <span x-show="enviando" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Enviando pedido...
                </span>
            </button>
        </div>
    </div>
</div>
```

### 3.3 Control de timing `[−] X min [+]`

```blade
{{-- resources/views/components/daily-menu-timing-control.blade.php --}}
@props(['round', 'defaultDelay', 'minDelay', 'label'])

<div class="py-2"
     role="group"
     :aria-labelledby="`timing-label-{{ $round }}`">

    <p id="timing-label-{{ $round }}"
       class="text-white font-semibold mb-3 text-base">
        {{ $label }}
    </p>

    <div class="flex items-center justify-center gap-4 my-4">

        {{-- Botón menos --}}
        <button
            @click="decrementTiming({{ $round }}, {{ $minDelay }})"
            :disabled="timings[{{ $round }}] <= {{ $minDelay }}"
            :aria-disabled="timings[{{ $round }}] <= {{ $minDelay }}"
            aria-label="Reducir tiempo de la ronda {{ $round }}"
            :class="{
                'w-11 h-11 rounded-full flex items-center justify-center': true,
                'border-2 border-blue-600/60 text-blue-300': true,
                'hover:bg-blue-800/50 hover:text-white transition-colors': timings[{{ $round }}] > {{ $minDelay }},
                'opacity-35 cursor-not-allowed': timings[{{ $round }}] <= {{ $minDelay }}
            }"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M20 12H4"/>
            </svg>
        </button>

        {{-- Valor actual --}}
        <div class="text-center min-w-[80px]"
             role="spinbutton"
             :aria-valuemin="{{ $minDelay }}"
             aria-valuemax="120"
             :aria-valuenow="timings[{{ $round }}]"
             :aria-valuetext="timings[{{ $round }}] + ' minutos'">
            <span class="text-4xl font-bold text-white font-['Space_Grotesk']"
                  x-text="timings[{{ $round }}]">
            </span>
            <span class="text-blue-300 text-sm ml-1">min</span>
        </div>

        {{-- Botón más --}}
        <button
            @click="incrementTiming({{ $round }})"
            :disabled="timings[{{ $round }}] >= 120"
            :aria-disabled="timings[{{ $round }}] >= 120"
            aria-label="Aumentar tiempo de la ronda {{ $round }}"
            :class="{
                'w-11 h-11 rounded-full flex items-center justify-center': true,
                'border-2 border-blue-600/60 text-blue-300': true,
                'hover:bg-blue-800/50 hover:text-white transition-colors': timings[{{ $round }}] < 120,
                'opacity-35 cursor-not-allowed': timings[{{ $round }}] >= 120
            }"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>

    {{-- Hora estimada de llegada --}}
    <div aria-live="polite" aria-atomic="true"
         class="text-center text-blue-200 text-sm">
        <span x-text="`Tu plato llegará aprox. a las ${horasEstimadas[{{ $round }}]}`">
        </span>
    </div>

    {{-- Nota de tiempo mínimo --}}
    <p class="text-center text-blue-400/70 text-xs mt-1">
        Tiempo mínimo de preparación: {{ $minDelay }} min
    </p>
</div>
```

### 3.4 Indicador de disponibilidad

```blade
{{-- resources/views/components/daily-menu-availability.blade.php --}}
@props(['menuData'])

<div x-show="menuData?.menu?.max_per_day !== null" class="mt-1.5">
    <template x-if="menuData?.menu?.available_count === 0">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-red-500/20 text-red-300
                     border border-red-500/30"
              role="status">
            <span class="w-1.5 h-1.5 rounded-full bg-red-400" aria-hidden="true"></span>
            Agotado
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 0 && menuData?.menu?.available_count <= 3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-amber-500/20 text-amber-300
                     border border-amber-500/30"
              role="status"
              :aria-label="`Últimos ${menuData.menu.available_count} menús disponibles`">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"
                  aria-hidden="true"></span>
            <span x-text="`Últimos ${menuData.menu.available_count}`"></span>
        </span>
    </template>

    <template x-if="menuData?.menu?.available_count > 3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                     text-xs font-medium bg-green-500/20 text-green-300
                     border border-green-500/30"
              role="status"
              :aria-label="`Quedan ${menuData.menu.available_count} menús disponibles`">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400" aria-hidden="true"></span>
            <span x-text="`Quedan ${menuData.menu.available_count}`"></span>
        </span>
    </template>
</div>
```

### 3.5 Dialog de exclusividad

```blade
{{-- resources/views/components/daily-menu-exclusivity-dialog.blade.php --}}

<div
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="exclusivity-title"
    aria-describedby="exclusivity-desc"
    @keydown.escape.window="showExclusivityWarning = false"
    @keydown.tab.window="
        if (!showExclusivityWarning || !$el.contains(document.activeElement)) return;
        $event.preventDefault();
        const focusable = [...$el.querySelectorAll('button:not([disabled]),[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex=-1])')];
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        focusable[$event.shiftKey ? (idx - 1 + focusable.length) % focusable.length : (idx + 1) % focusable.length].focus();
    "
    class="fixed inset-0 z-[60] flex items-end sm:items-center
           justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
>
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"
         @click="showExclusivityWarning = false"
         aria-hidden="true">
    </div>

    {{-- Panel --}}
    <div class="relative w-full max-w-sm rounded-2xl
                bg-[#0E1A38] border border-blue-600/40
                shadow-2xl shadow-blue-900/50 p-5 z-10">

        <h3 id="exclusivity-title"
            class="text-white font-bold text-base font-['Space_Grotesk'] mb-2">
            ¿Quieres pedir el Menú del Día?
        </h3>

        <p id="exclusivity-desc" class="text-blue-200 text-sm leading-relaxed mb-4">
            Tienes
            <span class="font-semibold text-white"
                  x-text="Alpine.store('cart').items.length"></span>
            <span x-text="Alpine.store('cart').items.length === 1
                          ? ' producto' : ' productos'"></span>
            en tu carrito à la carte. El Menú del Día y el pedido normal
            no pueden combinarse en la misma comanda.
            <br><br>
            Si continúas, <strong class="text-white">tu carrito actual se vaciará.</strong>
        </p>

        {{-- Botones — jerarquía invertida: seguro primario, destructivo secundario --}}
        <div class="flex flex-col gap-2">
            {{-- Botón primario visual: CANCELAR (camino seguro) --}}
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

            {{-- Botón destructivo: borde rojo, fondo transparente --}}
            <button
                @click="$dispatch('confirm')"
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
```

---

## 4. Checklist de accesibilidad WCAG 2.1 AA

### BANNER / CARD DE ANUNCIO
- [ ] Contraste texto/fondo mínimo 4.5:1 en modo light y dark (SC 1.4.3)
- [ ] Badge de precio: contraste mínimo 3:1 para texto grande (SC 1.4.11)
- [ ] Botón de apertura con `aria-haspopup="dialog"` (SC 4.1.2)
- [ ] Botón con nombre accesible explícito: texto visible o `aria-label` (SC 2.4.6)

### MODAL DEL STEPPER
- [ ] `role="dialog"` y `aria-modal="true"` (SC 4.1.2)
- [ ] `aria-labelledby` apuntando al título del modal (SC 1.3.1)
- [ ] Focus trap activo: Tab y Shift+Tab no salen del modal (SC 2.1.2)
- [ ] Al abrir: el foco va al primer elemento interactivo (SC 2.4.3)
- [ ] Al cerrar: el foco vuelve al botón que abrió el modal (SC 2.4.3)
- [ ] Cierre con tecla Escape (SC 2.1.2)
- [ ] Botón ✕ con `aria-label="Cerrar menú del día"` (SC 2.4.6)

### STEPPER (INDICADOR DE PASOS)
- [ ] `aria-current="step"` en el paso activo (SC 1.3.1)
- [ ] `aria-label` descriptivo en cada indicador de paso (SC 2.4.6)
- [ ] Pasos completados marcados visualmente Y con atributo ARIA (SC 1.3.3)

### SELECCIÓN DE PRODUCTOS
- [ ] Cada opción es un elemento interactivo (`button` o `input radio`) (SC 4.1.2)
- [ ] Estado seleccionado con ARIA (`aria-pressed` o `aria-checked`) (SC 4.1.2)
- [ ] No depender solo del color: añadir icono ✓ al seleccionar (SC 1.4.1)
- [ ] Imágenes de producto con `alt` descriptivo (SC 1.1.1)

### SECCIONES OBLIGATORIAS
- [ ] Botón "Siguiente" con `disabled` y `aria-disabled="true"` sin selección (SC 4.1.2)
- [ ] Mensaje de error con `role="alert"` al intentar avanzar sin selección (SC 3.3.1)

### CONTROL DE TIMING
- [ ] Botones `[−]` y `[+]` con `aria-label` ("Reducir tiempo" / "Aumentar tiempo") (SC 2.4.6)
- [ ] `aria-valuemin`, `aria-valuemax` y `aria-valuenow` actualizados dinámicamente (SC 4.1.2)
- [ ] `aria-live="polite"` en la región de hora estimada de llegada (SC 4.1.3)
- [ ] Botón `[−]` con `aria-disabled="true"` al llegar al mínimo (SC 4.1.2)

### CONFIRMACIÓN Y ENVÍO
- [ ] Botón "Confirmar" con `aria-describedby` al resumen de selecciones (SC 2.4.6)
- [ ] Estado de carga con `aria-busy="true"` durante el POST (SC 4.1.3)
- [ ] Mensaje de éxito/error con `role="alert"` tras la respuesta (SC 4.1.3)

### DIALOG DE EXCLUSIVIDAD
- [ ] `role="alertdialog"` y `aria-modal="true"` (SC 4.1.2)
- [ ] `aria-labelledby` y `aria-describedby` definidos (SC 1.3.1)
- [ ] Focus trap activo (SC 2.1.2)
- [ ] Cierre con Escape (SC 2.1.2)
- [ ] Jerarquía visual clara: botón seguro con mayor peso visual que el destructivo (SC 1.4.1)

### GENERAL
- [ ] Tab order lógico en todo el flujo (SC 2.4.3)
- [ ] Área táctil mínima 44×44px en todos los controles interactivos (SC 2.5.5)
- [ ] Contraste 4.5:1 en todos los textos del flujo (SC 1.4.3)

---

## 5. Decisiones pendientes de validación del equipo

Antes de iniciar la implementación del Bloque 14.5, el equipo debe aprobar:

1. **Paleta de colores del banner:** El diseño usa los colores del chat (`#0E1A38`, `#2E50B0`,
   `#1A3380`) como identidad Zampa. Si el equipo define tokens oficiales en `tailwind.config.js`
   antes de que empiece el 14.5, se deben usar esos tokens en lugar de los hex hardcodeados.

2. **Secciones opcionales:** Se propone que `dessert`, `coffee`, `drink` y `bread` tengan
   un enlace "Sin [sección], gracias →". Si el gerente quiere que una sección sea siempre
   obligatoria independientemente de su tipo, se necesita un campo `force_required` en el
   modelo `DailyMenuSection`.

3. **Auto-advance en sección con una sola opción:** Si la sección tiene exactamente 1 producto,
   el paso se selecciona automáticamente y avanza tras 0.5 seg. Confirmar si este comportamiento
   es aceptable o si el cliente debe confirmar siempre manualmente.

4. **Campo nota libre en confirmación:** El resumen incluye un textarea opcional para notas
   a cocina. Si se aprueba, se necesita un campo `note` en `daily_menu_orders` (verificar si
   ya existe en el modelo `Order` y puede reutilizarse).

5. **Focus trap:** Los componentes 3.2 y 3.5 implementan el focus trap mediante
   `@keydown.tab.window` sin dependencias adicionales. Si el equipo prefiere el plugin
   oficial, instalar con `npm install @alpinejs/focus` y registrarlo antes de
   `Alpine.start()` — `import Focus from '@alpinejs/focus'; Alpine.plugin(Focus);` — y
   sustituir los handlers por `x-trap="open"` (stepper) y
   `x-trap="showExclusivityWarning"` (dialog de exclusividad).
