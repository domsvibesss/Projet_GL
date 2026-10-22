<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiamMiamController;



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
// Les statistiques sont les mêmes que celles du gérant : on réutilise la page, avec le layout admin
Route::get('/admin/statistiques', function() {
    return view('statistiques', ['layout' => 'layouts.admin']);
});

Route::get('/admin/employes', function () {
    return view('admin.employes');
});    
Route::get('/admin/employes/ajouter', function () {
    return view('admin.employes-ajout');
});
Route::get('/admin/menus', function () {
    return view('admin.menus');
});
Route::get('/admin/menus/ajouter', function () {
    return view('admin.menus-ajout');
});
Route::get('/admin/promotions', function () {
    return view('admin.promotions');
});
Route::get('/admin/parametres', function () {
    return view('admin.parametres');
});

Route::view('/', 'accueil')->name('accueil');
Route::view('/menu', 'menu')->name('menu');
Route::view('/plat-du-jour', 'plat-du-jour')->name('plat-du-jour');
Route::view('/panier', 'panier')->name('panier');
Route::view('/finalisation', 'finalisation')->name('finalisation');
Route::view('/confirmation', 'confirmation')->name('confirmation');
Route::view('/historique', 'historique')->name('historique');
Route::view('/fidelite', 'fidelite')->name('fidelite');
Route::view('/reclamations', 'reclamations')->name('reclamations');
Route::view('/connexion', 'connexion')->name('connexion');
Route::view('/inscription', 'inscription')->name('inscription');
Route::view('/mot-de-passe-oublie', 'mot-de-passe-oublie')->name('mot-de-passe-oublie');


Route::name('miammiam.')->group(function () {
    Route::get('/', [MiamMiamController::class, 'accueil'])->name('accueil');
    Route::get('/menu', [MiamMiamController::class, 'menu'])->name('menu');
    Route::get('/plat-du-jour', [MiamMiamController::class, 'platDuJour'])->name('plat-du-jour');
    Route::get('/panier', [MiamMiamController::class, 'panier'])->name('panier');
    Route::get('/finalisation', [MiamMiamController::class, 'finalisation'])->name('finalisation');
    Route::get('/confirmation', [MiamMiamController::class, 'confirmation'])->name('confirmation');
    Route::get('/historique', [MiamMiamController::class, 'historique'])->name('historique');
    Route::get('/fidelite', [MiamMiamController::class, 'fidelite'])->name('fidelite');
    Route::get('/reclamations', [MiamMiamController::class, 'reclamations'])->name('reclamations');
    Route::get('/connexion', [MiamMiamController::class, 'connexion'])->name('connexion');
    Route::get('/inscription', [MiamMiamController::class, 'inscription'])->name('inscription');
    Route::get('/mot-de-passe-oublie', [MiamMiamController::class, 'motDePasseOublie'])->name('mot-de-passe-oublie');
});