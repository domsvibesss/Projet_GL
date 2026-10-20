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
        Schema::create('administrateur_reponse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrateur_id')->constrained('administrateurs')->cascadeOnDelete();
            $table->foreignId('reponse_id')->constrained('reponses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrateur_reponse');
    }
};
