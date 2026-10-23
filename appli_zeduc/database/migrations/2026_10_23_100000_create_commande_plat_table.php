<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lignes de commande : quels plats, et combien, dans chaque commande.
 * Une commande contient plusieurs plats, un plat est dans plusieurs commandes
 * (plats.commande_id ne permettait qu'une seule commande par plat).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commande_plat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('plat_id')->constrained('plats')->cascadeOnDelete();
            $table->unsignedInteger('quantite')->default(1);
            // Prix du plat au moment de la commande (le prix du menu peut changer ensuite)
            $table->decimal('prix_unitaire', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_plat');
    }
};
