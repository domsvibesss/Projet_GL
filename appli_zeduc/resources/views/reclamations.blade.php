@extends('layouts.app')

@section('title', 'Réclamations')

@section('content')

{{-- ============================================================
     DONNÉES DE SECOURS
     Le backend les remplacera en envoyant $reclamations, $priorites et $statuts.
     ============================================================ --}}
@php
    // Dictionnaire : la clé envoyée par le backend => le texte affiché + la couleur du badge
    $priorites = $priorites ?? [
        'urgente' => ['libelle' => 'Urgente', 'couleur' => 'rouge'],
        'normale' => ['libelle' => 'Normale', 'couleur' => 'dore'],
        'faible'  => ['libelle' => 'Faible',  'couleur' => 'jaune'],
    ];

    $statuts = $statuts ?? [
        'nouvelle' => ['libelle' => 'Nouvelle', 'couleur' => 'rouge'],
        'en_cours' => ['libelle' => 'En cours', 'couleur' => 'dore'],
        'resolue'  => ['libelle' => 'Résolue',  'couleur' => 'vert'],
    ];

    $reclamations = $reclamations ?? [
        ['id' => '#KK1234', 'client' => 'Jennifer Bela',    'sujet' => 'Commande arrivée froide', 'date' => "Aujourd'hui, 11:42", 'priorite' => 'urgente', 'statut' => 'nouvelle', 'message' => 'Il manquait la mayonnaise dans mon poulet pané.'],
        ['id' => '#KK1233', 'client' => 'Danielle Lili',    'sujet' => 'Article manquant',        'date' => "Aujourd'hui, 10:18", 'priorite' => 'normale', 'statut' => 'en_cours', 'message' => "Ma boisson n'était pas dans le sac."],
        ['id' => '#KK1232', 'client' => 'Yely Kanoute',     'sujet' => 'Erreur sur la formule',   'date' => 'Hier, 19:07',        'priorite' => 'faible',  'statut' => 'resolue',  'message' => "J'ai été facturé pour la mauvaise formule."],
        ['id' => '#KK1231', 'client' => 'Dominique Daphne', 'sujet' => 'Retard de livraison',     'date' => 'Hier, 12:31',        'priorite' => 'normale', 'statut' => 'nouvelle', 'message' => 'La livraison a eu 45 minutes de retard.'],
    ];

    // Le nombre affiché dans la carte « Réclamations Clients »
    $total = $total ?? count($reclamations);
@endphp

{{-- La page est coupée en 2 colonnes : la liste (3/5) et le détail (2/5) --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-5">


    {{-- ============================================================
         COLONNE GAUCHE : recherche + tableau des réclamations
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-6 text-black lg:col-span-3">

        <input id="recherche-reclamation" type="search" placeholder="Rechercher..."
               class="w-full rounded-xl bg-gray-200 px-6 py-4 text-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary">

        {{-- Le tableau (défile sur les côtés si l'écran est trop étroit) --}}
        <div class="mt-6 overflow-x-auto rounded-2xl border border-stone-300">
            <table class="w-full border-collapse text-left">

                <thead class="bg-primary-light text-sm uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-4 font-bold">Identifiant</th>
                        <th class="px-4 py-4 font-bold">Client</th>
                        <th class="px-4 py-4 font-bold">Sujet</th>
                        <th class="px-4 py-4 font-bold">Date</th>
                        <th class="px-4 py-4 font-bold">Priorité</th>
                        <th class="px-4 py-4 font-bold">Statut</th>
                    </tr>
                </thead>

                {{-- Le style des cellules est défini une seule fois ici --}}
                <tbody id="liste-reclamations"
                       class="[&_td]:px-4 [&_td]:py-4
                              [&_tr]:cursor-pointer [&_tr]:border-t [&_tr]:border-stone-200
                              [&_tr:hover]:bg-primary/10
                              [&_td:first-child]:font-bold">
                    @foreach ($reclamations as $r)
                        {{-- data-... : des informations invisibles que le JavaScript lira au clic --}}
                        <tr data-id="{{ $r['id'] }}"
                            data-client="{{ $r['client'] }}"
                            data-message="{{ $r['message'] }}"
                            data-url="{{ $r['url'] ?? '' }}">

                            <td>{{ $r['id'] }}</td>
                            <td>{{ $r['client'] }}</td>
                            <td>{{ $r['sujet'] }}</td>
                            <td class="text-sm text-gray-500">{{ $r['date'] }}</td>

                            {{-- On cherche, dans le dictionnaire, le texte et la couleur de cette priorité --}}
                            <td>
                                <x-badge :couleur="$priorites[$r['priorite']]['couleur']">
                                    {{ $priorites[$r['priorite']]['libelle'] }}
                                </x-badge>
                            </td>
                            <td>
                                <x-badge :couleur="$statuts[$r['statut']]['couleur']">
                                    {{ $statuts[$r['statut']]['libelle'] }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    {{-- ============================================================
         COLONNE DROITE : le compteur + le panneau de réponse
         ============================================================ --}}
    <div class="space-y-6 lg:col-span-2">

        {{-- Carte du compteur --}}
        <div class="rounded-2xl bg-white p-6 text-black">
            <h2 class="text-2xl font-medium">Réclamations Clients</h2>
            <p class="mt-2 text-8xl font-bold leading-none">{{ $total }}</p>
        </div>

        {{-- Panneau de réponse : rempli par le JavaScript quand on clique sur une ligne --}}
        <div class="rounded-2xl bg-gray-200 p-6 text-black">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                {{-- Gauche : la réclamation sélectionnée --}}
                <div>
                    <h3 id="detail-titre" class="text-2xl">Sélectionne une réclamation</h3>
                    <p id="detail-message" class="mt-4 rounded-3xl bg-primary/30 p-6 text-center text-lg"></p>
                </div>

                {{-- Droite : la réponse à écrire (vrai formulaire : le JavaScript met
                     dans action l'adresse de la réclamation sélectionnée) --}}
                <form id="form-reponse" method="POST">
                    @csrf
                    <label for="reponse" class="text-2xl">Votre réponse</label>
                    <textarea id="reponse" name="contenu" placeholder="Écrire la réponse au client..."
                              class="mt-4 h-40 w-full resize-none rounded-xl border border-primary bg-white p-4 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>

                    {{-- Message de confirmation (vert) ou d'erreur (rouge), après l'envoi ou par le JavaScript --}}
                    <p id="confirmation" class="mt-2 min-h-6 text-sm {{ $errors->any() ? 'text-red-600' : 'text-green-700' }}">{{ $errors->first() ?: session('success') }}</p>

                    <button id="btn-envoyer" type="submit"
                            class="mt-2 w-full rounded-full bg-primary px-6 py-4 text-xl text-black transition hover:opacity-80">
                        Envoyer la réponse
                    </button>
                </form>

            </div>
        </div>

    </div>

</div>

@endsection
