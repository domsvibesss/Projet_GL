@extends('layouts.admin')

@section('title', 'Réclamations')

@section('content')

{{-- Donnees de secours : le backend enverra la variable reclamations --}}
@php
    $reclamations = $reclamations ?? [
        ['id' => 'R-001', 'client' => 'Aïcha Ngono',  'objet' => 'Plat reçu froid',               'date' => '20/10/2026', 'type' => 'client', 'priorite' => 'urgente', 'statut' => 'nouvelle',
         'message' => "Mon plat est arrivé froid et la livraison a eu 40 minutes de retard.", 'reponse' => null, 'validation' => null],
        ['id' => 'R-002', 'client' => 'Paul Essomba', 'objet' => 'Points non crédités',           'date' => '19/10/2026', 'type' => 'points', 'priorite' => 'normale', 'statut' => 'en_cours',
         'message' => "Je n'ai pas reçu mes points après ma commande de samedi.", 'reponse' => "Nous vérifions votre commande et créditerons vos points sous 48 h.", 'validation' => 'attente'],
        ['id' => 'R-003', 'client' => 'Marie Tchoua', 'objet' => 'Commande incomplète',           'date' => '18/10/2026', 'type' => 'client', 'priorite' => 'normale', 'statut' => 'resolue',
         'message' => "Il manquait une boisson dans ma commande.", 'reponse' => "Une boisson vous sera offerte à votre prochaine commande.", 'validation' => 'validee'],
        ['id' => 'R-004', 'client' => 'Brice Ateba',  'objet' => 'Parrainage non pris en compte', 'date' => '17/10/2026', 'type' => 'points', 'priorite' => 'faible',  'statut' => 'en_cours',
         'message' => "Mon filleul a commandé mais je n'ai rien reçu.", 'reponse' => "Le parrainage n'est valable qu'à la première commande.", 'validation' => 'rejetee'],
    ];

    // Compteurs : calcules ici, le backend pourra aussi les envoyer
    $nbClients = $nbClients ?? collect($reclamations)->where('type', 'client')->count();
    $nbPoints  = $nbPoints  ?? collect($reclamations)->where('type', 'points')->count();

    // Pour chaque valeur : texte affiche, couleurs de la pastille, couleur du point
    $prioriteStyle = [
        'urgente' => ['Urgente', 'bg-red-100 text-red-900',       'bg-red-600'],
        'normale' => ['Normale', 'bg-yellow-100 text-yellow-900', 'bg-yellow-500'],
        'faible'  => ['Faible',  'bg-green-100 text-green-900',   'bg-green-600'],
    ];
    $statutStyle = [
        'nouvelle' => ['Nouvelle', 'bg-primary text-black',         'bg-black'],
        'en_cours' => ['En cours', 'bg-yellow-100 text-yellow-900', 'bg-yellow-500'],
        'resolue'  => ['Résolue',  'bg-green-100 text-green-900',   'bg-green-600'],
    ];

    // Fabrique une pastille (ecrite une seule fois, utilisee deux fois par ligne)
    $pastille = fn ($s) => '<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold '.$s[1].'">'
                         . '<span class="h-2 w-2 rounded-full '.$s[2].'"></span>'.$s[0].'</span>';
@endphp


{{-- Titre --}}
<h1 class="text-center font-serif text-5xl">Suivi des réclamations</h1>
<p class="mt-2 text-center text-white/70">Consultez et gérez les réclamations soumises par les étudiants</p>


{{-- Deux cartes compteurs --}}
<div class="mx-auto mt-10 grid max-w-4xl grid-cols-1 gap-6 sm:grid-cols-2">
    <div class="flex items-center justify-between rounded-2xl border-l-8 border-primary bg-white p-6 text-black shadow-lg">
        <p class="text-xl font-bold">Réclamations Clients</p>
        <p class="text-5xl font-bold">{{ $nbClients }}</p>
    </div>
    <div class="flex items-center justify-between rounded-2xl border-l-8 border-primary bg-white p-6 text-black shadow-lg">
        <p class="text-xl font-bold">Réclamations de Points</p>
        <p class="text-5xl font-bold">{{ $nbPoints }}</p>
    </div>
</div>


