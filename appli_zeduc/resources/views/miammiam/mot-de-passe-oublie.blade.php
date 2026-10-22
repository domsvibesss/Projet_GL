@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Mot de passe oublié')
@section('page', 'mot-de-passe-oublie')
@section('bodyClass', 'min-h-screen bg-black text-white flex items-center justify-center')

@section('background')
<div class="fixed inset-0 -z-10 opacity-10">
    <img src="{{ asset('images/mdpoublie.png') }}" alt="" class="absolute left-0 top-0 h-96">
    <img src="{{ asset('images/mdpoublie.png') }}" alt="" class="absolute right-0 bottom-0 h-96">
</div>
@endsection

@section('content')
<main class="max-w-md mx-auto px-6 py-12 w-full">
    <h1 class="text-3xl font-[Gilda_Display] mb-8 text-center">Mot de passe oublié</h1>

    <form id="formulaire" class="space-y-4">
        <input type="email" id="email" placeholder="Votre adresse e-mail" class="w-full p-3 rounded-lg bg-white/10 outline-none" required>
        <button type="submit" class="w-full py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
            Envoyer le lien de réinitialisation
        </button>
    </form>

    <a href="{{ route('miammiam.connexion') }}" class="block text-center mt-6 text-amber-500 underline">
        Retour à la connexion
    </a>
</main>
@endsection