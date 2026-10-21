{{--
    Une carte avec une photo, un titre, et les boutons Modifier / Supprimer.
    Reçoit :  id, titre, image, modifier (facultatif)
--}}
@props(['id', 'titre', 'image', 'modifier' => '#', 'detail' => null])

<div data-carte="{{ $id }}">

    {{-- 1) La carte blanche : titre + photo. Elle se ferme AVANT les boutons. --}}
    <div class="flex h-104 flex-col rounded-2xl bg-white p-4 text-center text-black">
        <h2 class="text-xl font-semibold">{{ $titre }}</h2>
        {{-- Une ligne de détail (par exemple le prix), seulement si on en donne une --}}
        @if ($detail)
            <p class="mt-1 text-base text-gray-600">{{ $detail }}</p>
        @endif

        <div class="flex flex-1 items-center justify-center">
            <img src="{{ asset($image) }}" alt="{{ $titre }}" class="max-h-72 object-contain">
        </div>
    </div>

    {{-- 2) Les boutons : EN DEHORS de la carte blanche --}}
    <a href="{{ $modifier }}"
       class="mt-4 block rounded-xl bg-primary py-3 text-center text-xl font-semibold text-white transition hover:opacity-80">
        Modifier
    </a>

    <button type="button" data-supprimer
            class="mt-3 block w-full rounded-xl bg-primary py-3 text-xl font-semibold text-white transition hover:opacity-80">
        Supprimer
    </button>
</div>