@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Connexion')
@section('page', 'connexion')
@section('bodyClass', 'min-h-screen bg-black text-white flex items-center justify-center')

@section('background')
<div class="fixed inset-0 -z-10 opacity-10">
    <img src="{{ asset('images/woman-chef.png') }}" alt="" class="absolute left-0 bottom-0 h-96">
    <img src="{{ asset('images/man-chef.png') }}" alt="" class="absolute right-0 bottom-0 h-96">
</div>
@endsection

@section('content')
<main class="max-w-md mx-auto px-6 py-12 w-full">
    <h1 class="text-3xl font-[Gilda_Display] mb-8 text-center">Connexion</h1>

    <form id="formulaire" class="space-y-4">
        <input type="email" id="email" placeholder="Adresse e-mail" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>
        <input type="password" id="password" placeholder="Mot de passe" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>

        <a href="{{ route('miammiam.mot-de-passe-oublie') }}" class="block text-sm text-amber-500 underline">
            Mot de passe oublié ?
        </a>

        <button type="submit" class="w-full py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
            Se connecter
        </button>
    </form>

    <p class="text-center mt-6 text-gray-400">
        Pas encore de compte ?
        <a href="{{ route('miammiam.inscription') }}" class="text-amber-500 underline">S'inscrire</a>
    </p>
</main>
@endsection

@push('scripts')
    @vite('resources/js/connexion.js')
@endpush