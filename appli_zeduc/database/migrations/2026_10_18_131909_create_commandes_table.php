<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant_local', 10, 2);
            $table->integer('points_accumules')->default(0);
            $table->dateTime('date_commande');
            $table->string('etat'); 
            $table->string('destination');

            $table->foreignId('employe_id')->nullable()->constrained('employes')->nullOnDelete();
            $table->foreignId('etudiant_id')->constrained('etudiants')->cascadeOnDelete();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
