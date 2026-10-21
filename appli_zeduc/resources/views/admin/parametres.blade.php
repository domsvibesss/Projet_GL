@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')

{{-- ============================================================
     VALEURS DE SECOURS : le backend les remplacera en envoyant
     $horaires, $tauxPoint, $validiteMois et $politiques
     ============================================================ --}}
@php
    // 'cle' sert de nom de champ pour le backend, 'jour' est le texte affiché
    $horaires = $horaires ?? [
        ['cle' => 'lundi',    'jour' => 'Lundi',    'ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true],
        ['cle' => 'mardi',    'jour' => 'Mardi',    'ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true],
        ['cle' => 'mercredi', 'jour' => 'Mercredi', 'ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true],
        ['cle' => 'jeudi',    'jour' => 'Jeudi',    'ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true],
        ['cle' => 'vendredi', 'jour' => 'Vendredi', 'ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true],
        ['cle' => 'samedi',   'jour' => 'Samedi',   'ouverture' => '10:00', 'fermeture' => '23:00', 'ouvert' => true],
        ['cle' => 'dimanche', 'jour' => 'Dimanche', 'ouverture' => '',      'fermeture' => '23:00', 'ouvert' => true],
    ];

    $tauxPoint    = $tauxPoint ?? 1000;   // 1 point = 1000 FCFA
    $validiteMois = $validiteMois ?? 12;  // les points expirent après 12 mois

    $politiques = $politiques ?? [
        'fidelite'     => "Les points sont crédités après chaque achat. 15 points te donnent droit à une remise de 1000 FCFA.",
        'parrainage'   => "Le parrain et son filleul reçoivent 3 points après le premier achat de son filleul.",
        'remboursement'=> "Toute demande est étudiée par notre équipe sous 48 heures. Un avoir peut être proposé selon la situation.",
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Paramètres</h1>

<form method="POST" action="#" class="mt-8">

    {{-- Jeton de sécurité obligatoire dans Laravel --}}
    @csrf

    {{-- Deux colonnes : les horaires (gauche) et le reste (droite) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">


        {{-- ============================================================
             COLONNE GAUCHE : les heures d'ouverture
             ============================================================ --}}
        <div class="rounded-2xl bg-white p-8 text-black">

            <h2 class="text-4xl font-bold">Heures d'ouverture</h2>
            <p class="mt-1 text-xl">Un horaire lisible, c'est déjà un bon début de service.</p>

            <div class="mt-8 space-y-4">
                @foreach ($horaires as $h)
                    {{-- Une ligne = un jour --}}
                    <div class="grid grid-cols-[6rem_1fr_1fr_6.5rem] items-center gap-3 sm:grid-cols-[8rem_1fr_1fr_7rem]">

                        <span class="text-xl font-bold">{{ $h['jour'] }}</span>

                        <input type="time" name="horaires[{{ $h['cle'] }}][ouverture]" value="{{ $h['ouverture'] }}"
                               class="w-full rounded-lg border border-black px-3 py-3">

                        <input type="time" name="horaires[{{ $h['cle'] }}][fermeture]" value="{{ $h['fermeture'] }}"
                               class="w-full rounded-lg border border-black px-3 py-3">

                        {{-- Bouton d'état (le JavaScript le fera basculer à l'étape suivante) --}}
                        @if ($h['ouvert'])
                            <button type="button" data-jour="{{ $h['cle'] }}" data-ouvert="1"
                                    class="rounded-full border border-green-900 bg-green-200 py-2 text-sm font-semibold uppercase">
                                Ouvert
                            </button>
                        @else
                            <button type="button" data-jour="{{ $h['cle'] }}" data-ouvert="0"
                                    class="rounded-full border border-red-900 bg-red-200 py-2 text-sm font-semibold uppercase">
                                Fermé
                            </button>
                        @endif

                        {{-- Champ caché : c'est lui que le backend lira (1 = ouvert, 0 = fermé) --}}
                        <input type="hidden" name="horaires[{{ $h['cle'] }}][ouvert]" value="{{ $h['ouvert'] ? 1 : 0 }}">
                    </div>
                @endforeach
            </div>
        </div>


        {{-- ============================================================
             COLONNE DROITE
             ============================================================ --}}
        <div class="space-y-6">

            {{-- Deux petites cartes côte à côte --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                {{-- Taux de conversion des points --}}
                <div class="rounded-2xl bg-white p-6 text-black">
                    <h2 class="text-center font-serif text-xl">Taux de conversion des points</h2>
                    <p class="mt-1 text-center text-sm">La valeur d'un point, en toute transparence</p>

                    <div class="mt-6 flex items-end gap-4">
                        <div class="flex-1">
                            <label for="taux_point" class="block text-xs uppercase">1 point =</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input id="taux_point" name="taux_point" type="number" min="1" value="{{ $tauxPoint }}"
                                       class="w-full rounded-lg border border-black px-3 py-2">
                                <span class="font-semibold">FCFA</span>
                            </div>
                        </div>

                        {{-- Aperçu : 25 points convertis (le JavaScript le mettra à jour à l'étape suivante) --}}
                        <div class="rounded-lg border border-black bg-primary/25 p-3 text-xs">
                            <p class="font-bold">Aperçu</p>
                            <p id="apercu-points" class="mt-1">{{ number_format(25 * $tauxPoint, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>

                {{-- Durée de validité des points --}}
                <div class="rounded-2xl bg-white p-6 text-black">
                    <h2 class="text-center font-serif text-xl">Durée de validité des points</h2>
                    <p class="mt-1 text-center text-sm">Un petit rappel avant que les points ne s'envolent.</p>

                    <div class="mt-6 flex items-center gap-3">
                        <input name="validite_mois" type="number" min="1" value="{{ $validiteMois }}"
                               class="w-24 rounded-lg border border-black px-3 py-2">
                        <span class="text-sm">mois après l'obtention</span>
                    </div>
                </div>
            </div>

            {{-- En savoir plus : les 3 politiques --}}
            <div class="rounded-2xl bg-white p-8 text-black">
                <h2 class="text-3xl font-bold">En savoir plus</h2>
                <p class="mt-1 text-sm">Écrivez-les comme vous les expliqueriez au comptoir.</p>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="politique_fidelite" class="block text-xs font-semibold uppercase">Politique de fidélité</label>
                        <textarea id="politique_fidelite" name="politique_fidelite" rows="2"
                                  class="mt-1 w-full resize-none rounded-lg border border-black px-4 py-3">{{ $politiques['fidelite'] }}</textarea>
                    </div>

                    <div>
                        <label for="politique_parrainage" class="block text-xs font-semibold uppercase">Politique de parrainage</label>
                        <textarea id="politique_parrainage" name="politique_parrainage" rows="2"
                                  class="mt-1 w-full resize-none rounded-lg border border-black px-4 py-3">{{ $politiques['parrainage'] }}</textarea>
                    </div>

                    <div>
                        <label for="politique_remboursement" class="block text-xs font-semibold uppercase">Politique de remboursement</label>
                        <textarea id="politique_remboursement" name="politique_remboursement" rows="2"
                                  class="mt-1 w-full resize-none rounded-lg border border-black px-4 py-3">{{ $politiques['remboursement'] }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Bouton d'enregistrement (absent de la maquette, mais indispensable) --}}
    <div class="mt-8 flex justify-center">
        <button type="submit"
                class="w-full max-w-md rounded-full bg-primary px-6 py-4 text-xl font-semibold text-white transition hover:opacity-80">
            Enregistrer les paramètres
        </button>
    </div>

</form>

@endsection