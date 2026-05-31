{{-- @author SebastianBCF --}}
{{-- @author Ayrtonalania --}}
{{-- Control de tiempos entre rondas — clases DS (dm-timing + dm-timing__slider).
     Sin x-data propio: hereda el scope Alpine de dailyMenuBanner. --}}

<div class="dm-step"
     role="group"
     :aria-labelledby="'dm-timing-title'">

    <div class="dm-step__head">
        <h3 class="dm-step__title" id="dm-timing-title"
            x-text="pasoActualObj?.label ?? 'Tiempos entre platos'"></h3>
        <p class="dm-step__hint">
            Marca cuánto quieres esperar entre rondas. Lo verá el equipo de cocina.
        </p>
    </div>

    <div class="dm-timing">
        <template x-for="rule in timingRules" :key="rule.round">
            <div class="dm-timing__row">
                <div class="dm-timing__top">
                    <span class="dm-timing__label"
                          x-text="'Ronda ' + rule.round"></span>
                    <span class="dm-timing__val"
                          x-text="(timings[rule.round] ?? rule.minDelay) + ' min'"></span>
                </div>
                <input
                    type="range"
                    :min="rule.minDelay"
                    max="120"
                    step="5"
                    :value="timings[rule.round] ?? rule.minDelay"
                    @input="setTiming(rule.round, Number($event.target.value))"
                    class="dm-timing__slider"
                    :aria-label="'Tiempo para ronda ' + rule.round"
                    :aria-valuemin="rule.minDelay"
                    aria-valuemax="120"
                    :aria-valuenow="timings[rule.round] ?? rule.minDelay"
                >
                <div class="dm-timing__ticks">
                    <span x-text="rule.minDelay + ' min'"></span>
                    <span>—</span>
                    <span>120 min</span>
                </div>
            </div>
        </template>
    </div>

</div>
