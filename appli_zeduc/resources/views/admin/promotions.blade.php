@extends('layouts.admin')

@section('title', 'Promotions et événements')

@section('content')

{{-- Valeurs de secours : le backend les remplacera en envoyant $promotions --}}
@php
    $promotions = $promotions ?? [
        ['titre' => 'Vendredrink !!!',         'fond' => 'bg-green-300',  'reduction' => '-50%', 'image' => 'images/admin/promo-boissons.png',
         'texte' => "Profitez d'une réduction sur toutes les bières !!"],

        ['titre' => 'Soirée Champions League', 'fond' => 'bg-blue-300',   'reduction' => null,   'image' => 'images/admin/promo-champions.png',
         'texte' => "Tous vos matchs en DIRECT avec l'ambiance du Zeduc !"],

        ['titre' => 'Soirée Karaoké',          'fond' => 'bg-red-300',    'reduction' => '-25%', 'image' => 'images/admin/promo-karaoke.png',
         'texte' => "Profitez d'une réduction sur tous les plats à partir de 2 plats !"],

        ['titre' => 'Full Poulet',             'fond' => 'bg-purple-100', 'reduction' => '-20%', 'image' => 'images/admin/promo-poulet.png',
         'texte' => "Profitez d'une réduction de 20% sur tous les plats contenant du poulet !"],
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Promotions et événements</h1>

{{-- 1 colonne sur téléphone, 2 sur ordinateur --}}
<div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">

    @foreach ($promotions as $p)
        {{-- Une affiche : la couleur de fond vient des données ($p['fond']) --}}
        <article class="{{ $p['fond'] }} rounded-2xl p-6 text-black">

            <h2 class="text-3xl font-bold">{{ $p['titre'] }}</h2>

            <div class="mt-4 flex items-center gap-6">

                {{-- Gauche : l'image, et la réduction si elle existe --}}
                <div class="flex flex-1 flex-col items-center">
                    <img src="{{ asset($p['image']) }}" alt="{{ $p['titre'] }}" class="max-h-48 object-contain">

                    @if ($p['reduction'])
                        <p class="mt-2 font-serif text-6xl">{{ $p['reduction'] }}</p>
                    @endif
                </div>

                {{-- Droite : la description --}}
                <p class="flex-1 text-xl">{{ $p['texte'] }}</p>
            </div>
        </article>
    @endforeach

</div>

@endsection