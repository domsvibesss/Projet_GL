@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Accueil')
@section('page', 'accueil')

@section('background')
<div class="fixed inset-0 overflow-hidden -z-10">
    <div class="absolute w-96 h-96 bg-amber-700/20 rounded-full blur-3xl -top-20 -left-20"></div>
    <div class="absolute w-96 h-96 bg-green-700/10 rounded-full blur-3xl top-1/3 right-0"></div>
    <div class="absolute w-96 h-96 bg-amber-900/20 rounded-full blur-3xl bottom-0 left-1/4"></div>
</div>
@endsection

@section('content')
<main class="max-w-5xl mx-auto px-6 py-16 text-center">
    <h1 class="bloc-entree text-4xl md:text-6xl font-[Gilda_Display] mb-4" style="--i:1">Mon Miam Miam</h1>
    <p class="bloc-entree text-lg text-gray-300 mb-8" style="--i:2">
        Les meilleurs plats camerounais livrés chez vous, encore chauds.
    </p>
    <h2 class="bloc-entree text-2xl font-[Handlee] mb-10" style="--i:3">
        Découvrez le plat du jour
    </h2>

    <div id="plat-scene" class="relative mx-auto w-72 h-72 mb-12" style="perspective:1000px;">
        <div id="plat-inclinaison" class="relative w-full h-full transition-transform duration-200 ease-out">
            <img id="plat-image" src="{{ asset('images/PorcRoti.jpeg') }}" alt="Plat du jour"
                 class="w-full h-full object-cover rounded-full shadow-2xl">
            <div id="plat-reflet" class="absolute inset-0 rounded-full pointer-events-none"></div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('miammiam.menu') }}"
           class="px-8 py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold transition">
            Voir le menu
        </a>
        <a href="{{ route('miammiam.plat-du-jour') }}"
           class="px-8 py-3 border border-amber-700 hover:bg-amber-700/20 rounded-full font-semibold transition">
            Commander le plat du jour
        </a>
    </div>
</main>
@endsection

@push('scripts')
    @vite('resources/js/accueil.js')
@endpush