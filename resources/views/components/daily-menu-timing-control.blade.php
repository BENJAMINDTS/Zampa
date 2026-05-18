{{-- @author SebastianBCF --}}
{{-- Control de timing [−] X min [+] para el stepper del menú del día.
     Sin x-data propio: hereda el scope Alpine del banner (pasoActualObj, timings, horasEstimadas). --}}

<div class="py-2"
     role="group"
     :aria-labelledby="`timing-label-${pasoActualObj?.round}`">

    <p :id="`timing-label-${pasoActualObj?.round}`"
       class="text-white font-semibold mb-3 text-base"
       x-text="pasoActualObj?.label">
    </p>

    <div class="flex items-center justify-center gap-6 my-6">

        {{-- Botón menos --}}
        <button
            type="button"
            @click="decrementTiming(pasoActualObj.round, pasoActualObj.minDelay)"
            :disabled="timings[pasoActualObj?.round] <= pasoActualObj?.minDelay"
            :aria-disabled="timings[pasoActualObj?.round] <= pasoActualObj?.minDelay"
            :aria-label="`Reducir tiempo de la ronda ${pasoActualObj?.round}`"
            :class="{
                'w-12 h-12 rounded-full flex items-center justify-center border-2 border-blue-600/60 text-blue-300 transition-colors': true,
                'hover:bg-blue-800/50 hover:text-white': timings[pasoActualObj?.round] > pasoActualObj?.minDelay,
                'opacity-35 cursor-not-allowed': timings[pasoActualObj?.round] <= pasoActualObj?.minDelay
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
             :aria-valuemin="pasoActualObj?.minDelay"
             aria-valuemax="120"
             :aria-valuenow="timings[pasoActualObj?.round]"
             :aria-valuetext="timings[pasoActualObj?.round] + ' minutos'">
            <span class="text-5xl font-bold text-white"
                  x-text="timings[pasoActualObj?.round]">
            </span>
            <span class="text-blue-300 text-sm ml-1">min</span>
        </div>

        {{-- Botón más --}}
        <button
            type="button"
            @click="incrementTiming(pasoActualObj.round)"
            :disabled="timings[pasoActualObj?.round] >= 120"
            :aria-disabled="timings[pasoActualObj?.round] >= 120"
            :aria-label="`Aumentar tiempo de la ronda ${pasoActualObj?.round}`"
            :class="{
                'w-12 h-12 rounded-full flex items-center justify-center border-2 border-blue-600/60 text-blue-300 transition-colors': true,
                'hover:bg-blue-800/50 hover:text-white': timings[pasoActualObj?.round] < 120,
                'opacity-35 cursor-not-allowed': timings[pasoActualObj?.round] >= 120
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
        <span x-text="`Tu plato llegará aprox. a las ${horasEstimadas[pasoActualObj?.round] ?? '--:--'}`"></span>
    </div>

    <p class="text-center text-blue-400/70 text-xs mt-1"
       x-text="`Tiempo mínimo de preparación: ${pasoActualObj?.minDelay} min`">
    </p>

</div>
