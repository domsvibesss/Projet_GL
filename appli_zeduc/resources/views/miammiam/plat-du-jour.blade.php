@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Plat du jour')
@section('page', 'plat-du-jour')
@section('bodyClass', 'bg-black min-h-screen text-white')

@section('content')
<main class="max-w-5xl mx-auto px-6 py-12">

    <div id="plat-details" class="grid md:grid-cols-2 gap-10"
         data-plats='@json($plats)'>

        <div>
            <img id="plat-image-detail" src="" alt="" class="w-full rounded-xl shadow-lg">
            <div class="flex justify-between mt-4">
                <button id="plat-precedent" class="px-4 py-2 border border-amber-700 rounded-full">← Précédent</button>
                <button id="plat-suivant" class="px-4 py-2 border border-amber-700 rounded-full">Suivant →</button>
            </div>
        </div>

        <div>
            <h1 id="plat-nom" class="text-3xl font-[Gilda_Display] mb-2"></h1>
            <p id="plat-description" class="text-gray-300 mb-4"></p>
            <p id="plat-prix" class="text-2xl text-amber-500 font-bold mb-6"></p>

            <div class="flex items-center gap-4 mb-6">
                <button id="quantite-moins" class="w-10 h-10 border border-amber-700 rounded-full">-</button>
                <span id="quantite-valeur">1</span>
                <button id="quantite-plus" class="w-10 h-10 border border-amber-700 rounded-full">+</button>
            </div>

            <h2 class="text-xl font-[Handlee] mb-3">Compléments</h2>
            <div class="flex gap-4 mb-6">
                <div class="complement" draggable="true" data-complement="riz" data-nom="Riz" data-prix="300">
                    <img src="{{ asset('images/complements/riz.png') }}" alt="Riz" class="w-16 h-16">
                </div>
                <div class="complement" draggable="true" data-complement="plantains" data-nom="Plantains" data-prix="300">
                    <img src="{{ asset('images/complements/plantains.png') }}" alt="Plantains" class="w-16 h-16">
                </div>
                <div class="complement" draggable="true" data-complement="batons" data-nom="Bâtons de manioc" data-prix="300">
                    <img src="{{ asset('images/complements/batons.png') }}" alt="Bâtons de manioc" class="w-16 h-16">
                </div>
            </div>

            <div id="ticket-caisse" class="bg-white/5 rounded-xl p-4 mb-6">
                <h3 class="font-semibold mb-2">Ticket de caisse</h3>
                <ul id="ticket-lignes" class="text-sm text-gray-300 space-y-1"></ul>
                <div class="border-t border-white/10 mt-2 pt-2 flex justify-between font-bold">
                    <span>Total</span>
                    <span id="ticket-total">0 FCFA</span>
                </div>
            </div>

            <button id="ajouter-panier" class="w-full py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
                Ajouter au panier
            </button>

            <a href="{{ route('miammiam.panier') }}" class="block text-center mt-4 text-amber-500 underline">
                Consulter le panier
            </a>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    @vite('resources/js/plat-du-jour.js')
@endpush