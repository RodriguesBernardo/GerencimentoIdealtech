@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .card-stat {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .card-stat:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); color: var(--primary-500); }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); color: var(--success-500); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); color: var(--warning-500); }
    .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-500); }
    .bg-soft-info { background-color: rgba(59, 130, 246, 0.1); color: var(--info-500); }

    .chart-container {
        position: relative;
        height: 250px; /* Altura fixa controlada */
        width: 100%;
    }
    
    .table-modern th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        font-weight: 600;
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: 0.75rem;
    }
    .table-modern td {
        padding: 1rem 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--gray-100);
    }
    .table-modern tr:last-child td {
        border-bottom: none;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 1.5rem;
        border-left: 2px solid var(--gray-200);
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        border-left: 2px solid transparent;
        padding-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
</style>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">A Receber (Vencido)</h6>
                        <h4 class="mb-0 fw-bold text-danger">R$ {{ number_format($estatisticasCobranca['valor_total_vencido'], 2, ',', '.') }}</h4>
                    </div>
                    <div class="stat-icon bg-soft-danger">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Clientes Pendentes</h6>
                        <h3 class="mb-0 fw-bold">{{ $estatisticasCobranca['clientes_pendentes'] }}</h3>
                    </div>
                    <div class="stat-icon bg-soft-warning">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">A Vencer (7 dias)</h6>
                        <h4 class="mb-0 fw-bold text-primary">R$ {{ number_format($estatisticasCobranca['valor_total_a_vencer'], 2, ',', '.') }}</h4>
                    </div>
                    <div class="stat-icon bg-soft-primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Serviços Pendentes de Pagamento</h6>
                        <h3 class="mb-0 fw-bold">{{ $servicosPendentes->count() }}</h3>
                    </div>
                    <div class="stat-icon bg-soft-info">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        
        <div class="card mb-4">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Desempenho Semestral</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="progressaoMensalChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Parcelas Vencidas
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive px-3 pb-3">
                            <table class="table table-modern w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente / Serviço</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parcelasVencidas->take(5) as $parcela)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar bg-soft-danger text-danger me-3" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($parcela->servico->cliente->nome ?? 'C', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ Str::limit($parcela->servico->cliente->nome ?? 'Cliente não encontrado', 25) }}</div>
                                                    <div class="small text-muted">{{ Str::limit($parcela->servico->descricao ?? 'N/A', 30) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-danger fw-medium">{{ $parcela->data_vencimento->format('d/m') }}</div>
                                            <div class="small text-muted">{{ $parcela->data_vencimento->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-end fw-bold">
                                            R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}
                                            <div class="small text-muted fw-normal">{{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }}</div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('servicos.show', $parcela->servico_id) }}" class="btn btn-sm text-primary">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Nenhuma parcela vencida.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Serviços Recentes</h5>
                        <a href="{{ route('servicos.index') }}" class="btn btn-sm text-muted">Ver todos</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive px-3 pb-3">
                            <table class="table table-modern w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>Serviço</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($servicosPendentes->take(5) as $servico)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ Str::limit($servico->descricao, 30) }}</div>
                                            <div class="small text-muted">{{ $servico->cliente->nome }}</div>
                                        </td>
                                        <td>
                                            @if($servico->status_pagamento == 'pago')
                                                <span class="badge bg-soft-success text-success">Pago</span>
                                            @elseif($servico->status_pagamento == 'pendente')
                                                <span class="badge bg-soft-warning text-warning">Pendente</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Não Pago</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $servico->data_servico->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            R$ {{ number_format($servico->valor, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum serviço recente.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        
        <div class="card mb-4">
            <div class="card-header border-0 bg-transparent">
                <h5 class="card-title mb-0">Agenda (7 Dias)</h5>
            </div>
            <div class="card-body pt-0">
                <div class="mt-2">
                    @forelse($proximosEventos as $evento)
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background-color: {{ $evento->cor ?? 'var(--primary-500)' }}"></div>
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-bold">{{ $evento->titulo }}</span>
                            @if($evento->dias_restantes == 0)
                                <span class="badge bg-success small">Hoje</span>
                            @elseif($evento->dias_restantes == 1)
                                <span class="badge bg-info small">Amanhã</span>
                            @else
                                <span class="text-muted small" style="font-size: 0.7rem">{{ $evento->data_formatada }}</span>
                            @endif
                        </div>
                        <p class="mb-1 small text-muted">
                            <i class="far fa-clock me-1"></i> {{ $evento->hora_formatada }} - {{ $evento->cliente->nome ?? 'Sem cliente' }}
                        </p>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-check fa-3x text-gray-200 mb-3"></i>
                        <p class="text-muted small">Agenda livre para os próximos dias.</p>
                        <a href="{{ route('atendimentos.index') }}" class="btn btn-sm btn-outline-primary mt-2">Agendar</a>
                    </div>
                    @endforelse
                </div>
                @if($proximosEventos->count() > 0)
                <div class="text-center mt-3">
                    <a href="{{ route('atendimentos.index') }}" class="btn btn-sm w-100 text-primary fw-medium">Ver Agenda Completa</a>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0 bg-transparent">
                <h5 class="card-title mb-0">Top Pendências</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($clientesParaCobrar->take(5) as $cliente)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar bg-gray-100 text-gray-600 me-3" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ strtoupper(substr($cliente->nome, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium small">{{ Str::limit($cliente->nome, 18) }}</div>
                                <div class="text-danger small" style="font-size: 0.7rem;">{{ $cliente->servicos->whereIn('status_pagamento', ['pendente', 'nao_pago'])->count() }} pendências</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small">R$ {{ number_format($cliente->total_pendente, 2, ',', '.') }}</div>
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-link p-0 text-muted" style="font-size: 0.7rem; text-decoration: none;">Cobrar <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted small">Nenhum cliente com pendências.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('progressaoMensalChart').getContext('2d');
        
        // Criar gradiente para o gráfico
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Cor primária com opacidade
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctx, {
            type: 'line', // Mudado para linha (mais moderno para progressão)
            data: {
                labels: @json($progressaoMensal['meses']),
                datasets: [{
                    label: 'Serviços',
                    data: @json($progressaoMensal['quantidades']),
                    backgroundColor: gradient,
                    borderColor: '#3B82F6', // Cor primária do seu tema
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3B82F6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Curva suave
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permite ajustar a altura pelo CSS
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        titleColor: '#F3F4F6',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Serviços';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#E5E7EB',
                            drawBorder: false,
                        },
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            color: '#9CA3AF'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#9CA3AF'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush