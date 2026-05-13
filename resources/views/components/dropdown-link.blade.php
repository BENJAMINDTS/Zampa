@props(['active' => false])

@php
$classes = $active
    ? 'block w-full px-4 py-2 text-start text-sm leading-5 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 focus:outline-none focus:bg-indigo-100 dark:focus:bg-indigo-900/60 transition duration-150 ease-in-out'
    : 'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
