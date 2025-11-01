@extends('layouts.app')

@section('title', 'Serviços')

@section('header-actions')
{{-- Botão Novo Serviço - apenas para usuários com permissão --}}
@if(auth()->user()->is_admin || in_array('servicos.create', auth()->user()->permissoes ?? []))
    <a href="{{ route('servicos.create') }}" class="btn btn-idealtech-blue">
        <i class="fas fa-plus me-2"></i>Novo Serviço
    </a>
@endif

@auth
    @if(auth()->user()->is_admin || in_array('relatorios.view', auth()->user()->permissoes ?? []))
    <div class="btn-group">
        <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="{{ route('servicos.export.excel', request()->query()) }}">
                    <i class="fas fa-file-excel text-success me-2"></i>Excel
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                    <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                </button>
            </li>
        </ul>
    </div>
    @endif
@endauth

<!-- Modal para Ordenação do PDF -->
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportPdfModalLabel">
                    <i class="fas fa-sort me-2"></i>Ordenar Relatório
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportPdfForm" method="GET" action="{{ route('servicos.export.pdf') }}">
                    <!-- Campos hidden para manter os filtros atuais -->
                    <input type="hidden" name="data_inicial" value="{{ request('data_inicial', now()->startOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="data_final" value="{{ request('data_final', now()->endOfMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="tipo_pagamento" value="{{ request('tipo_pagamento') }}">
                    
                    <div class="mb-3">
                        <label for="ordenacao" class="form-label">Ordenar por:</label>
                        <select class="form-select" id="ordenacao" name="ordenacao" required>
                            <option value="data_desc">Data do Serviço (Mais Recente)</option>
                            <option value="data_asc">Data do Serviço (Mais Antigo)</option>
                            <option value="valor_desc">Valor (Maior para Menor)</option>
                            <option value="valor_asc">Valor (Menor para Maior)</option>
                            <option value="cliente_asc">Cliente (A-Z)</option>
                            <option value="cliente_desc">Cliente (Z-A)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('exportPdfForm').submit()">
                    <i class="fas fa-download me-1"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('content')
<!-- Insights Cards - Apenas para Admin ou usuários com permissão de relatórios -->
@auth
    @if((auth()->user()->is_admin || in_array('relatorios.view', auth()->user()->permissoes ?? [])) && isset($insights))
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-body bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">{{ number_format($insights['total_clientes']) }}</h5>
                        <small class="text-muted">Total de Clientes</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-body bg-success bg-opacity-10 border border-success border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">R$ {{ number_format($insights['valor_mes_atual'], 2, ',', '.') }}</h5>
                        <small class="text-muted">Valor do Mês</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-alt fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-body bg-info bg-opacity-10 border border-info border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">R$ {{ number_format($insights['valor_ano_atual'], 2, ',', '.') }}</h5>
                        <small class="text-muted">Valor do Ano</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-body bg-warning bg-opacity-10 border border-warning border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">R$ {{ number_format($insights['total_devedor'], 2, ',', '.') }}</h5>
                        <small class="text-muted">Total Devedor</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Secundários -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card card-body bg-success bg-opacity-10 border border-success border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">R$ {{ number_format($insights['total_pago'], 2, ',', '.') }}</h5>
                        <small class="text-muted">Total Pago</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-body bg-dark bg-opacity-10 border border-dark border-opacity-25">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">R$ {{ number_format($insights['valor_total'], 2, ',', '.') }}</h5>
                        <small class="text-muted">Valor Total</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign fa-2x text-dark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endauth

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Serviços</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form id="filterForm" method="GET" action="{{ route('servicos.index') }}">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-start"></i></span>
                        <input type="date" name="data_inicial" class="form-control" 
                            value="{{ request('data_inicial', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-end"></i></span>
                        <input type="date" name="data_final" class="form-control" 
                            value="{{ request('data_final', now()->endOfMonth()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por cliente..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="status">
                        <option value="">Todos os status</option>
                        <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    </select>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <select class="form-control" name="tipo_pagamento">
                        <option value="">Todos os tipos</option>
                        <option value="avista" {{ request('tipo_pagamento') == 'avista' ? 'selected' : '' }}>À Vista</option>
                        <option value="parcelado" {{ request('tipo_pagamento') == 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('servicos.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i>Limpar
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="">
                    <tr>
                        <th width="15%">Cliente</th>
                        <th width="20%">Serviço</th>
                        <th width="10%">Valor</th>
                        <th width="10%">Tipo</th>
                        <th width="10%">Status</th>
                        <th width="10%">Data Serviço</th>
                        <th width="15%">Progresso</th>
                        <th width="10%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicos as $servico)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <strong class="d-block">{{ $servico->cliente->nome }}</strong>
                                    <small class="text-muted">{{ $servico->cliente->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ Str::limit($servico->descricao, 50) }}</small>
                        </td>
                        <td>
                            <span class="fw-bold text-success">R$ {{ number_format($servico->valor, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $servico->tipo_pagamento == 'avista' ? 'bg-info' : 'bg-secondary' }}">
                                <i class="fas {{ $servico->tipo_pagamento == 'avista' ? 'fa-money-bill-wave' : 'fa-credit-card' }} me-1"></i>
                                {{ $servico->tipo_pagamento == 'avista' ? 'À Vista' : 'Parcelado' }}
                                @if($servico->tipo_pagamento == 'parcelado')
                                    ({{ $servico->parcelas }}x)
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                @if($servico->status_pagamento == 'pago') bg-success
                                @elseif($servico->status_pagamento == 'nao_pago') bg-danger
                                @else bg-warning text-dark @endif">
                                <i class="fas 
                                    @if($servico->status_pagamento == 'pago') fa-check-circle
                                    @elseif($servico->status_pagamento == 'nao_pago') fa-times-circle
                                    @else fa-clock @endif me-1"></i>
                                {{ ucfirst($servico->status_pagamento) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-nowrap">
                                <i class="fas fa-calendar-alt text-muted me-1"></i>
                                {{ $servico->data_servico->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            @if($servico->tipo_pagamento == 'parcelado' && $servico->parcelas > 1)
                                @php
                                    $parcelasPagas = $servico->parcelasServico->where('status', 'paga')->count();
                                    $totalParcelas = $servico->parcelasServico->count();
                                    $progresso = $totalParcelas > 0 ? ($parcelasPagas / $totalParcelas) * 100 : 0;
                                @endphp
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 me-3">
                                        <small class="d-block text-muted mb-1">
                                            {{ $parcelasPagas }}/{{ $totalParcelas }} parcelas
                                        </small>
                                        @if($totalParcelas > 0)
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success progress-dynamic" 
                                                     data-width="{{ (int)$progresso }}"
                                                     role="progressbar">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0">
                                        <small class="fw-bold {{ $progresso == 100 ? 'text-success' : 'text-primary' }}">
                                            {{ (int)$progresso }}%
                                        </small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                {{-- Ver detalhes - disponível para todos com permissão de visualização --}}
                                @if(auth()->user()->is_admin || in_array('servicos.view', auth()->user()->permissoes ?? []))
                                    <a href="{{ route('servicos.show', $servico) }}" class="btn btn-outline-primary" 
                                    data-bs-toggle="tooltip" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                
                                {{-- Editar - apenas com permissão --}}
                                @if(auth()->user()->is_admin || in_array('servicos.edit', auth()->user()->permissoes ?? []))
                                    <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-outline-secondary"
                                    data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                
                                {{-- Deletar - apenas com permissão --}}
                                @if(auth()->user()->is_admin || in_array('servicos.delete', auth()->user()->permissoes ?? []))
                                    <form action="{{ route('servicos.destroy', $servico) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                                data-bs-toggle="tooltip" title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>Nenhum serviço encontrado</h5>
                                <p>Comece cadastrando um novo serviço.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação Melhorada -->
        @if($servicos->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Mostrando {{ $servicos->firstItem() }} a {{ $servicos->lastItem() }} de {{ $servicos->total() }} registros
            </div>
            <nav aria-label="Navegação de páginas">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Page Link -->
                    @if($servicos->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-chevron-left fa-xs"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $servicos->previousPageUrl() }}" aria-label="Anterior">
                                <i class="fas fa-chevron-left fa-xs"></i>
                            </a>
                        </li>
                    @endif

                    <!-- Pagination Elements - Mostrar apenas algumas páginas -->
                    @php
                        $current = $servicos->currentPage();
                        $last = $servicos->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    <!-- Primeira página -->
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $servicos->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    <!-- Páginas do meio -->
                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $servicos->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $servicos->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor

                    <!-- Última página -->
                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $servicos->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <!-- Next Page Link -->
                    @if($servicos->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $servicos->nextPageUrl() }}" aria-label="Próximo">
                                <i class="fas fa-chevron-right fa-xs"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-chevron-right fa-xs"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: #6c757d;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .page-link {
        border: 1px solid #dee2e6;
        color: #6c757d;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .page-link:hover {
        color: #0d6efd;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .fa-xs {
        font-size: 0.75rem;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
    
    .card-body {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar barras de progresso
        document.querySelectorAll('.progress-dynamic').forEach(bar => {
            const width = bar.getAttribute('data-width');
            bar.style.width = width + '%';
        });

        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Filtro automático apenas para busca
        const searchInput = document.querySelector('input[name="search"]');
        const statusFilter = document.querySelector('select[name="status"]');
        const tipoPagamentoFilter = document.querySelector('select[name="tipo_pagamento"]');
        const filterForm = document.getElementById('filterForm');

        function aplicarFiltros() {
            filterForm.submit();
        }

        // Busca automática após parar de digitar (apenas para o campo de busca)
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(aplicarFiltros, 800);
            });
        }

        // Filtros por select (mudanças imediatas)
        if (statusFilter) statusFilter.addEventListener('change', aplicarFiltros);
        if (tipoPagamentoFilter) tipoPagamentoFilter.addEventListener('change', aplicarFiltros);

    });
</script>
@endpush