<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rota raiz - redireciona para login (ou dashboard se estiver autenticado)
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard - deve vir primeiro
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes/search-ajax', [ClienteController::class, 'searchAjax'])->name('clientes.search-ajax');
    
    // Serviços
    Route::resource('servicos', ServicoController::class);
    Route::post('/servicos/{servico}/update-payment-status', [ServicoController::class, 'updatePaymentStatus'])
         ->name('servicos.update-payment-status');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::resource('usuarios', UsuarioController::class);
        Route::get('/relatorios', [DashboardController::class, 'relatorios'])->name('relatorios');
    });
});