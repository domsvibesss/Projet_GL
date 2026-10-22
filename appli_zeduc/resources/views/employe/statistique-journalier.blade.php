@extends('layouts.app')

@section('titre', 'Statistique journalier')

@section('contenu')
  <main class="relative min-h-screen bg-cover bg-center"
        style="background-image: url('{{ asset('images/fond-stats.jpg') }}'), url('{{ asset('images/fond-stats.png') }}');">
    <div class="absolute inset-0 bg-black/50"></div>

    <div class="relative max-w-5xl mx-auto px-8 pt-6 pb-12">
      <div class="flex items-center justify-between mb-10">
        <a href="{{ route('employe.statistiques') }}" class="text-white/60 hover:text-white text-sm w-40">← Statistiques</a>
        <h1 class="bg-miam-sable text-white text-xl px-6 py-2 rounded-md">Statistique journalier</h1>
        <div class="w-40 flex justify-end">
          @include('partials.utilisateur', [
              'nom' => 'Valdes KENMOE',
              'role' => 'Assistant cuisinier',
              'image' => 'images/chef.png',
              'emoji' => '👨‍🍳',
          ])
        </div>
      </div>

      <section class="max-w-xl mx-auto bg-[#B5B5B5] rounded-lg px-6 pt-6 pb-10 text-black">
        <div class="flex justify-between items-start">
          <span class="text-base">{{ $nomJour }}</span>
          <span class="text-2xl">{{ $total }} commandes</span>
        </div>

        <ul class="list-disc pl-5 mt-12 space-y-0.5 text-lg">
          @forelse ($plats as $plat)
            <li>
              <div class="grid grid-cols-[1fr_auto] pr-6">
                <span>{{ $plat['nom'] }}</span>
                <span>{{ str_pad($plat['nb'], 2, '0', STR_PAD_LEFT) }} commandes</span>
              </div>
            </li>
          @empty
            <li class="list-none -ml-5 text-sm text-black/60">Détail non disponible pour ce jour.</li>
          @endforelse
        </ul>
      </section>
    </div>
  </main>
@endsection
