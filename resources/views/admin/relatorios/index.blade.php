@extends('layouts.app')

@section('title', 'Relatórios Gerenciais')

@section('content')
@php
    $inicioFormatado = \Carbon\Carbon::parse($dataInicio ?? now()->startOfMonth())->format('d/m/Y');
    $fimFormatado = \Carbon\Carbon::parse($dataFim ?? now()->endOfMonth())->format('d/m/Y');
    $periodoTexto = "Período: {$inicioFormatado} até {$fimFormatado}";
@endphp

<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.relatorios.index') }}" method="GET" id="formFiltro">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-end gap-3 justify-content-between">
                            <p class="text-muted mb-0 small">
                                Exibindo dados de <span class="fw-bold ">{{ $inicioFormatado }}</span> até <span class="fw-bold ">{{ $fimFormatado }}</span>
                            </p>

                            <div class="btn-group shadow-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodo('mes_atual')">Este Mês</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodo('mes_anterior')">Mês Passado</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodo('ano_passado')">Ano Passado</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodo('ano_atual')">Este Ano</button>
                            </div>

                            <div class="d-flex gap-2 align-items-end">
                                <div>
                                    <label class="form-label text-muted small fw-bold mb-1">Início</label>
                                    <input type="date" class="form-control form-control-sm" name="data_inicial" id="data_inicial" 
                                           value="{{ $dataInicio ?? now()->startOfMonth()->format('Y-m-d') }}">
                                </div>
                                <div>
                                    <label class="form-label text-muted small fw-bold mb-1">Fim</label>
                                    <input type="date" class="form-control form-control-sm" name="data_final" id="data_final" 
                                           value="{{ $dataFim ?? now()->endOfMonth()->format('Y-m-d') }}">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-3" title="Filtrar">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v text-muted cursor-pointer"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">R$ {{ number_format($dadosRelatorios['resumo']['valor_total_arrecadado'], 0, ',', '.') }}</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-bold ls-1">Faturamento (Caixa)</p>
                    <div class="mt-3 small">
                        <span class="text-success fw-bold">
                            <i class="fas fa-arrow-up me-1"></i>Recebido no período
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">R$ {{ number_format($dadosRelatorios['resumo']['ticket_medio'], 2, ',', '.') }}</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-bold ls-1">Ticket Médio</p>
                    <div class="mt-3 small text-muted">
                        Média por serviço realizado
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ number_format($dadosRelatorios['insights']['taxa_inadimplencia'], 1) }}%</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-bold ls-1">Taxa Inadimplência</p>
                    <div class="mt-3 small">
                        <span class="text-danger fw-bold">Referente ao período</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="fas fa-cogs fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $dadosRelatorios['resumo']['total_servicos'] }}</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-bold ls-1">Total de Serviços</p>
                    <div class="mt-3 small text-muted">
                        Executados no período
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0">Evolução do Faturamento</h5>
                        <p class="text-muted small mb-0">Comparativo dos últimos 12 meses (Caixa)</p>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary align-self-start px-3 py-2 rounded-pill">
                        Total Ano: R$ {{ number_format($dadosRelatorios['resumo']['valor_ano_atual'], 2, ',', '.') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="graficoFaturamento"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Status de Pagamentos</h5>
                    <p class="text-muted small mb-0">Distribuição do período selecionado</p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <div style="height: 220px; position: relative;">
                        <canvas id="graficoStatusPagamento"></canvas>
                    </div>
                    <div class="mt-4">
                        @foreach($dadosRelatorios['graficos']['status_pagamento']['labels'] as $index => $label)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded-3 hover-">
                            <div class="d-flex align-items-center">
                                <span class="badge rounded-circle p-1 me-2" style="background-color: {{ $statusColors[strtolower(str_replace(' ', '_', $label))] ?? '#6B7280' }}"></span>
                                <span class="small fw-bold ">{{ $label }}</span>
                            </div>
                            <span class="small fw-bold">R$ {{ number_format($dadosRelatorios['graficos']['status_pagamento']['valores'][$index], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Top 10 Clientes</h5>
                    <p class="text-muted small mb-0">Por faturamento no período</p>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="graficoTopClientes"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white" 
                 style="background: linear-gradient(45deg, #2563eb, #1d4ed8);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-lightbulb fa-2x me-3 text-warning"></i>
                        <h4 class="mb-0 fw-bold">Insights do Período</h4>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 h-100 border border-white border-opacity-25">
                                <small class="text-white-50 text-uppercase fw-bold ls-1">Melhor Cliente</small>
                                <h5 class="mt-2 mb-1 fw-bold">{{ $dadosRelatorios['insights']['melhor_cliente']->nome ?? 'N/A' }}</h5>
                                <small class="text-white">
                                    @if(isset($dadosRelatorios['insights']['melhor_cliente']))
                                    Gerou R$ {{ number_format($dadosRelatorios['insights']['melhor_cliente']->servicos_sum_valor, 2, ',', '.') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 h-100 border border-white border-opacity-25">
                                <small class="text-white-50 text-uppercase fw-bold ls-1">Serviço + Lucrativo</small>
                                <h5 class="mt-2 mb-1 fw-bold">{{ Str::limit($dadosRelatorios['insights']['servico_mais_lucrativo']->descricao ?? 'N/A', 20) }}</h5>
                                <small class="text-white">
                                    @if(isset($dadosRelatorios['insights']['servico_mais_lucrativo']))
                                    Total: R$ {{ number_format($dadosRelatorios['insights']['servico_mais_lucrativo']->total, 2, ',', '.') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 h-100 border border-white border-opacity-25">
                                <small class="text-white-50 text-uppercase fw-bold ls-1">Dia + Produtivo</small>
                                <h5 class="mt-2 mb-1 fw-bold">{{ $dadosRelatorios['insights']['dia_semana_mais_produtivo'] }}</h5>
                                <small class="text-white">Baseado no volume de serviços</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 h-100 border border-white border-opacity-25">
                                <small class="text-white-50 text-uppercase fw-bold ls-1">Previsão Anual (Est.)</small>
                                <h5 class="mt-2 mb-1 fw-bold">R$ {{ number_format($dadosRelatorios['insights']['previsao_faturamento'], 0, ',', '.') }}</h5>
                                <small class="text-white">Se manter a média atual</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Detalhamento dos Serviços</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver Todos</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                            <thead class="">
                                <tr>
                                    <th class="px-4 py-3 border-0 text-muted small fw-bold text-uppercase">Data</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Cliente</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Valor</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dadosRelatorios['tabelas']['servicos_recentes'] as $servico)
                                <tr class="border-bottom border-light">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class=" rounded p-2 me-2 text-muted">
                                                <i class="far fa-calendar-alt"></i>
                                            </div>
                                            <span class="fw-bold ">{{ \Carbon\Carbon::parse($servico['data'])->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="d-block fw-bold ">{{ \Illuminate\Support\Str::limit($servico['cliente'], 25) }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-bold text-primary">R$ {{ number_format($servico['valor'], 2, ',', '.') }}</span>
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $statusClass = match($servico['status']) {
                                                'pago' => 'success',
                                                'pendente' => 'warning',
                                                'nao_pago' => 'danger',
                                                default => 'secondary'
                                            };
                                            $statusIcon = match($servico['status']) {
                                                'pago' => 'check-circle',
                                                'pendente' => 'clock',
                                                'nao_pago' => 'times-circle',
                                                default => 'minus'
                                            };
                                        @endphp
                                        <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }} rounded-pill px-3 py-2">
                                            <i class="fas fa-{{ $statusIcon }} me-1"></i> {{ ucfirst(str_replace('_', ' ', $servico['status'])) }}
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
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Estilos Personalizados para modernização */
    .ls-1 { letter-spacing: 1px; }
    .cursor-pointer { cursor: pointer; }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1) !important; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1) !important; }
    
    .hover-:hover { background-color: #f8f9fa; transition: 0.3s; }
    .card { transition: transform 0.2s ease-in-out; }
    
    /* Input de data customizado */
    input[type="date"] {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 6px 12px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Pega o texto do período gerado pelo PHP
        const periodoTexto = "{{ $periodoTexto }}";

        // Configuração Comum para os Gráficos (Plugins)
        const commonPlugins = {
            legend: {
                display: false // Oculta legenda padrão pois fizemos customizada ou está no título
            },
            subtitle: {
                display: true,
                text: periodoTexto,
                color: '#6c757d',
                font: {
                    size: 12,
                    family: "'Inter', sans-serif",
                    style: 'italic'
                },
                padding: {
                    bottom: 20
                }
            }
        };

        // Cores
        const colors = {
            primary: '#3B82F6',
            success: '#10B981',
            danger: '#EF4444',
            warning: '#F59E0B',
            info: '#06B6D4'
        };

        // 1. Gráfico de Faturamento Mensal
        new Chart(document.getElementById('graficoFaturamento'), {
            type: 'line',
            data: {
                labels: @json($dadosRelatorios['graficos']['faturamento_mensal']['labels']),
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: @json($dadosRelatorios['graficos']['faturamento_mensal']['valores']),
                    borderColor: colors.primary,
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
                        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                        return gradient;
                    },
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: colors.primary,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    ...commonPlugins,
                    // Sobrescreve legenda para este caso
                    subtitle: {
                        display: true,
                        text: 'Histórico dos últimos 12 meses',
                        padding: { bottom: 10 }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { callback: (val) => 'R$ ' + val.toLocaleString('pt-BR') }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. Gráfico de Status de Pagamento (Doughnut)
        new Chart(document.getElementById('graficoStatusPagamento'), {
            type: 'doughnut',
            data: {
                labels: @json($dadosRelatorios['graficos']['status_pagamento']['labels']),
                datasets: [{
                    data: @json($dadosRelatorios['graficos']['status_pagamento']['valores']),
                    backgroundColor: [colors.success, colors.warning, colors.danger, colors.info],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    ...commonPlugins, // Adiciona o período
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                let value = context.parsed;
                                return label + 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });

        // 3. Gráfico de Top Clientes (Bar Horizontal)
        new Chart(document.getElementById('graficoTopClientes'), {
            type: 'bar',
            data: {
                labels: @json($dadosRelatorios['graficos']['top_clientes']['labels']),
                datasets: [{
                    label: 'Faturamento',
                    data: @json($dadosRelatorios['graficos']['top_clientes']['valores']),
                    backgroundColor: colors.info,
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: commonPlugins,
                scales: {
                    x: {
                        grid: { borderDash: [5, 5] },
                        ticks: { callback: (val) => 'R$ ' + val.toLocaleString('pt-BR') }
                    },
                    y: { grid: { display: false } }
                }
            }
        });

        // 4. Evolução Parcelas (Bar Stacked ou Grouped)
        new Chart(document.getElementById('graficoEvolucaoParcelas'), {
            type: 'bar',
            data: {
                labels: @json($dadosRelatorios['graficos']['evolucao_parcelas']['labels']),
                datasets: [
                    {
                        label: 'Pagas',
                        data: @json($dadosRelatorios['graficos']['evolucao_parcelas']['pagas']),
                        backgroundColor: colors.success,
                        borderRadius: 4
                    },
                    {
                        label: 'Pendentes',
                        data: @json($dadosRelatorios['graficos']['evolucao_parcelas']['pendentes']),
                        backgroundColor: colors.danger,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: commonPlugins,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: false,
                        grid: { borderDash: [5, 5] }
                    },
                    x: { stacked: false, grid: { display: false } }
                }
            }
        });
    });

    // Função de Filtro
    function setPeriodo(tipo) {
        const hoje = new Date();
        let inicio, fim;
        const formatar = (data) => data.toISOString().split('T')[0];

        switch(tipo) {
            case 'mes_atual':
                inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                fim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
                break;
            case 'mes_anterior':
                inicio = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
                fim = new Date(hoje.getFullYear(), hoje.getMonth(), 0);
                break;
            case 'ano_atual':
                inicio = new Date(hoje.getFullYear(), 0, 1);
                fim = new Date(hoje.getFullYear(), 11, 31);
                break;
            case 'ano_passado':
                inicio = new Date(hoje.getFullYear() - 1, 0, 1);
                fim = new Date(hoje.getFullYear() - 1, 11, 31);
                break;
        }

        document.getElementById('data_inicial').value = formatar(inicio);
        document.getElementById('data_final').value = formatar(fim);
        document.getElementById('formFiltro').submit();
    }
</script>
@endpush