{{-- Recherche et filtres par statut (la cle vide veut dire : toutes) --}}
<div class="mx-auto mt-10 flex max-w-6xl flex-col gap-4 md:flex-row md:items-center">

    <input id="recherche-reclamations" type="search" placeholder="Rechercher un client ou un objet…"
           class="w-full rounded-full bg-white px-6 py-3 text-black md:flex-1">

    <div class="flex flex-wrap gap-2">
        @foreach (['' => 'Toutes', 'nouvelle' => 'Nouvelles', 'en_cours' => 'En cours', 'resolue' => 'Résolues'] as $cle => $texte)
            <button type="button" data-statut-filtre="{{ $cle }}"
                    class="rounded-full border border-primary px-5 py-2 text-sm font-semibold transition
                           {{ $cle === '' ? 'bg-primary text-black' : 'text-primary hover:bg-primary/20' }}">
                {{ $texte }}
            </button>
        @endforeach
    </div>
</div>


{{-- Tableau --}}
<div class="mx-auto mt-6 max-w-6xl overflow-x-auto rounded-2xl bg-white text-black shadow-lg">

    {{-- Les classes avec crochets stylent toutes les cellules, une seule fois --}}
    <table class="w-full text-left text-black [&_td]:px-6 [&_td]:py-4 [&_th]:px-6 [&_th]:py-4">

        <thead class="bg-primary text-sm uppercase tracking-wide">
            <tr>
                <th>N°</th><th>Client</th><th>Objet</th><th>Type</th>
                <th>Date</th><th>Priorité</th><th>Statut</th><th>Action</th>
            </tr>
        </thead>

        <tbody id="liste-reclamations" class="divide-y divide-gray-200 [&_tr:hover]:bg-primary-light">
            @forelse ($reclamations as $r)

                {{-- data-statut sert au filtre, data-reclamation sert a la fenetre --}}
                <tr data-statut="{{ $r['statut'] }}">
                    <td class="font-bold">{{ $r['id'] }}</td>
                    <td>{{ $r['client'] }}</td>
                    <td>{{ $r['objet'] }}</td>
                    <td>{{ $r['type'] === 'points' ? 'Points' : 'Client' }}</td>
                    <td class="whitespace-nowrap">{{ $r['date'] }}</td>
                    <td>{!! $pastille($prioriteStyle[$r['priorite']]) !!}</td>
                    <td data-cellule-statut>{!! $pastille($statutStyle[$r['statut']]) !!}</td>
                    <td>
                        <button type="button" data-reclamation="{{ json_encode($r) }}"
                                class="rounded-full bg-black px-4 py-2 text-sm font-semibold text-white transition hover:opacity-80">
                            Traiter
                        </button>
                    </td>
                </tr>

            @empty
                <tr><td colspan="8" class="text-center">Aucune réclamation.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>


{{-- Fenetre Traiter : balise dialog native, cachee jusqu'a showModal() --}}
<dialog id="detail-reclamation" class="m-auto w-full max-w-xl rounded-2xl bg-white p-0 text-black backdrop:bg-black/70">
    <div class="space-y-5 p-8">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="d-titre" class="text-2xl font-bold"></h2>
                <p id="d-meta" class="text-sm text-gray-600"></p>
            </div>
            <button type="button" id="d-fermer" class="text-3xl leading-none" aria-label="Fermer">&times;</button>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-gray-500">Message de l'étudiant</p>
            <p id="d-message" class="mt-1 rounded-lg bg-primary-light p-4"></p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-gray-500">Réponse proposée par l'employé</p>
            <p id="d-reponse" class="mt-1 rounded-lg bg-primary-light p-4"></p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-gray-500">Décision du gérant</p>
            <p id="d-validation" class="mt-1 font-semibold"></p>
        </div>

        <div>
            <label for="d-reponse-admin" class="text-xs font-semibold uppercase text-gray-500">Votre réponse (administrateur)</label>
            <textarea id="d-reponse-admin" rows="3" placeholder="Écrivez votre réponse à l'étudiant…"
                      class="mt-1 w-full resize-none rounded-lg border border-black px-4 py-3"></textarea>
        </div>

        <div>
            <label for="d-statut" class="text-xs font-semibold uppercase text-gray-500">Statut de la réclamation</label>
            <select id="d-statut" class="mt-1 w-full rounded-lg border border-black px-4 py-3">
                <option value="nouvelle">Nouvelle</option>
                <option value="en_cours">En cours</option>
                <option value="resolue">Résolue</option>
            </select>
        </div>

        <button type="button" id="d-enregistrer"
                class="w-full rounded-full bg-primary py-3 text-lg font-semibold text-white transition hover:opacity-80">
            Enregistrer
        </button>
    </div>
</dialog>

@endsection