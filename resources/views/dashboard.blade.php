@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Estatísticas de Cobrança -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Clientes Pendentes</h5>
                        <h3 class="mb-0">{{ $estatisticasCobranca['clientes_pendentes'] }}</h3>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Para cobrar
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Parcelas Vencidas</h5>
                        <h3 class="mb-0">{{ $estatisticasCobranca['parcelas_vencidas'] }}</h3>
                        <small class="text-danger">
                            <i class="fas fa-clock me-1"></i>
                            Em atraso
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-calendar-times fa-2x"></i>
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
                        <h5 class="card-title text-muted mb-2">Parcelas a Vencer</h5>
                        <h3 class="mb-0">{{ $estatisticasCobranca['parcelas_a_vencer'] }}</h3>
                        <small class="text-info">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Próximos 7 dias
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-calendar-check fa-2x"></i>
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
                        <h5 class="card-title text-muted mb-2">Valor Vencido</h5>
                        <h3 class="mb-0">R$ {{ number_format($estatisticasCobranca['valor_total_vencido'], 2, ',', '.') }}</h3>
                        <small class="text-success">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Para receber
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Progressão Mensal -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Progressão Mensal de Serviços</h5>
                <span class="badge bg-primary">Últimos 6 meses</span>
            </div>
            <div class="card-body">
                <canvas id="progressaoMensalChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Resumo de Cobranças -->
    <div class="col-xl-4 mb-4">
        <div class="card mb-4">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Resumo de Cobranças
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-danger bg-opacity-10">
                    <div>
                        <h6 class="mb-0 text-danger">Parcelas Vencidas</h6>
                        <small class="text-muted">Valor total em atraso</small>
                    </div>
                    <div class="text-end">
                        <strong class="text-danger">{{ $estatisticasCobranca['parcelas_vencidas'] }}</strong>
                        <br>
                        <small class="text-danger">R$ {{ number_format($estatisticasCobranca['valor_total_vencido'], 2, ',', '.') }}</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-info bg-opacity-10">
                    <div>
                        <h6 class="mb-0 text-info">Parcelas a Vencer</h6>
                        <small class="text-muted">Próximos 7 dias</small>
                    </div>
                    <div class="text-end">
                        <strong class="text-info">{{ $estatisticasCobranca['parcelas_a_vencer'] }}</strong>
                        <br>
                        <small class="text-info">R$ {{ number_format($estatisticasCobranca['valor_total_a_vencer'], 2, ',', '.') }}</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-warning bg-opacity-10">
                    <div>
                        <h6 class="mb-0 text-warning">Clientes Pendentes</h6>
                        <small class="text-muted">Para contatar</small>
                    </div>
                    <div class="text-end">
                        <strong class="text-warning">{{ $estatisticasCobranca['clientes_pendentes'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Clientes para Cobrar -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Clientes para Cobrar
                </h5>
                <span class="badge bg-warning">{{ $clientesParaCobrar->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-warning">
                            <tr>
                                <th>Cliente</th>
                                <th>Pendente</th>
                                <th>Parcelas Vencidas</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientesParaCobrar as $cliente)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-warning me-2"></i>
                                        <div>
                                            <strong>{{ $cliente->nome }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $cliente->servicos->whereIn('status_pagamento', ['pendente', 'nao_pago'])->count() }} serviços pendentes
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        R$ {{ number_format($cliente->total_pendente, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    @if($cliente->parcelas_vencidas > 0)
                                        <span class="badge bg-danger">{{ $cliente->parcelas_vencidas }} vencidas</span>
                                    @else
                                        <span class="badge bg-secondary">Nenhuma</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('servicos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus"></i>
                                    </a>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-danger bg-opacity-10">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                    Parcelas Vencidas
                </h5>
                <span class="badge bg-danger">{{ $parcelasVencidas->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Parcela</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcelasVencidas as $parcela)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-danger me-2"></i>
                                        <span>{{ Str::limit($parcela->servico->cliente->nome, 15) }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($parcela->servico->nome, 20) }}</td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger">
                                        {{ $parcela->data_vencimento->format('d/m/Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $parcela->data_vencimento->diffForHumans() }}
                                    </small>
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

<div class="row">
    <!-- Parcelas a Vencer -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-info bg-opacity-10">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt text-info me-2"></i>
                    Parcelas a Vencer (7 dias)
                </h5>
                <span class="badge bg-info">{{ $parcelasAVencer->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-info">
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Parcela</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcelasAVencer as $parcela)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-info me-2"></i>
                                        <span>{{ Str::limit($parcela->servico->cliente->nome, 15) }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($parcela->servico->nome, 20) }}</td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-info">
                                        R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-info">
                                        {{ $parcela->data_vencimento->format('d/m/Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $parcela->data_vencimento->diffForHumans() }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Serviços Pendentes -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock text-warning me-2"></i>
                    Serviços Pendentes
                </h5>
                <span class="badge bg-warning">{{ $servicosPendentes->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-warning">
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicosPendentes as $servico)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-warning me-2"></i>
                                        <span>{{ Str::limit($servico->cliente->nome, 15) }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($servico->nome, 20) }}</td>
                                <td>
                                    <span class="fw-bold text-warning">
                                        R$ {{ number_format($servico->valor, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($servico->status_pagamento == 'pendente') bg-warning text-dark
                                        @else bg-danger @endif">
                                        {{ ucfirst(str_replace('_', ' ', $servico->status_pagamento)) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $servico->data_servico->format('d/m/Y') }}
                                    </small>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Progressão Mensal (Barras)
        const progressaoCtx = document.getElementById('progressaoMensalChart').getContext('2d');
        const progressaoChart = new Chart(progressaoCtx, {
            type: 'bar',
            data: {
                labels: @json($progressaoMensal['meses']),
                datasets: [{
                    label: 'Serviços Cadastrados',
                    data: @json($progressaoMensal['quantidades']),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(14, 165, 233, 0.7)',
                        'rgba(236, 72, 153, 0.7)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)',
                        'rgb(14, 165, 233)',
                        'rgb(236, 72, 153)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Serviços: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        },
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
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
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
</style>
@endpush