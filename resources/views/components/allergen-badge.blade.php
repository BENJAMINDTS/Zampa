@props(['slug'])

@php
    $label = \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug;
@endphp

<span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
             bg-amber-50 dark:bg-amber-900/20
             border border-amber-200 dark:border-amber-800
             text-xs font-medium text-amber-900 dark:text-amber-200"
      role="listitem" title="{{ $label }}">
    <img src="{{ asset('images/allergens/' . $slug . '.svg') }}"
         alt=""
         aria-hidden="true"
         class="h-5 w-5 object-contain shrink-0">
    {{ $label }}
</span>
