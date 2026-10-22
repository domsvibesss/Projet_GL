@extends('layouts.admin')

@section('title', 'Gestion des employes')

@section('content')

{{-- Valeurs de secours : le backend les remplacera en envoyant $employes --}}
@php
    $employes = $employes ?? [
        ['id' => 1, 'nom' => 'Mballa',  'prenom' => 'Jean',  'poste' => 'Cuisinier',           'contact' => '+237 6 90 11 22 33', 'photo' => 'images/admin/cuisinier.png'],
        ['id' => 2, 'nom' => 'Ngo',     'prenom' => 'Marie', 'poste' => 'Assistant Cuisinier', 'contact' => '+237 6 77 11 22 33', 'photo' => 'images/admin/assistant-cuisinier.png'],
        ['id' => 3, 'nom' => 'Tchamba', 'prenom' => 'Paul',  'poste' => 'Livreur',             'contact' => '+237 6 55 11 22 33', 'photo' => 'images/admin/livreur.png'],
        ['id' => 4, 'nom' => 'Fotso',   'prenom' => 'Aline', 'poste' => 'Manager',             'contact' => '+237 6 99 11 22 33', 'photo' => 'images/admin/manager.png'],
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Gestion des employes</h1>

{{-- ===== Barre d'outils : recherche + bascule Cartes/Tableau + bouton Ajouter à droite ===== --}}
<div class="mt-8 flex flex-wrap items-center gap-4">

    <input id="recherche-employes" type="search" placeholder="Rechercher un employé..."
           class="min-w-[12rem] flex-1 rounded-full border border-primary bg-transparent px-5 py-3 text-white">

    {{-- Bascule Cartes / Tableau --}}
    <div class="flex overflow-hidden rounded-full border border-primary">
        <button type="button" data-vue="cartes"
                class="bg-primary px-5 py-3 font-semibold text-black">Cartes</button>
        <button type="button" data-vue="tableau"
                class="px-5 py-3 font-semibold text-primary">Tableau</button>
    </div>

    <a href="/admin/employes/ajouter"
       class="rounded-full bg-primary px-6 py-3 font-semibold text-white transition hover:opacity-80">
        Ajouter un employe
    </a>
</div>

{{-- ===== Vue CARTES (la maquette) ===== --}}
<div id="vue-cartes" class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($employes as $e)
        {{-- Ce conteneur sert à la recherche et à la suppression dans les deux vues --}}
        <div data-employe-id="{{ $e['id'] }}"
             data-recherche="{{ $e['prenom'] }} {{ $e['nom'] }} {{ $e['poste'] }} {{ $e['contact'] }}">
            <x-manage-card :id="$e['id']"
                           :titre="$e['prenom'].' '.$e['nom'].' - '.$e['poste']"
                           :image="$e['photo']"
                           :modifier="'/admin/employes/'.$e['id'].'/modifier'" />
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
                <th class="p-3">Prénom</th>
                <th class="p-3">Rôle</th>
                <th class="p-3">Contact</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="liste-employes">
            @foreach ($employes as $e)
                <tr data-carte data-employe-id="{{ $e['id'] }}"
                    data-nom="{{ $e['prenom'] }} {{ $e['nom'] }}"
                    data-recherche="{{ $e['prenom'] }} {{ $e['nom'] }} {{ $e['poste'] }} {{ $e['contact'] }}"
                    class="border-b border-gray-200">
                    <td class="p-3">
                        <img src="{{ asset($e['photo']) }}" alt="{{ $e['prenom'] }}"
                             class="h-12 w-12 rounded-full object-cover">
                    </td>
                    <td class="p-3">{{ $e['nom'] }}</td>
                    <td class="p-3">{{ $e['prenom'] }}</td>
                    <td class="p-3">{{ $e['poste'] }}</td>
                    <td class="p-3">{{ $e['contact'] }}</td>
                    <td class="p-3">
                        <div class="flex justify-end gap-2">
                            <a href="/admin/employes/{{ $e['id'] }}/modifier"
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
<p id="aucun-employe" class="mt-10 hidden text-center text-xl">Aucun employé trouvé.</p>

@endsection