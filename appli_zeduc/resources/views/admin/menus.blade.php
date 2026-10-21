@extends('layouts.admin')

@section('title', 'Gestion des menus')

@section('content')

{{-- Valeurs de secours : le backend les remplacera en envoyant $menus --}}
@php
    $menus = $menus ?? [
        ['id' => 1, 'titre' => 'Poisson braisé',    'prix' => 3000, 'image' => 'images/admin/poisson-braise.png'],
        ['id' => 2, 'titre' => 'Okok et baton de manioc',  'prix' => 1000, 'image' => 'images/admin/okok.png'],
        ['id' => 3, 'titre' => 'Porc braisé',       'prix' => 3000, 'image' => 'images/admin/porc-braise.png'],
        ['id' => 4, 'titre' => 'Spaghettis sautés', 'prix' => 1000, 'image' => 'images/admin/spaghettis.png'],
    ];
@endphp

<h1 class="text-center font-serif text-5xl">Gestion des menus</h1>

{{-- Bouton qui mène à la page d'ajout --}}
<div class="mt-8 flex justify-center">
    <a href="/admin/menus/ajouter"
       class="w-full max-w-md rounded-full bg-primary px-6 py-4 text-center text-xl font-semibold text-white transition hover:opacity-80">
        Ajouter un menu
    </a>
</div>

{{-- La grille : 1 colonne sur téléphone, 2 sur tablette, 4 sur ordinateur --}}
<div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($menus as $m)
        <x-manage-card :id="$m['id']" :titre="$m['titre']" :image="$m['image']"
                    :detail="number_format($m['prix'], 0, ',', ' ') . ' F'" />
    @endforeach
</div>

@endsection