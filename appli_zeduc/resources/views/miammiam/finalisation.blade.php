@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Finalisation')
@section('page', 'finalisation')
@section('bodyClass', 'bg-black min-h-screen text-white')

@section('background')
<div class="fixed inset-0 -z-10 opacity-20">
    <img src="{{ asset('images/PorcBraise.jpeg') }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-sm">
</div>
@endsection

@section('content')
<main class="max-w-5xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-10">
    <form id="formulaire-paiement" class="md:col-span-2 space-y-6">

        <div>
            <h2 class="text-xl font-[Handlee] mb-3">Adresse de livraison</h2>
            <input type="text" id="adresse" placeholder="Votre adresse"
                   class="w-full p-3 rounded-lg bg-white/10 outline-none">
            <button type="button" id="ouvrir-modale-cites" class="mt-2 text-amber-500 underline">
                Choisir une cité sur la carte
            </button>
        </div>

        <div>
            <h2 class="text-xl font-[Handlee] mb-3">Moyen de paiement</h2>

            <div class="flex gap-4 mb-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="moyen-paiement" value="mastercard">
                    <img src="{{ asset('images/paiement/mastercard.png') }}" class="h-8" alt="Mastercard">
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="moyen-paiement" value="mtn">
                    <img src="{{ asset('images/paiement/mtn.png') }}" class="h-8" alt="MTN Mobile Money">
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="moyen-paiement" value="orange">
                    <img src="{{ asset('images/paiement/orange.png') }}" class="h-8" alt="Orange Money">
                </label>
            </div>

            <div id="champs-carte" class="hidden space-y-3">
                <input type="text" id="numero-carte" placeholder="Numéro de carte" class="w-full p-3 rounded-lg bg-white/10">
                <div class="flex gap-3">
                    <input type="text" id="expiration-carte" placeholder="MM/AA" class="w-1/2 p-3 rounded-lg bg-white/10">
                    <input type="text" id="cvv-carte" placeholder="CVV" class="w-1/2 p-3 rounded-lg bg-white/10">
                </div>
            </div>

            <div id="champs-mobile" class="hidden space-y-3">
                <input type="text" id="numero-mobile" placeholder="Numéro mobile money" class="w-full p-3 rounded-lg bg-white/10">
            </div>
        </div>
    </form>

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
        <button id="bouton-payer-final" class="w-full mt-6 py-3 bg-amber-700 hover:bg-amber-600 rounded-full font-semibold">
            Payer maintenant
        </button>
    </aside>
</main>

<div id="modale-cites" class="hidden fixed inset-0 bg-black/70 items-center justify-center z-50">
    <div class="bg-[#111] rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-xl mb-4">Choisissez une cité</h3>
        <div id="liste-cites" class="space-y-2 max-h-64 overflow-y-auto"></div>
        <button id="fermer-modale-cites" class="mt-4 text-amber-500">Fermer</button>
    </div>
</div>

<div id="modale-carte" class="hidden fixed inset-0 bg-black/70 items-center justify-center z-50">
    <div class="bg-[#111] rounded-xl p-6 w-full max-w-2xl">
        <div id="carte" class="w-full h-96 rounded-lg"></div>
        <button id="fermer-modale-carte" class="mt-4 text-amber-500">Fermer</button>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/finalisation.js')
@endpush