<?php

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
