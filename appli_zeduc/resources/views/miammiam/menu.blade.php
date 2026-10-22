@extends('layouts.miammiam')

@section('title', 'Mon Miam Miam - Menu')
@section('page', 'menu')
@section('bodyClass', 'bg-black min-h-screen text-white')

@section('background')
<div class="fixed inset-0 -z-10 opacity-10">
    <img src="{{ asset('images/cart.png') }}" alt="" class="absolute right-0 top-0 w-96">
</div>
@endsection

@section('content')
<main class="max-w-6xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-[Gilda_Display] mb-8 text-center">Notre Menu</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($plats as $plat)
            <article class="carte-plat bg-white/5 rounded-xl overflow-hidden cursor-pointer hover:scale-105 transition"
                      data-nom="{{ $plat['nom'] }}"
                      data-prix="{{ $plat['prix'] }}">
                <img src="{{ asset('images/' . $plat['image']) }}" alt="{{ $plat['nom'] }}"
                     class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold">{{ $plat['nom'] }}</h3>
                    <p class="text-amber-500 font-bold">{{ number_format($plat['prix'], 0, ',', ' ') }} FCFA</p>
                </div>
            </article>
        @endforeach
    </div>
</main>
@endsection

@push('scripts')
    @vite('resources/js/menu.js')
@endpush