<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PharmacienController;
use App\Http\Controllers\RavitaillementController;
use App\Http\Controllers\BilanController;
use App\Http\Controllers\DestockageController;

// Routes publiques
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Ventes
Route::prefix('ventes')->name('ventes.')->group(function () {
    Route::get('/', [VenteController::class, 'index'])->name('index');
    Route::post('/store', [VenteController::class, 'store'])->name('store');
    Route::get('/get-produits', [VenteController::class, 'getProduits'])->name('getProduits');
});

// Produits
Route::prefix('produits')->name('produits.')->group(function () {
    Route::get('/', [ProduitController::class, 'index'])->name('index');
    Route::get('/create', [ProduitController::class, 'create'])->name('create');
    Route::post('/', [ProduitController::class, 'store'])->name('store');
    Route::get('/{produit}', [ProduitController::class, 'show'])->name('show');
    Route::get('/{produit}/edit', [ProduitController::class, 'edit'])->name('edit');
    Route::post('/{id}/update', [ProduitController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProduitController::class, 'destroy'])->name('destroy');
    
    // Routes pour la gestion des produits dans le ravitaillement
    Route::post('/add', [RavitaillementController::class, 'addProduit'])->name('add');
    Route::get('/list', [RavitaillementController::class, 'getProduits'])->name('list');
});

// Commandes
Route::prefix('commandes')->name('commandes.')->group(function () {
    Route::get('/', [CommandeController::class, 'showCommandes'])->name('index');
    Route::post('/{commandeId}/status/{status}', [CommandeController::class, 'updateStatus'])->name('updateStatus');
});

// Pharmaciens
Route::prefix('pharmaciens')->name('pharmaciens.')->group(function () {
    Route::get('/', [PharmacienController::class, 'index'])->name('index');
    Route::get('/create', [PharmacienController::class, 'create'])->name('create');
    Route::post('/', [PharmacienController::class, 'store'])->name('store');
    Route::get('/{pharmacienId}/edit', [PharmacienController::class, 'edit'])->name('edit');
    Route::put('/{pharmacienId}', [PharmacienController::class, 'update'])->name('update');
    Route::delete('/{pharmacienId}', [PharmacienController::class, 'destroy'])->name('destroy');
});

// Ravitaillements
Route::prefix('ravitaillements')->name('ravitaillements.')->group(function () {
    // Routes d'importation
    Route::get('/template/download', [RavitaillementController::class, 'downloadTemplate'])->name('template.download');
    Route::post('/import/preview', [RavitaillementController::class, 'previewImport'])->name('import.preview');
    Route::post('/import/process', [RavitaillementController::class, 'processImport'])->name('import.process');
    Route::get('/import', [RavitaillementController::class, 'showImport'])->name('import');
    
    // Routes principales
    Route::get('/', [RavitaillementController::class, 'index'])->name('index');
    Route::post('/store', [RavitaillementController::class, 'store'])->name('store');
    Route::get('/{id}/preview', [RavitaillementController::class, 'preview'])->name('preview');
    Route::delete('/{id}', [RavitaillementController::class, 'destroy'])->name('destroy');
});

// Bilans
Route::prefix('bilans')->name('bilans.')->group(function () {
    Route::get('/', [BilanController::class, 'index'])->name('index');
    Route::post('/generer', [BilanController::class, 'genererBilan'])->name('generer');
});

// Déstockage
Route::prefix('destockage')->name('destockage.')->group(function () {
    Route::get('/', [DestockageController::class, 'index'])->name('index');
    Route::post('/destocker', [DestockageController::class, 'destocker'])->name('destocker');
});
