<?php

use App\Http\Controllers\Admin\{EmployeController, MenuController, ParametreController,
    PromotionController, ReclamationController, StatsController};
use App\Http\Controllers\Gerant;
use Illuminate\Support\Facades\Route;

// ===== Espace gérant =====
// Le formulaire « Créer un compte » a action="#" : il envoie à /employes (même URL que la page).
Route::name('gerant.')->group(function () {
    Route::get('/supervision', [Gerant\SupervisionController::class, 'index'])->name('supervision');

    Route::get('/employes', [Gerant\EmployeController::class, 'index'])->name('employes.index');
    Route::post('/employes', [Gerant\EmployeController::class, 'store'])->name('employes.store');

    // Mêmes statistiques que l'administrateur, avec la barre de navigation du gérant
    Route::get('/statistiques', StatsController::class)->name('statistiques');

    Route::get('/reclamations', [Gerant\ReclamationController::class, 'index'])->name('reclamations.index');
    Route::post('/reclamations/{reclamation}/reponse', [Gerant\ReclamationController::class, 'repondre'])
        ->name('reclamations.repondre');
});

// ===== Espace administrateur (Humphrey) =====
// Les formulaires des pages admin ont action="#" : ils envoient à l'URL de la page elle-même.
// Les routes POST / PUT sont donc sur les mêmes URL que les pages d'ajout et de modification.
Route::prefix('admin')->name('admin.')->group(function () {

    // Les statistiques réutilisent la page du gérant, avec le layout admin et les chiffres de la base
    Route::get('/statistiques', StatsController::class)->name('statistiques');

    Route::controller(EmployeController::class)->name('employes.')->group(function () {
        Route::get('/employes', 'index')->name('index');
        Route::get('/employes/ajouter', 'create')->name('create');
        Route::post('/employes/ajouter', 'store')->name('store');
        Route::get('/employes/{employe}/modifier', 'edit')->name('edit');
        Route::put('/employes/{employe}/modifier', 'update')->name('update');
        Route::delete('/employes/{employe}', 'destroy')->name('destroy');
    });

    Route::controller(MenuController::class)->name('menus.')->group(function () {
        Route::get('/menus', 'index')->name('index');
        Route::get('/menus/ajouter', 'create')->name('create');
        Route::post('/menus/ajouter', 'store')->name('store');
        Route::get('/menus/{plat}/modifier', 'edit')->name('edit');
        Route::put('/menus/{plat}/modifier', 'update')->name('update');
        Route::delete('/menus/{plat}', 'destroy')->name('destroy');
    });

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');

    Route::get('/parametres', [ParametreController::class, 'edit'])->name('parametres.edit');
    Route::post('/parametres', [ParametreController::class, 'update'])->name('parametres.update');

    Route::get('/reclamations', [ReclamationController::class, 'index'])->name('reclamations.index');
    Route::put('/reclamations/{reclamation}', [ReclamationController::class, 'update'])->name('reclamations.update');
});
