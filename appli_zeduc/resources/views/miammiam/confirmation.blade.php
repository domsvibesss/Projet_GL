@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Confirmation')
@section('page', 'confirmation')
@section('bodyClass', 'bg-black min-h-screen text-white flex items-center justify-center')

@section('background')
<div class="fixed inset-0 -z-10 opacity-20">
    <img src="{{ asset('images/PorcBraise.jpeg') }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-sm">
</div>
@endsection

@section('content')
<main class="max-w-md mx-auto px-6 py-12 text-center bg-white/5 rounded-2xl">
    <svg class="w-20 h-20 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <h1 class="text-2xl font-[Gilda_Display] mb-2">Commande confirmée !</h1>
    <p class="text-gray-300 mb-6">Votre commande a bien été enregistrée et est en cours de préparation.</p>

    <a id="tracer" href="#" class="block mb-4 text-amber-500 underline">Suivre ma commande</a>

    <a href="{{ route('miammiam.accueil') }}"
       class="inline-block px-8 py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
        Rentrer à l'accueil
    </a>
</main>
@endsection