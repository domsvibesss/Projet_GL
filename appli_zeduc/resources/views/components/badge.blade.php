@props(['couleur' => 'dore'])

@php
    $styles = [
        'rouge' => 'bg-rose-600 text-white',
        'dore'  => 'bg-primary text-black',
        'jaune' => 'bg-yellow-100 text-yellow-800',
        'vert'  => 'bg-green-600 text-white',
    ];
@endphp

<span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $styles[$couleur] }}">
    {{ $slot }}
</span>