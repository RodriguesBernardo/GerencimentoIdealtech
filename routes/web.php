<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ParcelaController;
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
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes/search-ajax', [ClienteController::class, 'searchAjax'])->name('clientes.search-ajax');
    
    // Serviços
    Route::resource('servicos', ServicoController::class);
    Route::post('/servicos/{servico}/update-payment-status', [ServicoController::class, 'updatePaymentStatus'])->name('servicos.update-payment-status');
    Route::post('/servicos/{servico}/marcar-pago', [ServicoController::class, 'marcarPago'])->name('servicos.marcar-pago');

    // Parcelas
    Route::post('/parcelas/{parcela}/marcar-paga', [ParcelaController::class, 'marcarPaga'])->name('parcelas.marcar-paga');
    Route::post('/parcelas/{parcela}/marcar-pendente', [ParcelaController::class, 'marcarPendente'])->name('parcelas.marcar-pendente');
    Route::put('/parcelas/{parcela}', [ParcelaController::class, 'atualizarStatus'])->name('parcelas.update');
    Route::delete('/parcelas/{parcela}', [ParcelaController::class, 'destroy'])->name('parcelas.destroy');
});

// Rotas Admin 
Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::resource('usuarios', UsuarioController::class);
    Route::patch('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    Route::delete('/usuarios/{id}/force-delete', [UsuarioController::class, 'forceDelete'])->name('usuarios.force-delete');
    
    // Novas rotas de relatórios
    Route::get('/relatorios', [DashboardController::class, 'relatorios'])->name('relatorios');
    Route::post('/relatorios/dados', [DashboardController::class, 'relatoriosDados'])->name('relatorios.dados');
    Route::post('/relatorios/exportar', [DashboardController::class, 'exportarRelatorio'])->name('relatorios.exportar');
});