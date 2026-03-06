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
use App\Http\Controllers\OrcamentoController;
use Illuminate\Support\Facades\Schedule;

// --- Autenticação ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- CLIENTES ---
    Route::get('/clientes/search-ajax', [ClienteController::class, 'searchAjax'])->name('clientes.search-ajax');
    Route::middleware(['permissao:clientes.view'])->group(function () {
        Route::resource('clientes', ClienteController::class);
    });

    // --- SERVIÇOS (Ordem é vital aqui!) ---
    
    // 1. Primeiro as rotas de CRIAÇÃO (Antes do {servico})
    Route::middleware(['permissao:servicos.create'])->group(function () {
        Route::get('/servicos/create', [ServicoController::class, 'create'])->name('servicos.create');
        Route::post('/servicos', [ServicoController::class, 'store'])->name('servicos.store');
        Route::post('/servicos/{servico}/anexos', [ServicoController::class, 'storeAnexo'])->name('servicos.anexos.store');
    });

    // 2. Rotas de Exportação e Listagem
    Route::middleware(['permissao:servicos.view'])->group(function () {
        Route::get('/servicos', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/servicos/export/excel', [ServicoController::class, 'exportExcel'])->name('servicos.export.excel');
        Route::get('/servicos/export/pdf', [ServicoController::class, 'exportPdf'])->name('servicos.export.pdf');
    });

    // 3. Rotas de EDIÇÃO e Ações (Antes do Show)
    Route::middleware(['permissao:servicos.edit'])->group(function () {
        Route::get('/servicos/{servico}/edit', [ServicoController::class, 'edit'])->name('servicos.edit');
        Route::put('/servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::post('/servicos/{servico}/update-payment-status', [ServicoController::class, 'updatePaymentStatus'])->name('servicos.update-payment-status');
        Route::post('/servicos/{servico}/marcar-pago', [ServicoController::class, 'marcarPago'])->name('servicos.marcar-pago');
        Route::post('/servicos/{servico}/pagar', [ServicoController::class, 'pagar'])->name('servicos.pagar');
        Route::put('/servicos/{servico}/parcelas/atualizar-valores', [ServicoController::class, 'atualizarValoresParcelas'])->name('servicos.parcelas.atualizar-valores');
    });

    // 4. Visualização e Download (Show por último)
    Route::middleware(['permissao:servicos.view'])->group(function () {
        Route::get('/servicos/{servico}', [ServicoController::class, 'show'])->name('servicos.show');
        Route::get('servicos/{servico}/anexos/{anexo}/download', [ServicoController::class, 'downloadAnexo'])->name('servicos.anexos.download');
    });

    // 5. Exclusão
    Route::middleware(['permissao:servicos.delete'])->group(function () {
        Route::delete('/servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');
        Route::delete('servicos/{servico}/anexos/{anexo}', [ServicoController::class, 'destroyAnexo'])->name('servicos.anexos.destroy');
    });


    // --- PARCELAS ---
    Route::middleware(['permissao:parcelas.view'])->group(function () {
        Route::get('/parcelas/{parcela}/comprovante', [ParcelaController::class, 'comprovante'])->name('parcelas.comprovante');
    });

    Route::middleware(['permissao:parcelas.edit'])->group(function () {
        Route::post('/parcelas/{parcela}/marcar-paga', [ParcelaController::class, 'marcarPaga'])->name('parcelas.marcar-paga');
        Route::post('/parcelas/{parcela}/marcar-pendente', [ParcelaController::class, 'marcarPendente'])->name('parcelas.marcar-pendente');
        Route::put('/parcelas/{parcela}', [ParcelaController::class, 'atualizarStatus'])->name('parcelas.update');
    });

    Route::middleware(['permissao:parcelas.delete'])->group(function () {
        Route::delete('/parcelas/{parcela}', [ParcelaController::class, 'destroy'])->name('parcelas.destroy');
    });


    // --- ATENDIMENTOS (Agenda) ---
    Route::get('/atendimentos/events', [AtendimentoController::class, 'getEvents'])->name('atendimentos.events');
    Route::get('/api/atendimentos-events', [AtendimentoController::class, 'getEvents'])->name('api.atendimentos.events');
    Route::get('/atendimentos/{atendimento}/edit', [AtendimentoController::class, 'edit'])->name('atendimentos.edit');
    Route::resource('atendimentos', AtendimentoController::class);


    // --- ORÇAMENTOS ---
    
    // 1. Criação (Sempre primeiro!)
    Route::middleware(['permissao:orcamentos.create'])->group(function () {
        Route::get('/orcamentos/create', [OrcamentoController::class, 'create'])->name('orcamentos.create');
        Route::post('/orcamentos', [OrcamentoController::class, 'store'])->name('orcamentos.store');
    });

    // 2. Listagem e PDF
    Route::middleware(['permissao:orcamentos.view'])->group(function () {
        Route::get('/orcamentos', [OrcamentoController::class, 'index'])->name('orcamentos.index');
        Route::get('orcamentos/{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('orcamentos.pdf');
    });

    // 3. Edição e Ações
    Route::middleware(['permissao:orcamentos.edit'])->group(function () {
        Route::get('/orcamentos/{orcamento}/edit', [OrcamentoController::class, 'edit'])->name('orcamentos.edit');
        Route::put('/orcamentos/{orcamento}', [OrcamentoController::class, 'update'])->name('orcamentos.update');
        Route::patch('orcamentos/{orcamento}/aprovar', [OrcamentoController::class, 'aprovar'])->name('orcamentos.aprovar');
        Route::patch('orcamentos/{orcamento}/cancelar', [OrcamentoController::class, 'cancelar'])->name('orcamentos.cancelar');
    });

    // 4. Detalhes (Show depois do edit e create)
    Route::middleware(['permissao:orcamentos.view'])->group(function () {
        Route::get('/orcamentos/{orcamento}', [OrcamentoController::class, 'show'])->name('orcamentos.show');
    });

    // 5. Exclusão
    Route::middleware(['permissao:orcamentos.delete'])->group(function () {
        Route::delete('/orcamentos/{orcamento}', [OrcamentoController::class, 'destroy'])->name('orcamentos.destroy');
    });
});

// Rotas Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    Route::middleware(['permissao:usuarios.manage'])->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        Route::patch('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
        Route::delete('/usuarios/{id}/force-delete', [UsuarioController::class, 'forceDelete'])->name('usuarios.force-delete');
    });

    Route::middleware(['permissao:relatorios.view'])->group(function () {
        Route::get('/relatorios', [DashboardController::class, 'relatorios'])->name('relatorios.index');
        Route::post('/relatorios/dados', [DashboardController::class, 'relatoriosDados'])->name('relatorios.dados');
        Route::post('/relatorios/exportar', [DashboardController::class, 'exportarRelatorio'])->name('relatorios.exportar');
    });

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::get('/logs/export', [LogController::class, 'export'])->name('logs.export');
});

Schedule::command('orcamentos:verificar-vencidos')->dailyAt('09:00');