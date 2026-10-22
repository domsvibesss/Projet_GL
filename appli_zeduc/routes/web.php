<?php
 
use App\Http\Controllers\Admin\{EmployeeController, JeuController, MenuController,
    PromotionController, ReclamationController, ReponseController, StatsController};
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
 
Route::get('/', fn () => view('home'))->name('home');
 
/*
|--------------------------------------------------------------------------
| Authentification (un seul formulaire pour les 4 types de comptes)
|--------------------------------------------------------------------------
| Noms exigés par AuthController : home, login, password.*
*/
Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest:admin,gerant,employe,etudiant')->group(function () {
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register');
 
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login');
 
        Route::get('/forgot-password', 'showForgotPassword')->name('password.request');
        Route::post('/forgot-password', 'sendResetLink')->name('password.email');
        Route::get('/reset-password/{token}', 'showResetPassword')->name('password.reset');
        Route::post('/reset-password', 'resetPassword')->name('password.update');
    });
 
    Route::post('/logout', 'logout')->name('logout');
});
 
/*
|--------------------------------------------------------------------------
| Administration  (URL /admin/..., noms admin.*)
|--------------------------------------------------------------------------
| Noms exigés par les contrôleurs : admin.stats, admin.menu.index,
| admin.employees.index, admin.jeux.index, admin.promotions.index,
| admin.reclamations.show, admin.reponses.show ...
*/
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
 
    // Accueil admin = statistiques (AuthController redirige vers admin.stats)
    Route::get('/', [StatsController::class, 'index'])->name('stats');
    Route::get('stats/live', [StatsController::class, 'live'])->name('stats.live');
 
    // Actions spéciales (avant les resources)
    Route::patch('menu/{menu}/restore', [MenuController::class, 'restore'])->withTrashed()->name('menu.restore');
    Route::delete('menu/{menu}/force-delete', [MenuController::class, 'forceDelete'])->withTrashed()->name('menu.force-delete');
    Route::patch('menu/{menu}/toggle-availability', [MenuController::class, 'toggleAvailability'])->name('menu.toggle-availability');
    Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
    Route::patch('jeux/{jeu}/terminer', [JeuController::class, 'terminer'])->name('jeux.terminer');
    Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');
    Route::post('reclamations/{reclamation}/reponses', [ReponseController::class, 'store'])->name('reclamations.reponses.store');
 
    // CRUD : seulement les méthodes qui existent dans chaque contrôleur
    Route::resource('menu', MenuController::class)->except('show');
    Route::resource('employees', EmployeeController::class)->except('show');
    Route::resource('promotions', PromotionController::class)->except('show');
    Route::resource('jeux', JeuController::class)->parameters(['jeux' => 'jeu']);
    Route::resource('reclamations', ReclamationController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::resource('reponses', ReponseController::class)->only(['index', 'show', 'edit', 'update', 'destroy'])
        ->parameters(['reponses' => 'reponse']);
});