@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Réclamations')
@section('page', 'reclamations')
@section('bodyClass', 'min-h-screen bg-black text-white')

@section('content')
<main class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-[Gilda_Display] mb-8">Mes réclamations</h1>

    <div id="liste-reclamations" class="space-y-4"></div>

    <template id="modele-reclamation">
        <div class="reclamation bg-white/5 rounded-xl p-5 flex justify-between items-center">
            <div>
                <p class="reclamation-date text-sm text-gray-400"></p>
                <p class="reclamation-commande font-semibold"></p>
                <p class="reclamation-message text-gray-300"></p>
            </div>
            <span class="reclamation-statut px-3 py-1 rounded-full text-sm"></span>
        </div>
    </template>
</main>
@endsection

@push('scripts')
    @vite('resources/js/reclamations-page.js')
@endpush