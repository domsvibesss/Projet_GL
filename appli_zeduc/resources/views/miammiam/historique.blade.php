@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Historique')
@section('page', 'historique')
@section('bodyClass', 'bg-black min-h-screen text-white')

@section('content')
<main class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-[Gilda_Display] mb-8">Historique des commandes</h1>

    <div id="liste-commandes" class="space-y-4"></div>

    <template id="modele-commande">
        <div class="commande bg-white/5 rounded-xl p-5 flex justify-between items-center">
            <div>
                <p class="commande-date text-sm text-gray-400"></p>
                <p class="commande-id font-semibold"></p>
                <p class="commande-statut text-amber-500"></p>
            </div>
            <div class="text-right">
                <p class="commande-total font-bold"></p>
                <a id="reclamer" href="#" class="text-sm text-red-400 underline">Faire une réclamation</a>
            </div>
        </div>
    </template>
</main>
@endsection

@push('scripts')
    @vite('resources/js/historique.js')
@endpush