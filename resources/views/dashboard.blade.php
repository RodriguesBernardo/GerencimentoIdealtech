@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Estatísticas -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Total Clientes</h5>
                        <h3 class="mb-0">{{ $totalClientes }}</h3>
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
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Total Serviços</h5>
                        <h3 class="mb-0">{{ $totalServicos }}</h3>
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
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Serviços Este Mês</h5>
                        <h3 class="mb-0">{{ $servicosMes }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">
                            @if(auth()->user()->podeVerValoresCompletos())
                                Valor Total Mês
                            @else
                                Serviços Realizados
                            @endif
                        </h5>
                        <h3 class="mb-0">
                            @if(auth()->user()->podeVerValoresCompletos())
                                R$ {{ number_format($valorTotalMes, 2, ',', '.') }}
                            @else
                                {{ $servicosMes }}
                            @endif
                        </h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Serviços por Status -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Serviços por Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Serviços Recentes -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Serviços Recentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicosRecentes as $servico)
                            <tr>
                                <td>{{ $servico->cliente->nome }}</td>
                                <td>{{ Str::limit($servico->descricao_servico, 30) }}</td>
                                <td>
                                    <span class="badge 
                                        @if($servico->status == 'Pago') badge-pago
                                        @elseif($servico->status == 'Atrasado') badge-atrasado
                                        @else badge-pendente @endif">
                                        {{ $servico->status }}
                                    </span>
                                </td>
                                <td>{{ $servico->created_at->format('d/m/Y') }}</td>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($servicosPorStatus->pluck('status')) !!},
                datasets: [{
                    data: {!! json_encode($servicosPorStatus->pluck('total')) !!},
                    backgroundColor: [
                        '#28a745', // Pago - Verde
                        '#ffc107', // Pendente - Amarelo
                        '#dc3545', // Atrasado - Vermelho
                        '#17a2b8', // Outros - Azul
                        '#6c757d'  // Outros - Cinza
                    ]
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
    });
</script>
@endpush