<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapte la base aux pages de l'administrateur (sans modifier les migrations existantes).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Page Menus : photo du plat. L'admin n'est pas encore connecté : administrateur_id facultatif.
        Schema::table('plats', function (Blueprint $table) {
            $table->string('image')->nullable()->after('quantite');
            $table->foreignId('administrateur_id')->nullable()->change();
        });

        // Page Employés : le formulaire envoie nom, prénom, rôle, contact et photo.
        // Le reste sera rempli plus tard (connexion de l'employé, gérant responsable).
        Schema::table('employes', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('statut');
            $table->string('email')->nullable()->change();
            $table->string('mdp')->nullable()->change();
            $table->date('date_naissance')->nullable()->change();
            $table->foreignId('gerant_id')->nullable()->change();
        });

        // Page Promotions : une promotion est affichée comme une affiche
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('titre')->nullable()->after('id');
            $table->text('description')->nullable()->after('titre');
            $table->string('image')->nullable()->after('description');
        });

        // Page Réclamations : priorité affichée dans le tableau
        Schema::table('reclamations', function (Blueprint $table) {
            $table->string('priorite')->default('normale')->after('statut');
        });

        // Page Paramètres : une ligne par clé
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropColumn('priorite');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['titre', 'description', 'image']);
        });

        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->string('email')->nullable(false)->change();
            $table->string('mdp')->nullable(false)->change();
            $table->date('date_naissance')->nullable(false)->change();
            $table->foreignId('gerant_id')->nullable(false)->change();
        });

        Schema::table('plats', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->foreignId('administrateur_id')->nullable(false)->change();
        });
    }
};
