@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Inscription')
@section('page', 'inscription')
@section('bodyClass', 'min-h-screen bg-black text-white flex items-center justify-center')

@section('background')
<div class="fixed inset-0 -z-10 opacity-10">
    <img src="{{ asset('images/groceries.png') }}" alt="" class="absolute right-0 bottom-0 h-96">
</div>
@endsection

@section('content')
<main class="max-w-md mx-auto px-6 py-12 w-full">
    <h1 class="text-3xl font-[Gilda_Display] mb-8 text-center">Créer un compte</h1>

    <form id="formulaire" class="space-y-4">
        <input type="text" id="username" placeholder="Nom d'utilisateur" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>
        <input type="email" id="email" placeholder="Adresse e-mail" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>
        <input type="tel" id="telephone" placeholder="Téléphone" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>
        <input type="password" id="password" placeholder="Mot de passe" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>

        <button type="submit" class="w-full py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
            S'inscrire
        </button>
    </form>

    <p class="text-center mt-6 text-gray-400">
        Déjà un compte ?
        <a href="{{ route('miammiam.connexion') }}" class="text-amber-500 underline">Se connecter</a>
    </p>
</main>
@endsection

@push('scripts')
    @vite('resources/js/inscription.js')
@endpush