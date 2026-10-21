@extends('layouts.app')

@section('title', 'Supervision des commandes')

{{-- Cette page n'a pas d'espace autour : l'image de fond couvre tout --}}
@section('main-class', 'p-0')

@section('content')

@php
    $fond = asset('images/fond-commandes.png');

    // ---------- Valeurs de secours : le backend les remplacera ----------

    // Les 4 compteurs
    $compteurs = $compteurs ?? [
        'en_attente'     => 4,
        'en_preparation' => 3,
        'prete'          => 8,
        'livree'         => 12,
    ];

    // Texte affiché sur les cartes
    $cartes = [
        'en_attente'     => 'En attente',
        'en_preparation' => 'En préparation',
        'prete'          => 'Prêtes',
        'livree'         => 'Livrées',
    ];

    // Texte affiché dans le tableau pour chaque statut
    $statuts = [
        'en_attente'     => 'En attente',
        'en_preparation' => 'En préparation',
        'prete'          => 'Prête',
        'livree'         => 'Livrée',
    ];

    // Les commandes
    $commandes = $commandes ?? [
        ['numero' => 101, 'client' => 'Marie K.', 'articles' => '2 × Poulet DG',  'heure' => '12h30', 'statut' => 'en_attente'],
        ['numero' => 102, 'client' => 'Paul T.',  'articles' => '1 × Ndolé',      'heure' => '12h35', 'statut' => 'en_preparation'],
        ['numero' => 103, 'client' => 'Awa N.',   'articles' => '1 × Riz sauté',  'heure' => '12h40', 'statut' => 'prete'],
        ['numero' => 104, 'client' => 'Luc B.',   'articles' => '3 × Brochettes', 'heure' => '12h45', 'statut' => 'livree'],
    ];
@endphp

<section class="relative min-h-screen bg-cover bg-center"
         style="background-image: url('{{ $fond }}')">

    {{-- Voile noir pour assombrir la photo --}}
    <div class="absolute inset-0 bg-black/60"></div>

    {{-- Tout le contenu est ICI, au-dessus du voile --}}
    <div class="relative z-10 px-6 py-10">

        {{-- ===== Les 4 cartes (cliquables) ===== --}}
        <div class="flex flex-wrap justify-between gap-6">
            @foreach ($cartes as $cle => $libelle)
                <x-status-card :id="'compteur-' . $cle"
                               :filtre="$cle"
                               :nombre="$compteurs[$cle]"
                               :libelle="$libelle" />
            @endforeach
        </div>

        {{-- ===== Bouton pour retirer le filtre ===== --}}
        <div class="mt-16 flex justify-end">
            <button type="button" id="btn-tout"
                    class="rounded-full bg-primary px-6 py-2 text-black transition hover:opacity-80">
                Tout afficher
            </button>
        </div>

        {{-- ===== Le tableau des commandes ===== --}}
        <div class="mt-4 overflow-x-auto">
            <table id="table-commandes" class="w-full border-collapse text-left text-black">

                <thead>
                    <tr class="bg-stone-200 text-xl font-bold">
                        <th class="border border-black px-6 py-4">N°</th>
                        <th class="border border-black px-6 py-4">Client</th>
                        <th class="border border-black px-6 py-4">Articles</th>
                        <th class="border border-black px-6 py-4">Heure</th>
                        <th class="border border-black px-6 py-4">Statut</th>
                    </tr>
                </thead>

                <tbody id="corps-commandes">
                    @forelse ($commandes as $c)
                        <tr data-statut="{{ $c['statut'] }}" class="bg-white">
                            <td class="border border-black bg-stone-100 px-6 py-5 text-2xl font-bold">#{{ $c['numero'] }}</td>
                            <td class="border border-black px-6 py-5">{{ $c['client'] }}</td>
                            <td class="border border-black px-6 py-5">{{ $c['articles'] }}</td>
                            <td class="border border-black px-6 py-5">{{ $c['heure'] }}</td>
                            <td class="border border-black px-6 py-5">{{ $statuts[$c['statut']] }}</td>
                        </tr>
                    @empty
                        <tr class="bg-white">
                            <td colspan="5" class="border border-black px-6 py-5 text-center">Aucune commande pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
</section>

@endsection

