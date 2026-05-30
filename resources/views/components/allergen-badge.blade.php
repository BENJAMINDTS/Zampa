@props(['slug'])

@php
    $label = \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug;
@endphp

<div class="flex flex-col items-center gap-1 w-16" title="{{ $label }}">
    <div class="w-12 h-12 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center shadow-sm p-2">
        <img src="{{ asset('images/allergens/' . $slug . '.svg') }}"
             alt="{{ $label }}"
             class="w-full h-full object-contain">
    </div>
    <span class="text-xs text-center font-medium text-gray-600 dark:text-gray-400 leading-tight">
        {{ $label }}
    </span>
</div>
