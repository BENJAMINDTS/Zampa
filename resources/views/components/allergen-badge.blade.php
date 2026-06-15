@props(['slug'])

@php
    $label = \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug;
@endphp

<span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
             bg-white dark:bg-gray-800
             border border-gray-200 dark:border-gray-700
             text-xs font-medium text-gray-700 dark:text-gray-300 shrink-0"
      role="listitem">
    <img src="{{ asset('images/allergens/' . $slug . '.svg') }}"
         width="24" height="24"
         class="shrink-0"
         alt="{{ $label }}">
    {{ $label }}
</span>
