@extends('layouts.app')

@section('title', 'Painel Administrativo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Painel Admin</li>
@endsection

@section('content')
<div class="row">
    <!-- Estatísticas Administrativas -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title text-muted mb-2">Total Usuários</h5>
                        <h3 class="mb-0">{{ $totalUsuarios }}</h3>
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
                        <h5 class="card-title text-muted mb-2">Total Clientes</h5>
                        <h3 class="mb-0">{{ $totalClientes }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-gradient text-white rounded-circle p-3">
                            <i class="fas fa-user-friends fa-2x"></i>
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
                        <div class="bg-info bg-gradient text-white rounded-circle p-3">
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
                        <h5 class="card-title text-muted mb-2">Valor Este Mês</h5>
                        <h3 class="mb-0">R$ {{ number_format($valorTotalMes, 2, ',', '.') }}</h3>
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
    <!-- Últimos Serviços -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Últimos Serviços</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ultimosServicos as $servico)
                            <tr>
                                <td>{{ $servico->cliente->nome }}</td>
                                <td>{{ Str::limit($servico->descricao_servico, 30) }}</td>
                                <td>R$ {{ number_format($servico->valor, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($servico->status == 'Pago') badge-pago
                                        @elseif($servico->status == 'Atrasado') badge-atrasado
                                        @else badge-pendente @endif">
                                        {{ $servico->status }}
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

    <!-- Últimos Usuários -->
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Últimos Usuários</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Tipo</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ultimosUsuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    <span class="badge {{ $usuario->is_admin ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $usuario->is_admin ? 'Admin' : 'Usuário' }}
                                    </span>
                                </td>
                                <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
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
    <!-- Gráfico de Serviços por Status -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribuição de Serviços por Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Serviços por Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($servicosPorStatus->pluck('status')) !!},
                datasets: [{
                    label: 'Quantidade de Serviços',
                    data: {!! json_encode($servicosPorStatus->pluck('total')) !!},
                    backgroundColor: [
                        '#28a745', // Pago - Verde
                        '#ffc107', // Pendente - Amarelo
                        '#dc3545', // Atrasado - Vermelho
                        '#17a2b8', // Outros - Azul
                        '#6c757d'  // Outros - Cinza
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
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
    });
</script>
@endpush