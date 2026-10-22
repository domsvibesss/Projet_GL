<?php

use App\Http\Controllers\Employe\EmployeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Espace employé  →  URL : /api/employe/...
|--------------------------------------------------------------------------
| Toutes les routes passent par EmployeController, qui vérifie lui-même que
| l'utilisateur connecté a le rôle « employe » et un compte « actif ».
| Le middleware 'auth:sanctum' ci-dessous est à adapter au guard utilisé par
| l'équipe (ex. 'auth' si l'authentification est par session / Breeze).
*/
Route::prefix('employe')
    ->name('employe.')
    ->middleware('auth:sanctum')
    ->controller(EmployeController::class)
    ->group(function () {

        // ----- Commandes -------------------------------------------------
        Route::get('commandes', 'commandes')->name('commandes.index');
        Route::get('commandes/{id}', 'commande')->whereNumber('id')->name('commandes.show');
        Route::patch('commandes/{id}/en-preparation', 'enPreparation')->whereNumber('id')->name('commandes.en-preparation');
        Route::patch('commandes/{id}/prete', 'prete')->whereNumber('id')->name('commandes.prete');
        Route::patch('commandes/{id}/livree', 'livree')->whereNumber('id')->name('commandes.livree');
        Route::patch('commandes/{id}/rejeter', 'rejeter')->whereNumber('id')->name('commandes.rejeter');

        // ----- Menu ------------------------------------------------------
        Route::get('plats', 'plats')->name('plats.index');
        Route::patch('plats/{id}/quantite', 'mettreAJourStock')->whereNumber('id')->name('plats.quantite');
        Route::patch('plats/{id}/epuise', 'marquerEpuise')->whereNumber('id')->name('plats.epuise');
        Route::get('plat-du-jour', 'platDuJour')->name('plat-du-jour.show');
        Route::patch('plats/{id}/plat-du-jour', 'definirPlatDuJour')->whereNumber('id')->name('plat-du-jour.definir');
        Route::delete('plat-du-jour', 'retirerPlatDuJour')->name('plat-du-jour.retirer');

        // ----- Réclamations ----------------------------------------------
        Route::get('reclamations', 'reclamations')->name('reclamations.index');
        Route::get('reclamations/{id}', 'reclamation')->whereNumber('id')->name('reclamations.show');
        Route::post('reclamations/{id}/reponses', 'repondreReclamation')->whereNumber('id')->name('reclamations.repondre');

        // ----- Statistiques ----------------------------------------------
        Route::get('statistiques/hebdomadaire', 'statistiquesHebdomadaires')->name('statistiques.hebdomadaire');
        Route::get('statistiques/journaliere', 'statistiquesJournalieres')->name('statistiques.journaliere');
    });