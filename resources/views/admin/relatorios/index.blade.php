@extends('layouts.app')

@section('title', 'Relatórios')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header com Filtros e Exportação -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0 text-primary">
                                <i class="fas fa-chart-bar me-2"></i>Dashboard de Relatórios
                            </h4>
                            <p class="text-muted mb-0">Análise completa do seu negócio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Métricas Principais -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-primary text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Faturamento</h6>
                            <h4 class="mb-0">R$ {{ number_format($dadosRelatorios['resumo']['valor_total_arrecadado'], 0, ',', '.') }}</h4>
                            <small class="text-white-70">Período selecionado</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-success text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Faturamento Anual</h6>
                            <h4 class="mb-0">R$ {{ number_format($dadosRelatorios['resumo']['valor_ano_atual'], 0, ',', '.') }}</h4>
                            <small class="text-white-70">Ano {{ now()->year }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-info text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Ticket Médio</h6>
                            <h4 class="mb-0">R$ {{ number_format($dadosRelatorios['resumo']['ticket_medio'], 2, ',', '.') }}</h4>
                            <small class="text-white-70">Por serviço</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-warning text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Crescimento</h6>
                            <h4 class="mb-0">{{ number_format($dadosRelatorios['resumo']['crescimento_mensal'], 1) }}%</h4>
                            <small class="text-white-70">Vs mês anterior</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-trending-up fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-danger text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Inadimplência</h6>
                            <h4 class="mb-0">{{ number_format($dadosRelatorios['insights']['taxa_inadimplencia'], 1) }}%</h4>
                            <small class="text-white-70">Taxa atual</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-dark text-white shadow-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Serviços</h6>
                            <h4 class="mb-0">{{ $dadosRelatorios['resumo']['total_servicos'] }}</h4>
                            <small class="text-white-70">Período</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cogs fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primeira Linha de Gráficos -->
    <div class="row mb-4">
        <!-- Faturamento Mensal -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm border-0" style="height: 400px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Faturamento Mensal (Últimos 12 Meses)
                        </span>
                        <span class="badge bg-primary">R$ {{ number_format(array_sum($dadosRelatorios['graficos']['faturamento_mensal']['valores']), 0, ',', '.') }}</span>
                    </h5>
                </div>
                <div class="card-body" style="height: 320px; padding: 1rem;">
                    <canvas id="graficoFaturamento"></canvas>
                </div>
            </div>
        </div>

        <!-- Status de Pagamento -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm border-0" style="height: 400px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie text-success me-2"></i>
                        Status de Pagamento
                    </h5>
                </div>
                <div class="card-body" style="height: 320px; padding: 1rem;">
                    <div style="height: 200px;">
                        <canvas id="graficoStatusPagamento"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach($dadosRelatorios['graficos']['status_pagamento']['labels'] as $index => $label)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="d-flex align-items-center">
                                <span class="badge me-2" style="background-color: {{ $statusColors[strtolower(str_replace(' ', '_', $label))] ?? '#6B7280' }}; width: 12px; height: 12px;"></span>
                                {{ $label }}
                            </span>
                            <span class="fw-bold">R$ {{ number_format($dadosRelatorios['graficos']['status_pagamento']['valores'][$index], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Linha de Gráficos -->
    <div class="row mb-4">
        <!-- Top Clientes -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0" style="height: 350px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users text-info me-2"></i>
                        Top 8 Clientes (Por Faturamento)
                    </h5>
                </div>
                <div class="card-body" style="height: 270px; padding: 1rem;">
                    <canvas id="graficoTopClientes"></canvas>
                </div>
            </div>
        </div>

        <!-- Serviços Mais Comuns -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0" style="height: 350px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools text-warning me-2"></i>
                        Serviços Mais Comuns
                    </h5>
                </div>
                <div class="card-body" style="height: 270px; padding: 1rem;">
                    <canvas id="graficoServicosComuns"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira Linha - Métricas Avançadas -->
    <div class="row mb-4">
        <!-- Evolução de Parcelas -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0" style="height: 350px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area text-danger me-2"></i>
                        Evolução de Parcelas
                    </h5>
                </div>
                <div class="card-body" style="height: 270px; padding: 1rem;">
                    <canvas id="graficoEvolucaoParcelas"></canvas>
                </div>
            </div>
        </div>

        <!-- Métricas de Desempenho -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0" style="height: 350px;">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt text-dark me-2"></i>
                        Métricas de Desempenho
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success mb-1">{{ $dadosRelatorios['resumo']['clientes_ativos'] }}</h3>
                                <small class="text-muted">Clientes Ativos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h3 class="text-info mb-1">{{ $dadosRelatorios['resumo']['total_servicos'] }}</h3>
                                <small class="text-muted">Total Serviços</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights e Análises -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Insights e Análises
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                                <h6 class="mb-1">Melhor Cliente</h6>
                                <p class="mb-1 fw-bold">{{ $dadosRelatorios['insights']['melhor_cliente']->nome ?? 'N/A' }}</p>
                                <small class="text-muted">
                                    @if(isset($dadosRelatorios['insights']['melhor_cliente']))
                                    R$ {{ number_format($dadosRelatorios['insights']['melhor_cliente']->servicos_sum_valor, 2, ',', '.') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-star fa-2x text-info mb-2"></i>
                                <h6 class="mb-1">Serviço Mais Lucrativo</h6>
                                <p class="mb-1 fw-bold">{{ $dadosRelatorios['insights']['servico_mais_lucrativo']->descricao ?? 'N/A' }}</p>
                                <small class="text-muted">
                                    @if(isset($dadosRelatorios['insights']['servico_mais_lucrativo']))
                                    R$ {{ number_format($dadosRelatorios['insights']['servico_mais_lucrativo']->total, 2, ',', '.') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
                                <h6 class="mb-1">Dia Mais Produtivo</h6>
                                <p class="mb-1 fw-bold">{{ $dadosRelatorios['insights']['dia_semana_mais_produtivo'] }}</p>
                                <small class="text-muted">Melhor dia da semana</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                <h6 class="mb-1">Previsão Anual</h6>
                                <p class="mb-1 fw-bold">R$ {{ number_format($dadosRelatorios['insights']['previsao_faturamento'], 0, ',', '.') }}</p>
                                <small class="text-muted">Baseado na média atual</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas Detalhadas -->
    <div class="row">
        <!-- Serviços Recentes -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-list text-primary me-2"></i>
                            Serviços Recentes
                        </span>
                        <span class="badge bg-primary">{{ count($dadosRelatorios['tabelas']['servicos_recentes']) }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Data</th>
                                    <th class="border-0">Cliente</th>
                                    <th class="border-0">Valor</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorios['tabelas']['servicos_recentes'] as $servico)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($servico['data'])->format('d/m') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 ms-2">
                                                {{ \Illuminate\Support\Str::limit($servico['cliente'], 15) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold">R$ {{ number_format($servico['valor'], 2, ',', '.') }}</td>
                                    <td>
                                       <span class="badge bg-{{ $servico['status'] == 'pago' ? 'success' : ($servico['status'] == 'pendente' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($servico['status']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parcelas Vencidas -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Parcelas Vencidas
                        </span>
                        <span class="badge bg-danger">{{ count($dadosRelatorios['tabelas']['parcelas_vencidas']) }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Cliente</th>
                                    <th class="border-0">Valor</th>
                                    <th class="border-0">Vencimento</th>
                                    <th class="border-0">Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorios['tabelas']['parcelas_vencidas'] as $parcela)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($parcela['cliente'], 20) }}</td>
                                    <td class="fw-bold text-danger">R$ {{ number_format($parcela['valor'], 2, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($parcela['vencimento'])->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $parcela['dias_atraso'] }} dias</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .shadow-hover:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .card {
        border-radius: 12px;
    }
    .avatar-xs {
        width: 24px;
        height: 24px;
    }
    .border-0 {
        border: none !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cores para os gráficos
        const colors = {
            primary: '#3B82F6',
            success: '#10B981',
            danger: '#EF4444',
            warning: '#F59E0B',
            info: '#06B6D4',
            purple: '#8B5CF6',
            pink: '#EC4899',
            indigo: '#6366F1'
        };

        // Gráfico de Faturamento Mensal
        new Chart(document.getElementById('graficoFaturamento'), {
            type: 'line',
            data: {
                labels: @json($dadosRelatorios['graficos']['faturamento_mensal']['labels']),
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: @json($dadosRelatorios['graficos']['faturamento_mensal']['valores']),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Status de Pagamento
        new Chart(document.getElementById('graficoStatusPagamento'), {
            type: 'doughnut',
            data: {
                labels: @json($dadosRelatorios['graficos']['status_pagamento']['labels']),
                datasets: [{
                    data: @json($dadosRelatorios['graficos']['status_pagamento']['valores']),
                    backgroundColor: [
                        colors.success,
                        colors.warning,
                        colors.danger,
                        colors.info
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '65%'
            }
        });

        // Gráfico de Top Clientes
        new Chart(document.getElementById('graficoTopClientes'), {
            type: 'bar',
            data: {
                labels: @json($dadosRelatorios['graficos']['top_clientes']['labels']),
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: @json($dadosRelatorios['graficos']['top_clientes']['valores']),
                    backgroundColor: colors.info,
                    borderColor: colors.info,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Serviços Mais Comuns
        new Chart(document.getElementById('graficoServicosComuns'), {
            type: 'bar',
            data: {
                labels: @json($dadosRelatorios['graficos']['servicos_mais_comuns']['labels']),
                datasets: [{
                    label: 'Quantidade',
                    data: @json($dadosRelatorios['graficos']['servicos_mais_comuns']['quantidades']),
                    backgroundColor: colors.warning,
                    borderColor: colors.warning,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                indexAxis: 'y'
            }
        });

        // Gráfico de Evolução de Parcelas
        new Chart(document.getElementById('graficoEvolucaoParcelas'), {
            type: 'line',
            data: {
                labels: @json($dadosRelatorios['graficos']['evolucao_parcelas']['labels']),
                datasets: [
                    {
                        label: 'Parcelas Pagas',
                        data: @json($dadosRelatorios['graficos']['evolucao_parcelas']['pagas']),
                        borderColor: colors.success,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: 'Parcelas Pendentes',
                        data: @json($dadosRelatorios['graficos']['evolucao_parcelas']['pendentes']),
                        borderColor: colors.danger,
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush