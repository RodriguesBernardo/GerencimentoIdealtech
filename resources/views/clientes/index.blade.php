@extends('layouts.app')

@section('title', 'Clientes')

@section('header-actions')
    <a href="{{ route('clientes.create') }}" class="btn btn-idealtech-blue">
        <i class="fas fa-plus me-2"></i>Novo Cliente
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Lista de Clientes</h5>
            <div class="search-box">
                <form action="{{ route('clientes.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group" style="min-width: 300px;">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Buscar por nome..." 
                               value="{{ request('search') }}"
                               aria-label="Buscar cliente">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(request('search') && $clientes->count() > 0)
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Mostrando resultados para: "<strong>{{ request('search') }}</strong>"
                <a href="{{ route('clientes.index') }}" class="float-end text-decoration-none">
                    <small>Limpar busca</small>
                </a>
            </div>
        @endif

        @if(request('search') && $clientes->count() === 0)
            <div class="alert alert-warning mb-3">
                <i class="fas fa-search me-2"></i>
                Nenhum cliente encontrado para: "<strong>{{ request('search') }}</strong>"
                <a href="{{ route('clientes.index') }}" class="float-end text-decoration-none">
                    <small>Ver todos os clientes</small>
                </a>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF/CNPJ</th>
                        <th>Contato</th>
                        <th>Total Serviços</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nome }}</td>
                        <td>{{ $cliente->cpf_cnpj ?? '-' }}</td>
                        <td>{{ $cliente->celular ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $cliente->servicos_count ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum cliente cadastrado</p>
                            @if(!request('search'))
                                <a href="{{ route('clientes.create') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-2"></i>Cadastrar Primeiro Cliente
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($clientes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $clientes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<style>
/* Corrige o tamanho das setas da paginação */
.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    padding: 0.375rem 0.75rem;
}

/* Remove qualquer estilo que esteja fazendo as setas ficarem gigantes */
.pagination .page-link i {
    font-size: 0.875rem;
}

/* Garante que a paginação tenha o estilo padrão do Bootstrap */
.pagination {
    --bs-pagination-padding-x: 0.75rem;
    --bs-pagination-padding-y: 0.375rem;
    --bs-pagination-font-size: 0.875rem;
    --bs-pagination-color: var(--bs-link-color);
    --bs-pagination-bg: var(--bs-body-bg);
    --bs-pagination-border-width: var(--bs-border-width);
    --bs-pagination-border-color: var(--bs-border-color);
    --bs-pagination-border-radius: var(--bs-border-radius);
    --bs-pagination-hover-color: var(--bs-link-hover-color);
    --bs-pagination-hover-bg: var(--bs-tertiary-bg);
    --bs-pagination-hover-border-color: var(--bs-border-color);
    --bs-pagination-focus-color: var(--bs-link-hover-color);
    --bs-pagination-focus-bg: var(--bs-secondary-bg);
    --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    --bs-pagination-active-color: #fff;
    --bs-pagination-active-bg: #0d6efd;
    --bs-pagination-active-border-color: #0d6efd;
    --bs-pagination-disabled-color: var(--bs-secondary-color);
    --bs-pagination-disabled-bg: var(--bs-secondary-bg);
    --bs-pagination-disabled-border-color: var(--bs-border-color);
    display: flex;
    padding-left: 0;
    list-style: none;
}

/* Estilo para a busca */
.search-box .input-group {
    box-shadow: var(--shadow-sm);
    border-radius: 8px;
}

.search-box .form-control {
    border-radius: 8px 0 0 8px;
}

.search-box .btn {
    border-radius: 0 8px 8px 0;
}
</style>
@endsection