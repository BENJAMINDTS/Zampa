@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 dark:text-gray-300']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
        <span class="sr-only">(obligatorio)</span>
    @endif
</label>
