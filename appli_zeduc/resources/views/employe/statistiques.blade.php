@extends('layouts.employe')

@section('titre', 'Statistiques')

@php
  $max = max(array_column($stats, 'valeur'));
  $top2 = collect($stats)->sortByDesc('valeur')->take(2)->pluck('code')->all();
@endphp

@section('contenu')
  <main class="relative min-h-[calc(100vh-4rem)] bg-cover bg-center"
        style="background-image: url('{{ asset('images/fond-stats.jpg') }}'), url('{{ asset('images/fond-stats.png') }}');">
    <div class="absolute inset-0 bg-black/50"></div>

    <div class="relative max-w-6xl mx-auto px-8 pt-8 pb-12">
      <section class="flex justify-between px-8 mb-10">
        @foreach ($resume as $carte)
          <div class="w-60 bg-miam-sable rounded-md {{ $carte['grand'] ? 'px-6' : 'px-4' }} py-3 text-white">
            <p class="{{ $carte['grand'] ? 'text-4xl' : 'text-2xl' }}">{{ $carte['valeur'] }}</p>
            <p class="text-[10px] {{ $carte['grand'] ? 'mt-1' : 'mt-2' }} text-white/80">{{ $carte['label'] }}</p>
          </div>
        @endforeach
      </section>

      <section class="bg-miam-gris rounded-lg px-4 pt-5 pb-2">
        <h2 class="text-base font-semibold text-black ml-5 mb-4">Commandes par jour</h2>

        <div class="flex items-end gap-8 h-[260px]">
          @foreach ($stats as $jour)
            <a href="{{ route('employe.statistique-journalier', $jour['code']) }}"
               class="flex-1 {{ in_array($jour['code'], $top2) ? 'bg-[#1a1a1a] text-white' : 'bg-miam-beige text-black' }} rounded-t-lg rounded-b-sm flex justify-center pt-4 text-sm font-semibold hover:opacity-90"
               style="height: {{ $jour['valeur'] / $max * 100 }}%">
              {{ $jour['valeur'] }}
            </a>
          @endforeach
        </div>

        <div class="flex gap-8 mt-1">
          @foreach ($stats as $jour)
            <span class="flex-1 text-center font-serif text-sm text-black">{{ $jour['label'] }}</span>
          @endforeach
        </div>
      </section>

      <p class="text-white/50 text-xs mt-3">Astuce : clique sur une barre pour voir le détail du jour.</p>
    </div>
  </main>
@endsection
