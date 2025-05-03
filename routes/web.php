<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PharmacienController;
use App\Http\Controllers\RavitaillementController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Ventes
Route::get('/ventes', [VenteController::class, 'index'])->name('ventes.index');
Route::post('/ventes/store', [VenteController::class, 'store'])->name('ventes.store');
Route::get('/ventes/get-produits', [VenteController::class, 'getProduits'])->name('ventes.getProduits');

// Produits
Route::get('/produits', [ProduitController::class, 'index'])->name('produits.index');
Route::get('/produits/create', [ProduitController::class, 'create'])->name('produits.create');
Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
Route::get('/produits/{produit}', [ProduitController::class, 'show'])->name('produits.show');
Route::get('/produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
Route::post('/produits/{id}/update', [ProduitController::class, 'update'])->name('produits.update');
Route::post('/produits/{id}/delete', [ProduitController::class, 'destroy'])->name('produits.destroy');

// Routes pour les commandes
Route::get('/commandes', [CommandeController::class, 'showCommandes'])->name('commandes.index');
Route::post('/commandes/{commandeId}/status/{status}', [CommandeController::class, 'updateStatus'])->name('commandes.updateStatus');

// Routes pour les pharmaciensuse App\Http\Controllers\PharmacienController;


    Route::get('/pharmaciens', [PharmacienController::class, 'index'])->name('pharmaciens.index');
    Route::get('/pharmaciens/create', [PharmacienController::class, 'create'])->name('pharmaciens.create');
    Route::post('/pharmaciens', [PharmacienController::class, 'store'])->name('pharmaciens.store');
    Route::get('/pharmaciens/{pharmacienId}/edit', [PharmacienController::class, 'edit'])->name('pharmaciens.edit');
    Route::put('/pharmaciens/{pharmacienId}', [PharmacienController::class, 'update'])->name('pharmaciens.update');
    Route::delete('/pharmaciens/{pharmacienId}', [PharmacienController::class, 'destroy'])->name('pharmaciens.destroy');

    Route::get('/ravitaillements', [RavitaillementController::class, 'index'])->name('ravitaillements.index');
    Route::post('/ravitaillements/store', [RavitaillementController::class, 'store'])->name('ravitaillements.store');
    Route::post('/ravitaillements/{id}/delete', [RavitaillementController::class, 'destroy'])->name('ravitaillements.destroy');
    
