<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Espace employé : les PAGES  →  URL : /employe/...
|--------------------------------------------------------------------------
| Route::view affiche la vue sans passer par un contrôleur : les données
| arrivent ensuite par l'API (/api/employe/... dans routes/api.php),
| appelée par le JavaScript de chaque page.
| 'auth' : il faut être connecté, sinon redirection vers la page de connexion.
*/
Route::middleware('auth')->prefix('employe')->name('employe.')->group(function () {
    Route::view('/commandes', 'employe.commandes')->name('commandes');
    Route::view('/menu', 'employe.menu')->name('menu');
    Route::view('/reclamations', 'employe.reclamations')->name('reclamations');
    Route::view('/statistiques', 'employe.statistiques')->name('statistiques');
});

Route::get('', function () {
    $user = \App\Models\User::where('email', '')->firstOrFail();

    auth()->login($user);                  // ouvre la session comme si on s'était connecté
    return redirect('/employe/commandes'); // puis envoie sur la première page employé
});
