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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('poste');
            $table->string('nom');
            $table->string('prenom');
            $table->string('tel');
            $table->string('email')->unique();
            $table->string('mdp');
            $table->date('date_naissance');
            $table->dateTime('date_enregistrement');
            $table->string('statut');
        
            $table->foreignId('gerant_id')->constrained('gerants')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
