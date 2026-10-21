@extends('layouts.admin')

@section('title', "Ajout d'un employé")

@section('content')

{{-- Valeur de secours : le backend enverra la vraie liste --}}
@php
    $postes = $postes ?? ['Caissier', 'Cuisinier', 'Assistant cuisinier', 'Livreur', 'Manager'];
@endphp

<h1 class="text-center font-serif text-5xl">Ajout d'un employé</h1>

{{-- enctype : obligatoire pour envoyer un fichier (la photo) --}}
<form id="form-admin" method="POST" action="#" enctype="multipart/form-data"
      data-message-succes="Employé ajouté (démo : rien n'est enregistré).">

    {{-- Jeton de sécurité obligatoire dans Laravel --}}
    @csrf

    {{-- Grand cadre arrondi autour des deux cartes --}}
    <div class="mt-8 rounded-[2.5rem] border border-white/70 p-6 lg:p-14">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">


            {{-- ===== GAUCHE : la photo ===== --}}
            <div class="rounded-2xl bg-white p-8 text-black lg:col-span-2">

                {{-- Zone d'aperçu : l'image apparaît ici quand on choisit un fichier --}}
                <div class="mx-auto flex aspect-square w-full max-w-sm items-center justify-center overflow-hidden bg-gray-200">
                    <img id="apercu-photo" alt="Aperçu de la photo" class="hidden h-full w-full object-cover">
                </div>

                {{-- Le vrai champ fichier est caché : notre bouton le déclenche --}}
                <input id="champ-photo" name="photo" type="file" accept="image/*" class="hidden">

                <button type="button" id="btn-ajouter-photo"
                        class="mx-auto mt-10 block w-full max-w-sm rounded-xl bg-primary py-3 text-xl font-semibold text-white transition hover:opacity-80">
                    Ajouter une photo
                </button>
                <button type="button" id="btn-supprimer-photo"
                        class="mx-auto mt-5 block w-full max-w-sm rounded-xl bg-primary py-3 text-xl font-semibold text-white transition hover:opacity-80">
                    Supprimer la photo
                </button>
            </div>


            {{-- ===== DROITE : les informations ===== --}}
            <div class="rounded-2xl bg-white p-8 text-black lg:col-span-3">

                <h2 class="text-xl font-semibold">Informations de l'employé</h2>

                <div class="mt-6 space-y-5">

                    <div>
                        <label for="nom" class="mb-1 block">Nom</label>
                        <input id="nom" name="nom" type="text" required
                               class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label for="prenom" class="mb-1 block">Prénom</label>
                        <input id="prenom" name="prenom" type="text" required
                               class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    {{-- Le rôle : liste déroulante construite à partir de $postes --}}
                    <div>
                        <label for="role" class="mb-1 block">Rôle</label>
                        <select id="role" name="role" required
                                class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Choisir un rôle</option>
                            @foreach ($postes as $poste)
                                <option value="{{ $poste }}">{{ $poste }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Numéro camerounais : 9 chiffres qui commencent par 6 --}}
                    <div>
                        <label for="contact" class="mb-1 block">Contact</label>
                        <input id="contact" name="contact" type="tel" required
                               placeholder="6 77 12 40 18"
                               pattern="(\+237\s?)?6(\s?[0-9]){8}"
                               title="Numéro camerounais : 9 chiffres commençant par 6 (ex : 6 77 12 40 18)"
                               class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                </div>

                {{-- Message de confirmation (étape suivante) --}}
                <p id="confirmation-admin" class="mt-4 min-h-6 text-center text-green-700"></p>

                <button type="submit"
                        class="mx-auto mt-2 block w-full max-w-md rounded-full bg-primary px-6 py-3 text-xl font-semibold text-white transition hover:opacity-80">
                    Ajouter l'employé
                </button>
            </div>

        </div>
    </div>
</form>

@endsection