@extends('layouts.app')

@section('title', 'Relatórios - Visão Geral')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Relatórios</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtro de Período -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar me-2"></i>Período do Relatório
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.relatorios.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="periodo" class="form-label">Selecione o Período</label>
                    <select class="form-control" id="periodo" name="periodo" onchange="this.form.submit()">
                        <option value="semana_atual" {{ $periodo == 'semana_atual' ? 'selected' : '' }}>Semana Atual</option>
                        <option value="mes_atual" {{ $periodo == 'mes_atual' ? 'selected' : '' }}>Mês Atual</option>
                        <option value="mes_anterior" {{ $periodo == 'mes_anterior' ? 'selected' : '' }}>Mês Anterior</option>
                        <option value="trimestre_atual" {{ $periodo == 'trimestre_atual' ? 'selected' : '' }}>Trimestre Atual</option>
                        <option value="semestre_atual" {{ $periodo == 'semestre_atual' ? 'selected' : '' }}>Semestre Atual</option>
                        <option value="ano_atual" {{ $periodo == 'ano_atual' ? 'selected' : '' }}>Ano Atual</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i>Atualizar
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>Exportar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.relatorios.exportar', ['periodo' => $periodo, 'tipo' => 'pdf']) }}">PDF</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.relatorios.exportar', ['periodo' => $periodo, 'tipo' => 'excel']) }}">Excel</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Valor Arrecadado (Período)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ {{ number_format($dadosRelatorios['resumo']['valor_total_arrecadado'], 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Valor Total no Ano
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ {{ number_format($dadosRelatorios['resumo']['valor_ano_atual'], 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ticket Médio
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ {{ number_format($dadosRelatorios['resumo']['ticket_medio'], 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Crescimento Mensal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dadosRelatorios['resumo']['crescimento_mensal'], 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Importantes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Insights do Negócio
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-crown me-2"></i>Melhor Cliente</h6>
                                <p class="mb-0">
                                    {{ $dadosRelatorios['insights']['melhor_cliente']->nome ?? 'N/A' }}
                                    @if(isset($dadosRelatorios['insights']['melhor_cliente']))
                                        - R$ {{ number_format($dadosRelatorios['insights']['melhor_cliente']->servicos_sum_valor, 2, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-star me-2"></i>Serviço Mais Lucrativo</h6>
                                <p class="mb-0">
                                    {{ $dadosRelatorios['insights']['servico_mais_lucrativo']->nome ?? 'N/A' }}
                                    @if(isset($dadosRelatorios['insights']['servico_mais_lucrativo']))
                                        - R$ {{ number_format($dadosRelatorios['insights']['servico_mais_lucrativo']->total, 2, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-calendar-day me-2"></i>Dia Mais Produtivo</h6>
                                <p class="mb-0">{{ $dadosRelatorios['insights']['dia_semana_mais_produtivo'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Taxa de Inadimplência</h6>
                                <p class="mb-0">{{ number_format($dadosRelatorios['insights']['taxa_inadimplencia'], 1) }}%</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-primary">
                                <h6><i class="fas fa-chart-line me-2"></i>Previsão Anual</h6>
                                <p class="mb-0">R$ {{ number_format($dadosRelatorios['insights']['previsao_faturamento'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principais -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Faturamento Mensal (Últimos 12 Meses)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoFaturamento" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Status de Pagamento
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoStatusPagamento" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Mais Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Top Clientes
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoTopClientes" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Serviços Mais Comuns
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoServicosComuns" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas de Dados -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Serviços Recentes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorios['tabelas']['servicos_recentes'] as $servico)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($servico['data'])->format('d/m/Y') }}</td>
                                    <td>{{ $servico['cliente'] }}</td>
                                    <td>{{ $servico['servico'] }}</td>
                                    <td>R$ {{ number_format($servico['valor'], 2, ',', '.') }}</td>
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
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Parcelas Vencidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Dias Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorios['tabelas']['parcelas_vencidas'] as $parcela)
                                <tr>
                                    <td>{{ $parcela['cliente'] }}</td>
                                    <td>{{ $parcela['servico'] }}</td>
                                    <td>R$ {{ number_format($parcela['valor'], 2, ',', '.') }}</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Faturamento Mensal
        new Chart(document.getElementById('graficoFaturamento'), {
            type: 'line',
            data: {
                labels: @json($dadosRelatorios['graficos']['faturamento_mensal']['labels']),
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: @json($dadosRelatorios['graficos']['faturamento_mensal']['valores']),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
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
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
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
                    backgroundColor: '#8B5CF6',
                    borderColor: '#7C3AED',
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
                    backgroundColor: '#EC4899',
                    borderColor: '#DB2777',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endpush