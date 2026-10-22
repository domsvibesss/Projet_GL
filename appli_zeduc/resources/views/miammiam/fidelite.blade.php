@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Fidélité')
@section('page', 'fidelite')
@section('bodyClass', 'min-h-screen bg-black text-white')

@section('content')
<main class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-[Gilda_Display] mb-8 text-center">Programme de fidélité</h1>

    <div class="flex flex-col items-center mb-10">
        <div id="badge-actuel" class="w-24 h-24 rounded-full bg-amber-700 flex items-center justify-center text-3xl mb-3"></div>
        <p id="points-actuels" class="text-xl font-bold"></p>
        <p id="prochain-badge" class="text-sm text-gray-400"></p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <div>
            <h2 class="text-xl font-[Handlee] mb-4">Historique des points</h2>
            <div id="historique-points" class="space-y-2"></div>
        </div>

        <div class="bg-white/5 rounded-xl p-6 h-fit text-center">
            <h2 class="text-xl font-[Handlee] mb-4">Gagnez des points</h2>
            <p class="text-gray-300 mb-4">Partagez Mon Miam Miam et gagnez 30 points !</p>
            <button id="bouton-partager" class="px-6 py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
                Partager
            </button>
        </div>
    </div>

    <div id="modale-partage" class="hidden fixed inset-0 bg-black/70 items-center justify-center z-50">
        <div class="bg-[#111] rounded-xl p-6 w-full max-w-sm text-center">
            <p class="mb-4">Merci d'avoir partagé ! +30 points</p>
            <button id="fermer-modale-partage" class="text-amber-500">Fermer</button>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    @vite('resources/js/fidelite-page.js')
@endpush