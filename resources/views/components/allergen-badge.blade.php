@props(['ingredient'])

@if($ingredient->is_allergen)
    @php
        $shortNames = [
            'gluten'          => 'Gluten',
            'crustaceos'      => 'Crustáceos',
            'huevos'          => 'Huevos',
            'pescado'         => 'Pescado',
            'cacahuetes'      => 'Cacahuetes',
            'soja'            => 'Soja',
            'lacteos'         => 'Lácteos',
            'frutos-cascara'  => 'Frutos secos',
            'apio'            => 'Apio',
            'mostaza'         => 'Mostaza',
            'sesamo'          => 'Sésamo',
            'sulfitos'        => 'Sulfitos',
            'altramuces'      => 'Altramuz',
            'moluscos'        => 'Moluscos',
        ];

        $label = $shortNames[$ingredient->allergen_type] ?? $ingredient->name;
    @endphp

    <div class="flex flex-col items-center gap-1 w-16"
         title="{{ $label }}">
        <div class="w-12 h-12 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center shadow-sm p-2">
            @if($ingredient->allergen_type)
                <img src="{{ $ingredient->allergenIconPath() }}"
                     alt="{{ $label }}"
                     class="w-full h-full object-contain">
            @else
                <span class="text-2xl leading-none">{{ $ingredient->ingredientEmoji() }}</span>
            @endif
        </div>
        <span class="text-xs text-center font-medium text-gray-600 dark:text-gray-400 leading-tight">
            {{ $label }}
        </span>
    </div>
@endif
