<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\AtendimentoController; 
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\LogController;

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::get('/clientes/search-ajax', [ClienteController::class, 'searchAjax'])->name('clientes.search-ajax');
    Route::resource('clientes', ClienteController::class);
    
    // Serviços
    Route::resource('servicos', ServicoController::class);
    Route::post('/servicos/{servico}/update-payment-status', [ServicoController::class, 'updatePaymentStatus'])->name('servicos.update-payment-status');
    Route::post('/servicos/{servico}/marcar-pago', [ServicoController::class, 'marcarPago'])->name('servicos.marcar-pago');
    // Rotas de exportação 
    Route::get('/servicos/export/excel', [ServicoController::class, 'exportExcel'])->name('servicos.export.excel');
    Route::get('/servicos/export/pdf', [ServicoController::class, 'exportPdf'])->name('servicos.export.pdf');
    
    // Parcelas
    Route::post('/parcelas/{parcela}/marcar-paga', [ParcelaController::class, 'marcarPaga'])->name('parcelas.marcar-paga');
    Route::post('/parcelas/{parcela}/marcar-pendente', [ParcelaController::class, 'marcarPendente'])->name('parcelas.marcar-pendente');
    Route::put('/parcelas/{parcela}', [ParcelaController::class, 'atualizarStatus'])->name('parcelas.update');
    Route::delete('/parcelas/{parcela}', [ParcelaController::class, 'destroy'])->name('parcelas.destroy');

    // Atendimentos (Agenda)
    Route::resource('atendimentos', AtendimentoController::class);
    Route::get('/atendimentos/events', [AtendimentoController::class, 'getEvents'])->name('atendimentos.events');
    Route::get('/atendimentos/{atendimento}/edit', [AtendimentoController::class, 'edit'])->name('atendimentos.edit');
    Route::get('/api/atendimentos-events', [AtendimentoController::class, 'getEvents'])->name('api.atendimentos.events');
});

// Rotas Admin 
Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::resource('usuarios', UsuarioController::class);
    Route::patch('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    Route::delete('/usuarios/{id}/force-delete', [UsuarioController::class, 'forceDelete'])->name('usuarios.force-delete');
    
    // Rotas de Relatórios 
    Route::get('/relatorios', [DashboardController::class, 'relatorios'])->name('relatorios.index');
    Route::post('/relatorios/dados', [DashboardController::class, 'relatoriosDados'])->name('relatorios.dados');
    Route::post('/relatorios/exportar', [DashboardController::class, 'exportarRelatorio'])->name('relatorios.exportar');

    // Rotas de Logs do Sistema
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::get('/logs/export', [LogController::class, 'export'])->name('logs.export');
});