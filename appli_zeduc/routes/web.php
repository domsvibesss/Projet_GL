<?php

use App\Http\Controllers\Admin\{EmployeController, MenuController, ParametreController,
    PromotionController, ReclamationController, StatsController};
use Illuminate\Support\Facades\Route;

Route::get('/statistiques', function () {
    return view('statistiques');
});
Route::get('/supervision', function () {
    return view('supervision');
});
Route::get('/employes', function () {
    return view('employes');
});
Route::get('/reclamations', function () {
    return view('reclamations');
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
