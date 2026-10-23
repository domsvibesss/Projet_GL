@extends('layouts.admin')

@section('title', isset($menu) ? "Modification d'un menu" : "Ajout d'un menu")

@section('content')

{{-- Si la variable menu existe, on est en mode modification --}}
@php
    $menu         = $menu ?? null;
    $modification = $menu !== null;
@endphp

<a href="/admin/menus" class="text-primary transition hover:opacity-80">
    &larr; Retour aux menus
</a>
<h1 class="text-center font-serif text-5xl">{{ $modification ? "Modification d'un menu" : "Ajout d'un menu" }}</h1>

{{-- enctype : obligatoire pour envoyer un fichier (la photo) --}}
{{-- action="#" : le formulaire est envoyé à l'URL de la page (voir routes/web.php) --}}
<form id="form-admin" method="POST" action="#" enctype="multipart/form-data">

    {{-- Jeton de sécurité obligatoire dans Laravel --}}
    @csrf
    {{-- En modification, Laravel attend la méthode PUT --}}
    @if ($modification) @method('PUT') @endif

    {{-- Grand cadre arrondi autour des deux cartes --}}
    <div class="mt-8 rounded-[2.5rem] border border-white/70 p-6 lg:p-14">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">


            {{-- ===== GAUCHE : la photo du plat ===== --}}
            <div class="rounded-2xl bg-white p-8 text-black lg:col-span-2">

                <div class="mx-auto flex aspect-square w-full max-w-sm items-center justify-center overflow-hidden bg-gray-200">
                    <img id="apercu-photo" alt="Aperçu de la photo"
                         @if ($modification && $menu['photo']) src="{{ asset($menu['photo']) }}" @endif
                         class="{{ $modification && $menu['photo'] ? '' : 'hidden' }} h-full w-full object-cover">
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


            {{-- ===== DROITE : les informations du plat ===== --}}
            <div class="rounded-2xl bg-white p-8 text-black lg:col-span-3">

                <h2 class="text-xl font-semibold">Informations du plat</h2>

                <div class="mt-6 space-y-5">

                    <div>
                        <label for="nom" class="mb-1 block">Nom du plat</label>
                        <input id="nom" name="nom" type="text" required value="{{ old('nom', $menu['nom'] ?? '') }}"
                               class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label for="prix" class="mb-1 block">Prix (FCFA)</label>
                        <input id="prix" name="prix" type="number" min="0" step="50" required value="{{ old('prix', $menu['prix'] ?? '') }}"
                            class="w-full rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label for="description" class="mb-1 block">Description</label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full resize-none rounded-xl bg-gray-200 px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary">{{ old('description', $menu['description'] ?? '') }}</textarea>
                    </div>

                </div>

                {{-- Message après envoi : erreur de validation (rouge) ou confirmation (vert) --}}
                <p id="confirmation-admin" class="mt-4 min-h-6 text-center {{ $errors->any() ? 'text-red-700' : 'text-green-700' }}">{{ $errors->first() ?: session('success') }}</p>

                <button type="submit"
                        class="mx-auto mt-2 block w-full max-w-md rounded-full bg-primary px-6 py-3 text-xl font-semibold text-white transition hover:opacity-80">
                    {{ $modification ? 'Enregistrer les modifications' : 'Ajouter un plat' }}
                </button>
            </div>

        </div>
    </div>
</form>

@endsection