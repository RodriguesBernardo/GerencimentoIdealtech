@extends('layouts.app')

@section('title', 'Serviços')

@section('header-actions')
<a href="{{ route('servicos.create') }}" class="btn btn-idealtech-blue">
    <i class="fas fa-plus me-2"></i>Novo Serviço
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Serviços</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Buscar por cliente, serviço..." id="searchInput">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="statusFilter">
                    <option value="">Todos os status</option>
                    <option value="pago">Pago</option>
                    <option value="pendente">Pendente</option>
                    <!-- <option value="nao_pago">Não Pago</option> -->
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="tipoPagamentoFilter">
                    <option value="">Todos os tipos</option>
                    <option value="avista">À Vista</option>
                    <option value="parcelado">Parcelado</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
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
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong class="d-block">{{ $servico->nome }}</strong>
                            <small class="text-muted">{{ Str::limit($servico->descricao, 50) }}</small>
                        </td>
                        <td>
                            @if(auth()->user()->podeVerValoresCompletos())
                                <span class="fw-bold text-success">R$ {{ number_format($servico->valor, 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">***</span>
                            @endif
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
                                <a href="{{ route('servicos.show', $servico) }}" class="btn btn-outline-primary" 
                                   data-bs-toggle="tooltip" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-outline-secondary"
                                   data-bs-toggle="tooltip" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('servicos.destroy', $servico) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            data-bs-toggle="tooltip" title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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

        // Filtros
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tipoPagamentoFilter = document.getElementById('tipoPagamentoFilter');

        function aplicarFiltros() {
            const search = searchInput.value.toLowerCase();
            const status = statusFilter.value;
            const tipo = tipoPagamentoFilter.value;

            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (row.querySelector('.text-center')) return; // Pular linha vazia

                const text = row.textContent.toLowerCase();
                const rowStatus = row.querySelectorAll('.badge')[1]?.textContent.trim().toLowerCase() || '';
                const rowTipo = row.querySelectorAll('.badge')[0]?.textContent.toLowerCase() || '';

                const matchSearch = text.includes(search);
                const matchStatus = !status || rowStatus.includes(status);
                const matchTipo = !tipo || rowTipo.includes(tipo);

                row.style.display = matchSearch && matchStatus && matchTipo ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', aplicarFiltros);
        statusFilter.addEventListener('change', aplicarFiltros);
        tipoPagamentoFilter.addEventListener('change', aplicarFiltros);

        // Aplicar filtros inicialmente se houver valores
        aplicarFiltros();
    });
</script>
@endpush