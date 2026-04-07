@props(['ingredient'])

@if($ingredient->allergen_type)
    <span class="inline-flex items-center gap-1
                 bg-orange-50 dark:bg-orange-900/30
                 border border-orange-200 dark:border-orange-700
                 rounded-full px-2 py-0.5 text-xs
                 text-orange-800 dark:text-orange-200"
          title="{{ $ingredient->allergenTypeName() }}">
        <img src="{{ $ingredient->allergenIconPath() }}"
             alt="{{ $ingredient->allergenTypeName() }}"
             class="h-4 w-4 rounded-full"
             loading="lazy">
        <span>{{ $ingredient->allergenTypeName() }}</span>
    </span>
@elseif($ingredient->is_allergen)
    <span class="inline-flex items-center gap-1
                 bg-yellow-50 dark:bg-yellow-900/30
                 border border-yellow-200 dark:border-yellow-700
                 rounded-full px-2 py-0.5 text-xs
                 text-yellow-800 dark:text-yellow-200"
          title="Alérgeno (tipo no especificado)">
        <span aria-hidden="true">⚠️</span>
        <span>{{ $ingredient->name }}</span>
    </span>
@endif
