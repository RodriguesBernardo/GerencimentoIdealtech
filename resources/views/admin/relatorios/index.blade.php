@extends('layouts.app')

@section('title', 'Relatórios Avançados')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Relatórios Avançados</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtros Avançados -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-sliders-h me-2"></i>Configurações do Relatório
            </h5>
        </div>
        <div class="card-body">
            <form id="filtrosForm" class="row g-3">
                @csrf
                
                <!-- Período -->
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                           value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                           value="{{ date('Y-m-t') }}">
                </div>
                
                <!-- Tipo de Relatório -->
                <div class="col-md-3">
                    <label for="tipo_relatorio" class="form-label">Tipo de Relatório</label>
                    <select class="form-control" id="tipo_relatorio" name="tipo_relatorio">
                        <option value="financeiro">Financeiro</option>
                        <option value="servicos">Serviços</option>
                        <option value="clientes">Clientes</option>
                        <option value="parcelas">Parcelas</option>
                    </select>
                </div>
                
                <!-- Tipo de Gráfico -->
                <div class="col-md-3">
                    <label for="tipo_grafico" class="form-label">Tipo de Gráfico</label>
                    <select class="form-control" id="tipo_grafico" name="tipo_grafico">
                        <option value="bar">Barras</option>
                        <option value="line">Linhas</option>
                        <option value="pie">Pizza</option>
                        <option value="doughnut">Rosca</option>
                        <option value="radar">Radar</option>
                    </select>
                </div>
                
                <!-- Agrupamento -->
                <div class="col-md-3">
                    <label for="agrupamento" class="form-label">Agrupamento</label>
                    <select class="form-control" id="agrupamento" name="agrupamento">
                        <option value="mes">Mensal</option>
                        <option value="semana">Semanal</option>
                        <option value="dia">Diário</option>
                    </select>
                </div>
                
                <!-- Filtros Adicionais -->
                <div class="col-md-3">
                    <label for="status_pagamento" class="form-label">Status Pagamento</label>
                    <select class="form-control" id="status_pagamento" name="status_pagamento">
                        <option value="">Todos</option>
                        <option value="pago">Pago</option>
                        <option value="pendente">Pendente</option>
                        <option value="nao_pago">Não Pago</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="tipo_pagamento" class="form-label">Tipo Pagamento</label>
                    <select class="form-control" id="tipo_pagamento" name="tipo_pagamento">
                        <option value="">Todos</option>
                        <option value="avista">À Vista</option>
                        <option value="parcelado">Parcelado</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="ordenacao" class="form-label">Ordenação</label>
                    <select class="form-control" id="ordenacao" name="ordenacao">
                        <option value="valor">Por Valor</option>
                        <option value="data">Por Data</option>
                        <option value="quantidade">Por Quantidade</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i>Atualizar Relatório
                        </button>
                        <button type="button" id="btnReset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i>Redefinir
                        </button>
                        <button type="button" id="exportarPdf" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                        </button>
                        <button type="button" id="exportarExcel" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Exportar Excel
                        </button>
                        <button type="button" id="salvarConfig" class="btn btn-outline-info">
                            <i class="fas fa-save me-1"></i>Salvar Configuração
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo Dinâmicos -->
    <div class="row mb-4" id="cardsResumo">
        <!-- Os cards serão carregados dinamicamente via JavaScript -->
    </div>

    <!-- Gráficos Principais -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <span id="tituloGraficoPrincipal">Gráfico Principal</span>
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="alternarVistaGrafico('principal')">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarGrafico('principal')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="graficoPrincipal" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Secundários -->
    <div class="row mb-4" id="graficosSecundarios">
        <!-- Gráficos secundários serão carregados dinamicamente -->
    </div>

    <!-- Tabelas de Dados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>
                        <span id="tituloTabela">Dados Detalhados</span>
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleDetalhes">
                        <label class="form-check-label" for="toggleDetalhes">Mostrar Detalhes</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabelaDetalhes">
                            <thead id="cabecalhoTabela">
                                <!-- Cabeçalho dinâmico -->
                            </thead>
                            <tbody id="corpoTabelaDetalhes">
                                <!-- Dados dinâmicos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Salvar Configuração -->
