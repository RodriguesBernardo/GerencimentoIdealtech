@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Estatísticas Principais -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Total Clientes</h5>
                        <h3 class="mb-0">{{ $totalClientes }}</h3>
                        <small class="text-success">
                            <i class="fas fa-trend-up me-1"></i>
                            Ativos
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Total Serviços</h5>
                        <h3 class="mb-0">{{ $totalServicos }}</h3>
                        <small class="text-muted">
                            Todos os tempos
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-tools fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Valor Recebido</h5>
                        <h3 class="mb-0">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Pagos
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Valor Pendente</h5>
                        <h3 class="mb-0">R$ {{ number_format($valorPendente, 2, ',', '.') }}</h3>
                        <small class="text-warning">
                            <i class="fas fa-clock me-1"></i>
                            A receber
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Evolução Mensal -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Evolução de Serviços (Últimos 6 Meses)</h5>
                <span class="badge bg-primary">Mensal</span>
            </div>
            <div class="card-body">
                <canvas id="evolucaoMensalChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos de Status e Tipo de Pagamento -->
    <div class="col-xl-4 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Status dos Serviços</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tipo de Pagamento</h5>
            </div>
            <div class="card-body">
                <canvas id="tipoPagamentoChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Serviços Recentes -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Serviços Recentes</h5>
                <a href="{{ route('servicos.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="">
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicosRecentes as $servico)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-muted me-2"></i>
                                        <span>{{ Str::limit($servico->cliente->nome, 15) }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($servico->nome, 20) }}</td>
                                <td>
                                    <span class="fw-bold text-success">R$ {{ number_format($servico->valor, 2, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($servico->status_pagamento == 'pago') bg-success
                                        @elseif($servico->status_pagamento == 'nao_pago') bg-danger
                                        @else bg-warning text-dark @endif">
                                        {{ ucfirst($servico->status_pagamento) }}
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

    <!-- Top Clientes e Parcelas Pendentes -->
    <div class="col-xl-6 mb-4">
        <!-- Top Clientes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Top Clientes</h5>
                <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                @foreach($topClientes as $cliente)
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-2">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $cliente->nome }}</h6>
                        <small class="text-muted">
                            {{ $cliente->total_servicos }} serviços • 
                            R$ {{ number_format($cliente->servicos_sum_valor, 2, ',', '.') }}
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Parcelas Pendentes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Parcelas a Vencer</h5>
                <span class="badge bg-warning">{{ $parcelasPendentes->count() }}</span>
            </div>
            <div class="card-body">
                @forelse($parcelasPendentes as $parcela)
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle p-2">
                            <i class="fas fa-calendar-day text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $parcela->servico->cliente->nome }}</h6>
                        <small class="text-muted">
                            Parcela {{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }} • 
                            R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }} • 
                            Vence: {{ $parcela->data_vencimento->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p class="mb-0">Nenhuma parcela pendente</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Evolução Mensal
        const evolucaoCtx = document.getElementById('evolucaoMensalChart').getContext('2d');
        const evolucaoChart = new Chart(evolucaoCtx, {
            type: 'line',
            data: {
                labels: @json($evolucaoMensal['meses']),
                datasets: [{
                    label: 'Serviços Realizados',
                    data: @json($evolucaoMensal['valores']),
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
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Gráfico de Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pago', 'Pendente', 'Não Pago'],
                datasets: [{
                    data: [
                        {{ $servicosPorStatus['pago'] }},
                        {{ $servicosPorStatus['pendente'] }},
                        {{ $servicosPorStatus['nao_pago'] }}
                    ],
                    backgroundColor: [
                        '#10B981',
                        '#F59E0B', 
                        '#EF4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });

        // Gráfico de Tipo de Pagamento
        const tipoPagamentoCtx = document.getElementById('tipoPagamentoChart').getContext('2d');
        const tipoPagamentoChart = new Chart(tipoPagamentoCtx, {
            type: 'pie',
            data: {
                labels: ['À Vista', 'Parcelado'],
                datasets: [{
                    data: [
                        {{ $distribuicaoTipoPagamento['avista'] }},
                        {{ $distribuicaoTipoPagamento['parcelado'] }}
                    ],
                    backgroundColor: [
                        '#8B5CF6',
                        '#06B6D4'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        border-bottom: 1px solid #dee2e6;
    }
    
    .border-start {
        border-left-width: 4px !important;
    }
</style>
@endpush