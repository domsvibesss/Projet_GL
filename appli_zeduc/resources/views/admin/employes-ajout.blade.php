@extends('layouts.admin')

@section('title', isset($employe) ? 'Modifier un employe' : 'Ajouter un employe')

@section('content')

{{-- Si la variable employe existe, on est en mode modification --}}
@php
    $employe      = $employe ?? null;
    $modification = $employe !== null;
    $postes       = $postes ?? ['Cuisinier', 'Assistant Cuisinier', 'Livreur', 'Manager'];
@endphp

<a href="/admin/employes" class="text-primary transition hover:opacity-80">
    &larr; Retour aux employes
</a>
<h1 class="text-center font-serif text-5xl">{{ $modification ? 'Modifier un employe' : 'Ajouter un employe' }}</h1>

{{-- action="#" : le formulaire est envoyé à l'URL de la page (voir routes/web.php) --}}
{{-- enctype : obligatoire pour envoyer un fichier (la photo) --}}
<form id="form-admin" method="POST" action="#" enctype="multipart/form-data"
      class="mx-auto mt-10 grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-[18rem_1fr]">

    @csrf
    {{-- En modification, Laravel attend la methode PUT --}}
    @if ($modification) @method('PUT') @endif

    {{-- ===== Carte photo (gauche) ===== --}}
    <div class="flex flex-col items-center gap-4 rounded-2xl bg-white p-6 text-black">

        {{-- Zone grise affichee tant qu'il n'y a pas de photo --}}
        <div id="photo-vide" class="{{ $modification && $employe['photo'] ? 'hidden' : '' }} flex aspect-square w-full items-center justify-center rounded-xl bg-gray-200 text-gray-500">
            Aucune photo
        </div>

        <img id="apercu-photo" alt="Aperçu de la photo"
             @if ($modification && $employe['photo']) src="{{ asset($employe['photo']) }}" @endif
             class="{{ $modification && $employe['photo'] ? '' : 'hidden' }} aspect-square w-full rounded-xl object-cover">

        {{-- Champ fichier cache : les boutons ci-dessous le declenchent --}}
        <input id="champ-photo" name="photo" type="file" accept="image/*" class="hidden">

        <button type="button" id="btn-ajouter-photo"
                class="w-full rounded-full bg-primary py-2 font-semibold text-white transition hover:opacity-80">Ajouter une photo</button>
        <button type="button" id="btn-supprimer-photo"
                class="w-full rounded-full bg-black py-2 font-semibold text-white transition hover:opacity-80">Supprimer la photo</button>
    </div>

    {{-- ===== Carte formulaire (droite) ===== --}}
    <div class="space-y-5 rounded-2xl bg-white p-8 text-black">

        <div>
            <label for="nom" class="text-sm font-semibold">Nom</label>
            <input id="nom" name="nom" type="text" required value="{{ old('nom', $employe['nom'] ?? '') }}"
                   class="mt-1 w-full rounded-lg border border-black px-4 py-3">
        </div>

        <div>
            <label for="prenom" class="text-sm font-semibold">Prénom</label>
            <input id="prenom" name="prenom" type="text" required value="{{ old('prenom', $employe['prenom'] ?? '') }}"
                   class="mt-1 w-full rounded-lg border border-black px-4 py-3">
        </div>

        <div>
            <label for="poste" class="text-sm font-semibold">Rôle</label>
            <select id="poste" name="poste" required class="mt-1 w-full rounded-lg border border-black px-4 py-3">
                @foreach ($postes as $p)
                    <option value="{{ $p }}" @selected(old('poste', $employe['poste'] ?? '') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="contact" class="text-sm font-semibold">Contact</label>
            <input id="contact" name="contact" type="tel" required value="{{ old('contact', $employe['contact'] ?? '') }}"
                   pattern="(\+237\s?)?6(\s?[0-9]){8}" title="Numéro camerounais, ex : +237 6 90 11 22 33"
                   class="mt-1 w-full rounded-lg border border-black px-4 py-3">
        </div>

        {{-- Message après envoi : erreur de validation (rouge) ou confirmation (vert) --}}
        <p id="confirmation-admin" class="font-semibold {{ $errors->any() ? 'text-red-700' : 'text-green-700' }}">{{ $errors->first() ?: session('success') }}</p>

        <button type="submit" class="w-full rounded-full bg-primary py-3 text-lg font-semibold text-white transition hover:opacity-80">
            {{ $modification ? 'Enregistrer les modifications' : "Ajouter l'employé" }}
        </button>
    </div>
</form>

@endsection