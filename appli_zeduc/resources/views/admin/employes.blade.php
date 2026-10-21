@extends('layouts.admin')

@section('title', 'Gestion des employes')

@section('content')

{{-- Valeurs de secours : le backend les remplacera en envoyant $employes --}}
@php
    $employes = $employes ?? [
        ['id' => 1, 'titre' => 'Cuisinier',           'image' => 'images/admin/cuisinier.png'],
        ['id' => 2, 'titre' => 'Assistant Cuisinier', 'image' => 'images/admin/assistant-cuisinier.png'],
        ['id' => 3, 'titre' => 'Livreur',             'image' => 'images/admin/livreur.png'],
        ['id' => 4, 'titre' => 'Manager',             'image' => 'images/admin/manager.png'],
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Gestion des employes</h1>

{{-- Bouton qui mènera à la page d'ajout (on la crée plus tard) --}}
<div class="mt-8 flex justify-center">
    <a href="/admin/employes/ajouter"
       class="w-full max-w-md rounded-full bg-primary px-6 py-4 text-center text-xl font-semibold text-white transition hover:opacity-80">
        Ajouter un employe
    </a>
</div>

{{-- La grille : 1 colonne sur téléphone, 2 sur tablette, 4 sur ordinateur --}}
<div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($employes as $e)
        <x-manage-card :id="$e['id']" :titre="$e['titre']" :image="$e['image']" />
    @endforeach
</div>

@endsection