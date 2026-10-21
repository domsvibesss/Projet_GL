@extends('layouts.app')

@section('title', 'Compte employés')

@section('content')

{{-- ============================================================
     DONNÉES DE SECOURS
     Si le backend envoie ces variables, ce sont elles qui sont utilisées.
     Sinon, on prend ces exemples (ceux de la maquette).
     ============================================================ --}}
@php
    $prochainId = $prochainId ?? 'zds-0007';
    $postes     = $postes ?? ['Caissier', 'Cuisinier', 'Assistant cuisinier', 'Livreur'];

    $employes = $employes ?? [
        ['id' => 'zds-0006', 'poste' => 'Livreur',             'nom' => 'Mballa', 'prenom' => 'Ruth',      'tel' => '6 77 12 40 18', 'email' => 'ruth.mballa@exemple.com'],
        ['id' => 'zds-0005', 'poste' => 'Assistant cuisinier', 'nom' => 'KENMOE', 'prenom' => 'Valdes',    'tel' => '6 55 90 21 07', 'email' => 'valdes.kenmoe@exemple.com'],
        ['id' => 'zds-0004', 'poste' => 'Cuisinier',           'nom' => 'Njoya',  'prenom' => 'Aïcha',     'tel' => '6 99 34 56 81', 'email' => 'aicha.njoya@exemple.com'],
        ['id' => 'zds-0003', 'poste' => 'Caissier',            'nom' => 'Mbarga', 'prenom' => 'Jean-Paul', 'tel' => '6 71 08 33 92', 'email' => 'jp.mbarga@exemple.com'],
        ['id' => 'zds-0002', 'poste' => 'Cuisinier',           'nom' => 'Kamga',  'prenom' => 'Hervé',     'tel' => '6 52 47 10 66', 'email' => 'herve.kamga@exemple.com'],
        ['id' => 'zds-0001', 'poste' => 'Caissier',            'nom' => 'Eyenga', 'prenom' => 'Sandrine',  'tel' => '6 90 25 74 13', 'email' => 'sandrine.eyenga@exemple.com'],
    ];
@endphp

{{-- La page est coupée en 2 colonnes : formulaire (1/3) et liste (2/3) --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">


    {{-- ============================================================
         COLONNE GAUCHE : le formulaire « Créer un compte »
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-6 text-black lg:col-span-1">

        <h1 class="border-b border-primary pb-3 font-serif text-3xl">Créer un compte</h1>

        {{-- id="form-employe" : le JavaScript s'en sert pour retrouver le formulaire.
             action="#" : le backend mettra ici l'adresse qui enregistre l'employé. --}}
        <form id="form-employe" method="POST" action="#" class="mt-4 space-y-4">

            {{-- Jeton de sécurité obligatoire dans Laravel (sans lui : erreur 419) --}}
            @csrf

            {{-- Chaque ligne <x-form-field> = une étiquette + une case --}}
            <x-form-field label="Identifiant" name="identifiant" :value="$prochainId" readonly />
            <x-form-field label="Nom(s)" name="nom" required />
            <x-form-field label="Prénom(s)" name="prenom" required />

            {{-- Numéro camerounais : 9 chiffres qui commencent par 6, avec ou sans +237 et espaces --}}
            <x-form-field label="Téléphone" name="telephone" type="tel"
                          placeholder="6 77 12 40 18"
                          pattern="(\+237\s?)?6(\s?[0-9]){8}"
                          title="Numéro camerounais : 9 chiffres commençant par 6 (ex : 6 77 12 40 18)"
                          required />

            {{-- E-mail : texte, @, domaine, point, puis au moins 2 lettres --}}
            <x-form-field label="E-mail" name="email" type="email"
                          placeholder="nom@exemple.com"
                          pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}"
                          title="Adresse e-mail valide (ex : nom@exemple.com)"
                          required />

            {{-- Le poste : liste déroulante construite à partir de $postes --}}
            <div>
                <label for="poste" class="mb-1 block text-sm">Poste</label>
                <select id="poste" name="poste" required
                        class="w-full rounded-xl border border-primary bg-white px-4 py-3">
                    <option value="">Choisir un poste</option>
                    @foreach ($postes as $poste)
                        <option value="{{ $poste }}">{{ $poste }}</option>
                    @endforeach
                </select>
            </div>

            <x-form-field label="Date de naissance" name="date_naissance" type="date" required />

            {{-- Règle du cahier des charges : 1 majuscule et 1 chiffre minimum --}}
            <x-form-field label="Mot de passe provisoire" name="mot_de_passe" type="password"
                          pattern="(?=.*[A-Z])(?=.*[0-9]).+"
                          title="1 majuscule et 1 chiffre minimum"
                          required />
            <p class="text-xs text-gray-500">1 majuscule et 1 chiffre minimum</p>

            <button type="submit"
                    class="w-full rounded-full bg-primary px-6 py-3 text-lg text-black transition hover:opacity-80">
                Créer le compte
            </button>
        </form>
    </div>


    {{-- ============================================================
         COLONNE DROITE : la liste des employés
         ============================================================ --}}
    <div class="overflow-hidden rounded-2xl border border-primary bg-white text-black lg:col-span-2">

        {{-- Titre + barre de recherche --}}
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <h2 class="text-3xl font-bold">Employés</h2>

            <input id="recherche" type="search" placeholder="Rechercher un employé"
                   class="w-full rounded-xl border border-primary px-4 py-3 sm:w-80">
        </div>

        {{-- Le tableau (défile sur les côtés si l'écran est trop étroit) --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">

                {{-- En-tête --}}
                <thead>
                    <tr class="bg-stone-200 text-lg font-bold italic">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Poste</th>
                        <th class="px-6 py-4">Nom</th>
                        <th class="px-6 py-4">Prénom</th>
                        <th class="px-6 py-4">Tél</th>
                        <th class="px-6 py-4">E-mail</th>
                    </tr>
                </thead>

                {{-- Corps : une ligne par employé --}}
                <tbody id="liste-employes">
                    @forelse ($employes as $e)
                        <tr class="border-b border-stone-200 italic">
                            <td class="px-6 py-5 text-xl font-bold">{{ $e['id'] }}</td>
                            <td class="px-6 py-5">{{ $e['poste'] }}</td>
                            <td class="px-6 py-5">{{ $e['nom'] }}</td>
                            <td class="px-6 py-5">{{ $e['prenom'] }}</td>
                            <td class="whitespace-nowrap px-6 py-5">{{ $e['tel'] }}</td>
                            <td class="break-all px-6 py-5">{{ $e['email'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-5 text-center">Aucun employé pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>



@endsection