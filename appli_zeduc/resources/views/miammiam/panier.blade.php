@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Panier')
@section('page', 'panier')
@section('bodyClass', 'bg-black min-h-screen text-white')

@section('background')
<div class="fixed inset-0 -z-10 opacity-20">
    <img src="{{ asset('images/PorcBraise.jpeg') }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-sm">
</div>
@endsection

@section('content')
<main class="max-w-5xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-10">
    <div class="md:col-span-2">
        <h1 class="text-3xl font-[Gilda_Display] mb-6">Votre Panier</h1>
        <div id="liste-articles" class="space-y-4"></div>

        <template id="modele-article">
            <div class="article flex items-center justify-between bg-white/5 rounded-xl p-4">
                <div class="flex items-center gap-4">
                    <img class="article-image w-16 h-16 object-cover rounded-lg" src="" alt="">
                    <div>
                        <p class="article-nom font-semibold"></p>
                        <p class="article-prix text-amber-500"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="article-moins w-8 h-8 border border-amber-700 rounded-full">-</button>
                    <span class="article-quantite"></span>
                    <button class="article-plus w-8 h-8 border border-amber-700 rounded-full">+</button>
                    <button class="article-supprimer text-red-500 ml-3">✕</button>
                </div>
            </div>
        </template>

        <a href="{{ route('miammiam.menu') }}" class="inline-block mt-6 text-amber-500 underline">
            Voir le menu
        </a>
    </div>

    <aside class="bg-white/5 rounded-xl p-6 h-fit">
        <h2 class="text-xl font-[Handlee] mb-4">Facture</h2>
        <div class="flex justify-between mb-2">
            <span>Sous-total</span>
            <span id="facture-sous-total">0 FCFA</span>
        </div>
        <div class="flex justify-between mb-4">
            <span>Livraison</span>
            <span id="facture-livraison">0 FCFA</span>
        </div>
        <div class="flex justify-between font-bold text-lg border-t border-white/10 pt-3">
            <span>Total</span>
            <span id="facture-total">0 FCFA</span>
        </div>
        <button id="bouton-payer" class="w-full mt-6 py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
            Passer au paiement
        </button>
    </aside>
</main>
@endsection

@push('scripts')
    @vite('resources/js/panier-page.js')
@endpush