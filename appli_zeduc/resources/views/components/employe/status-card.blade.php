@props(['nombre', 'libelle', 'id' => null, 'filtre' => null])

<button type="button" data-filtre="{{ $filtre }}"
        class="w-60 cursor-pointer rounded-3xl bg-primary/30 px-6 py-4 text-center text-white ring-primary backdrop-blur-sm transition hover:bg-primary/50">
    <p id="{{ $id }}" class="font-serif text-5xl">{{ $nombre }}</p>
    <p class="font-serif text-xl">{{ $libelle }}</p>
</button>
