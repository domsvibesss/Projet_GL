@extends($layout ?? 'layouts.app')

@section('title', 'Statistiques')

@section('content')

{{-- Valeurs de secours : StatsController (page admin) envoie $stats et $parrains --}}
@php
    $parrains = $parrains ?? [
        ['initiales' => 'DD', 'nom' => 'Dominique Daphne', 'filleuls' => 17],
        ['initiales' => 'PW', 'nom' => 'Pethe William',    'filleuls' => 15],
        ['initiales' => 'ML', 'nom' => 'Messang Louis',    'filleuls' => 10],
        ['initiales' => 'RD', 'nom' => 'Rigeau Dajel',     'filleuls' => 7],
    ];
@endphp

<h1 class="text-center text-5xl text-primary mb-8">Statistiques</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ===== Colonne gauche ===== --}}
    <div class="space-y-6">

        {{-- Les 4 cartes --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card titre="Commandes d'aujourd'hui" valeur="{{ $stats['commandes_jour'] ?? '42' }}" />
            <x-stat-card titre="Commandes en cours" valeur="{{ $stats['commandes_en_cours'] ?? '8' }}" />
            <x-stat-card titre="Chiffre d'affaires" valeur="{{ $stats['chiffre_affaires'] ?? '185 000 F' }}" />
            <x-stat-card titre="Clients actifs" valeur="{{ $stats['clients_actifs'] ?? '312' }}" />
        </div>

        {{-- Mouvement des points --}}
        <div class="bg-white text-black rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-bold">Mouvement des Points</h2>
                <span class="bg-primary rounded-full px-4 py-1 text-sm">30 jours</span>
            </div>

            <p class="mt-4 text-sm">Émis et utilisés sur les 30 derniers jours.</p>

            <div class="flex gap-6 mt-3 text-sm">
                <span class="flex items-center gap-2"><span class="w-8 h-1 bg-red-500"></span> Points émis</span>
                <span class="flex items-center gap-2"><span class="w-8 h-1 bg-pink-300"></span> Points utilisés</span>
            </div>

            {{-- Zone du futur graphique --}}
            <div class="h-40 mt-4 border-b-2 border-black bg-gray-50"></div>

            <div class="grid grid-cols-3 mt-3">
                <div><p class="text-xs uppercase">Émis</p><p class="text-xl font-bold">{{ $stats['points_emis'] ?? '48 290' }}</p></div>
                <div><p class="text-xs uppercase">Utilisés</p><p class="text-xl font-bold">{{ $stats['points_utilises'] ?? '16 840' }}</p></div>
                <div><p class="text-xs uppercase">Taux</p><p class="text-xl font-bold">{{ $stats['points_taux'] ?? '34,9 %' }}</p></div>
            </div>
        </div>
    </div>

    {{-- ===== Colonne droite : Parrainage ===== --}}
    <div class="bg-white text-black rounded-2xl p-6">
        <h2 class="text-4xl font-bold">Parrainage</h2>
        <p class="mt-3 text-sm">Un bon poulet se partage. Voici ceux qui le savent.</p>

        <div class="grid grid-cols-3 gap-4 mt-6 py-4 border-y-2 border-black">
            <div><p class="text-xs uppercase">Invitations</p><p class="text-3xl font-bold">{{ $stats['invitations'] ?? '1 248' }}</p></div>
            <div><p class="text-xs uppercase">Converties</p><p class="text-3xl font-bold">{{ $stats['converties'] ?? '438' }}</p></div>
            <div><p class="text-xs uppercase">Conversion</p><p class="text-3xl font-bold">{{ $stats['conversion'] ?? '35 %' }}</p></div>
        </div>

        <h3 class="mt-6 text-sm uppercase">Meilleurs parrains</h3>

        <ul class="mt-4 space-y-5">
            @foreach ($parrains as $p)
                <li class="flex items-center gap-4">
                    <span class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white">
                        {{ $p['initiales'] }}
                    </span>
                    <span class="flex-1 text-xl font-bold">{{ $p['nom'] }}</span>
                    <span class="text-sm">{{ $p['filleuls'] }} filleuls</span>
                </li>
            @endforeach
        </ul>
    </div>

</div>
@endsection