@extends('layouts.admin')

@section('title', 'Gestion des menus')

@section('content')

{{-- Valeurs de secours : le backend les remplacera en envoyant $menus --}}
@php
    $menus = $menus ?? [
        ['id' => 1, 'nom' => 'Poisson braisé',          'prix' => 3000, 'description' => 'Poisson braisé servi avec du bâton de manioc et des piments.', 'photo' => 'images/admin/poisson-braise.png'],
        ['id' => 2, 'nom' => 'Okok et baton de manioc', 'prix' => 1000, 'description' => 'Feuilles d\'okok mijotées, accompagnées de bâtons de manioc.', 'photo' => 'images/admin/okok.png'],
        ['id' => 3, 'nom' => 'Porc braisé',             'prix' => 3000, 'description' => 'Porc braisé avec plantains mûrs et sauce pimentée.',          'photo' => 'images/admin/porc-braise.png'],
        ['id' => 4, 'nom' => 'Spaghettis sautés',       'prix' => 1000, 'description' => 'Spaghettis sautés aux légumes et aux oeufs.',                 'photo' => 'images/admin/spaghettis.png'],
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Gestion des menus</h1>

{{-- ===== Barre d'outils : recherche + bascule Cartes/Tableau + bouton Ajouter à droite ===== --}}
<div class="mt-8 flex flex-wrap items-center gap-4">

    <input id="champ-recherche" type="search" placeholder="Rechercher un menu..."
           class="min-w-[12rem] flex-1 rounded-full border border-primary bg-transparent px-5 py-3 text-white">

    {{-- Bascule Cartes / Tableau --}}
    <div class="flex overflow-hidden rounded-full border border-primary">
        <button type="button" data-vue="cartes"
                class="bg-primary px-5 py-3 font-semibold text-black">Cartes</button>
        <button type="button" data-vue="tableau"
                class="px-5 py-3 font-semibold text-primary">Tableau</button>
    </div>

    <a href="/admin/menus/ajouter"
       class="rounded-full bg-primary px-6 py-3 font-semibold text-white transition hover:opacity-80">
        Ajouter un menu
    </a>
</div>

{{-- ===== Vue CARTES (la maquette) ===== --}}
<div id="vue-cartes" class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($menus as $m)
        {{-- Ce conteneur sert à la recherche et à la suppression dans les deux vues --}}
        <div data-item-id="{{ $m['id'] }}"
             data-recherche="{{ $m['nom'] }} {{ $m['prix'] }} {{ $m['description'] }}">
            <x-manage-card :id="$m['id']"
                           :titre="$m['nom']"
                           :image="$m['photo']"
                           :detail="number_format($m['prix'], 0, ',', ' ') . ' F'"
                           :modifier="'/admin/menus/'.$m['id'].'/modifier'" />
        </div>
    @endforeach
</div>

{{-- ===== Vue TABLEAU ===== --}}
<div id="vue-tableau" class="mt-10 hidden overflow-x-auto rounded-2xl bg-white p-4 text-black">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-black text-sm uppercase">
                <th class="p-3">Photo</th>
                <th class="p-3">Nom</th>
                <th class="p-3">Prix</th>
                <th class="p-3">Description</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="liste-menus">
            @foreach ($menus as $m)
                <tr data-carte data-item-id="{{ $m['id'] }}"
                    data-nom="{{ $m['nom'] }}"
                    data-recherche="{{ $m['nom'] }} {{ $m['prix'] }} {{ $m['description'] }}"
                    class="border-b border-gray-200">
                    <td class="p-3">
                        <img src="{{ asset($m['photo']) }}" alt="{{ $m['nom'] }}"
                             class="h-12 w-12 rounded-full object-cover">
                    </td>
                    <td class="p-3">{{ $m['nom'] }}</td>
                    <td class="p-3">{{ number_format($m['prix'], 0, ',', ' ') }} F</td>
                    <td class="p-3">{{ $m['description'] }}</td>
                    <td class="p-3">
                        <div class="flex justify-end gap-2">
                            <a href="/admin/menus/{{ $m['id'] }}/modifier"
                               class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:opacity-80">Modifier</a>
                            <button type="button" data-supprimer
                                    class="rounded-full bg-black px-4 py-2 text-sm font-semibold text-white transition hover:opacity-80">Supprimer</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Message affiché quand la recherche ne trouve rien --}}
<p id="message-vide" class="mt-10 hidden text-center text-xl">Aucun menu trouvé.</p>

@endsection