<div class="modal fade" id="modalSalvarConfig" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salvar Configuração</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nomeConfiguracao" class="form-label">Nome da Configuração</label>
                    <input type="text" class="form-control" id="nomeConfiguracao" placeholder="Ex: Relatório Financeiro Mensal">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarConfiguracao()">Salvar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-card {
        border-left: 4px solid;
    }
    .stat-card.success { border-left-color: #10B981; }
    .stat-card.warning { border-left-color: #F59E0B; }
    .stat-card.danger { border-left-color: #EF4444; }
    .stat-card.info { border-left-color: #3B82F6; }
    
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .btn-group .btn {
        border-radius: 6px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    let graficos = {};
    let configuracaoAtual = {};
    let dadosAtuais = {};

    document.addEventListener('DOMContentLoaded', function() {
        inicializarRelatorios();
        
        // Event Listeners
        document.getElementById('filtrosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            carregarRelatorios();
        });
        
        document.getElementById('btnReset').addEventListener('click', function() {
            document.getElementById('filtrosForm').reset();
            carregarRelatorios();
        });
        
        document.getElementById('salvarConfig').addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('modalSalvarConfig')).show();
        });
        
        // Alterações em tempo real
        ['tipo_relatorio', 'tipo_grafico', 'agrupamento'].forEach(id => {
            document.getElementById(id).addEventListener('change', function() {
                carregarRelatorios();
            });
        });
    });

    function inicializarRelatorios() {
        carregarRelatorios();
    }

    function carregarRelatorios() {
        const form = document.getElementById('filtrosForm');
        form.classList.add('loading');
        
        const formData = new FormData(form);
        
        fetch('{{ route("admin.relatorios.dados") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            dadosAtuais = data;
            configuracaoAtual = data.config;
            atualizarInterface(data);
            form.classList.remove('loading');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar relatórios');
            form.classList.remove('loading');
        });
    }

    function atualizarInterface(data) {
        atualizarCardsResumo(data.resumo);
        atualizarGraficoPrincipal(data.graficos, data.config);
        atualizarGraficosSecundarios(data.graficos, data.config);
        atualizarTabelas(data.tabelas);
        atualizarTitulos(data.config.tipo_relatorio);
    }

    function atualizarCardsResumo(resumo) {
        const container = document.getElementById('cardsResumo');
        const tipoRelatorio = configuracaoAtual.tipo_relatorio;
        
        let cardsHTML = '';
        
        if (tipoRelatorio === 'financeiro') {
            cardsHTML = `
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Total Pago</h6>
                                    <h4 class="mb-0">${formatarMoeda(resumo.total_pago)}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Total Pendente</h6>
                                    <h4 class="mb-0">${formatarMoeda(resumo.total_pendente)}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Total Não Pago</h6>
                                    <h4 class="mb-0">${formatarMoeda(resumo.total_nao_pago)}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Total Serviços</h6>
                                    <h4 class="mb-0">${resumo.total_servicos}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tools fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Ticket Médio</h6>
                                    <h4 class="mb-0">${formatarMoeda(resumo.ticket_medio || 0)}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card stat-card info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-muted mb-1">Valor Total</h6>
                                    <h4 class="mb-0">${formatarMoeda(resumo.valor_total || 0)}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-dollar-sign fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        // Adicione outros casos para diferentes tipos de relatório...
        
        container.innerHTML = cardsHTML;
    }

    function atualizarGraficoPrincipal(graficos, config) {
        const ctx = document.getElementById('graficoPrincipal').getContext('2d');
        
        // Destruir gráfico anterior se existir
        if (graficos.principal) {
            graficos.principal.destroy();
        }
        
        const graficoKey = Object.keys(graficos)[0]; // Primeiro gráfico disponível
        const dados = graficos[graficoKey];
        
        let datasetConfig = {};
        
        switch (config.tipo_grafico) {
            case 'bar':
                datasetConfig = {
                    type: 'bar',
                    data: dados.valores || dados.quantidades,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3B82F6',
                    borderWidth: 2
                };
                break;
            case 'line':
                datasetConfig = {
                    type: 'line',
                    data: dados.valores || dados.quantidades,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                };
                break;
            case 'pie':
            case 'doughnut':
                datasetConfig = {
                    type: config.tipo_grafico,
                    data: dados.valores || dados.quantidades,
                    backgroundColor: [
                        '#10B981', '#F59E0B', '#EF4444', '#3B82F6', 
                        '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                };
                break;
        }
        
        graficos.principal = new Chart(ctx, {
            data: {
                labels: dados.labels,
                datasets: [{
                    label: dados.valores ? 'Valores (R$)' : 'Quantidades',
                    ...datasetConfig
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== undefined) {
                                    if (dados.valores) {
                                        label += formatarMoeda(context.parsed.y);
                                    } else {
                                        label += context.parsed.y;
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: config.tipo_grafico !== 'pie' && config.tipo_grafico !== 'doughnut' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return dados.valores ? 'R$ ' + value.toLocaleString('pt-BR') : value;
                            }
                        }
                    }
                } : {}
            }
        });
    }

    function atualizarGraficosSecundarios(graficos, config) {
        const container = document.getElementById('graficosSecundarios');
        let html = '';
        
        // Remover o primeiro gráfico (já usado como principal)
        const graficosSecundarios = Object.keys(graficos).slice(1);
        
        graficosSecundarios.forEach((key, index) => {
            if (index < 2) { // Mostrar até 2 gráficos secundários
                html += `
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">${formatarTituloGrafico(key)}</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="graficoSecundario${index}" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        
        container.innerHTML = html;
        
        // Inicializar gráficos secundários
        graficosSecundarios.forEach((key, index) => {
            if (index < 2) {
                criarGraficoSecundario(`graficoSecundario${index}`, graficos[key], config);
            }
        });
    }

    function criarGraficoSecundario(canvasId, dados, config) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dados.labels,
                datasets: [{
                    label: 'Valores',
                    data: dados.valores || dados.quantidades,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: '#8B5CF6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function atualizarTabelas(tabelas) {
        const cabecalho = document.getElementById('cabecalhoTabela');
        const corpo = document.getElementById('corpoTabelaDetalhes');
        
        // Usar a primeira tabela disponível
        const primeiraTabela = Object.values(tabelas)[0];
        
        if (primeiraTabela && primeiraTabela.length > 0) {
            // Criar cabeçalho baseado nas chaves do primeiro item
            const chaves = Object.keys(primeiraTabela[0]);
            cabecalho.innerHTML = `<tr>${chaves.map(chave => 
                `<th>${formatarTituloColuna(chave)}</th>`
            ).join('')}</tr>`;
            
            // Criar linhas
            corpo.innerHTML = primeiraTabela.map(item => 
                `<tr>${chaves.map(chave => 
                    `<td>${formatarValorTabela(item[chave], chave)}</td>`
                ).join('')}</tr>`
            ).join('');
        } else {
            cabecalho.innerHTML = '<tr><th colspan="6" class="text-center">Nenhum dado encontrado</th></tr>';
            corpo.innerHTML = '';
        }
    }

    function atualizarTitulos(tipoRelatorio) {
        const titulos = {
            'financeiro': 'Análise Financeira',
            'servicos': 'Relatório de Serviços',
            'clientes': 'Análise de Clientes',
            'parcelas': 'Relatório de Parcelas'
        };
        
        document.getElementById('tituloGraficoPrincipal').textContent = 
            titulos[tipoRelatorio] || 'Relatório';
        document.getElementById('tituloTabela').textContent = 
            `Dados Detalhados - ${titulos[tipoRelatorio] || 'Relatório'}`;
    }

    // Funções auxiliares
    function formatarMoeda(valor) {
        return 'R$ ' + parseFloat(valor || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatarTituloGrafico(key) {
        const titulos = {
            'status_pagamento': 'Status de Pagamento',
            'evolucao_mensal': 'Evolução Mensal',
            'comparativo_tipos': 'Tipos de Pagamento',
            'servicos_mensal': 'Serviços por Mês',
            'categorias_servicos': 'Categorias de Serviços'
        };
        return titulos[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function formatarTituloColuna(chave) {
        const titulos = {
            'nome': 'Nome',
            'total_servicos': 'Total Serviços',
            'valor_total': 'Valor Total',
            'telefone': 'Telefone',
            'data_servico': 'Data Serviço',
            'cliente_nome': 'Cliente',
            'servico_nome': 'Serviço',
            'status_pagamento': 'Status'
        };
        return titulos[chave] || chave.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function formatarValorTabela(valor, chave) {
        if (chave.includes('valor') || chave.includes('total') && !chave.includes('servicos')) {
            return formatarMoeda(valor);
        }
        if (chave.includes('data')) {
            return valor ? new Date(valor).toLocaleDateString('pt-BR') : '-';
        }
        return valor || '-';
    }

    function alternarVistaGrafico(tipo) {
        // Alternar entre diferentes visualizações do gráfico
        const select = document.getElementById('tipo_grafico');
        const opcoes = ['bar', 'line', 'pie', 'doughnut'];
        const indexAtual = opcoes.indexOf(select.value);
        select.value = opcoes[(indexAtual + 1) % opcoes.length];
        carregarRelatorios();
    }

    function exportarGrafico(tipo) {
        // Implementar exportação de gráfico
        alert('Funcionalidade de exportação de gráfico em desenvolvimento');
    }

    function salvarConfiguracao() {
        const nome = document.getElementById('nomeConfiguracao').value;
        if (!nome) {
            alert('Por favor, informe um nome para a configuração');
            return;
        }
        
        // Salvar configuração no localStorage
        const configs = JSON.parse(localStorage.getItem('relatorioConfigs') || '{}');
        configs[nome] = {
            filtros: Object.fromEntries(new FormData(document.getElementById('filtrosForm'))),
            data: new Date().toISOString()
        };
        
        localStorage.setItem('relatorioConfigs', JSON.stringify(configs));
        bootstrap.Modal.getInstance(document.getElementById('modalSalvarConfig')).hide();
        alert('Configuração salva com sucesso!');
    }

    function exportarRelatorio(tipo) {
        const formData = new FormData(document.getElementById('filtrosForm'));
        formData.append('tipo_export', tipo);

        fetch('{{ route("admin.relatorios.exportar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `relatorio_${new Date().toISOString().split('T')[0]}.${tipo}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao exportar relatório');
        });
    }
</script>
@endpush