@extends('layouts.app')

@section('title', $cliente->nome)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">{{ $cliente->nome }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Informações do Cliente -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Nome:</strong>
                    <p class="mb-1">{{ $cliente->nome }}</p>
                </div>

                @if($cliente->cpf_cnpj)
                <div class="mb-3">
                    <strong>CPF/CNPJ:</strong>
                    <p class="mb-1">{{ $cliente->cpf_cnpj }}</p>
                </div>
                @endif

                @if($cliente->celular)
                <div class="mb-3">
                    <strong>Celular:</strong>
                    <p class="mb-1">
                        <a href="https://wa.me/55{{ preg_replace('/\D/', '', $cliente->celular) }}" 
                           target="_blank" class="text-decoration-none">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            {{ $cliente->celular }}
                        </a>
                    </p>
                </div>
                @endif


                @if($cliente->observacoes)
                <div class="mb-3">
                    <strong>Observações:</strong>
                    <p class="mb-1">{{ $cliente->observacoes }}</p>
                </div>
                @endif

                <div class="mb-3">
                    <strong>Total de Serviços:</strong>
                    <p class="mb-1">
                        <span class="badge bg-primary">{{ $cliente->servicos_count ?? $cliente->servicos()->count() }}</span>
                    </p>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                            <i class="fas fa-trash me-1"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Serviços -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Serviços Realizados</h5>
                <a href="{{ route('servicos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-idealtech-blue btn-sm">
                    <i class="fas fa-plus me-1"></i>Novo Serviço
                </a>
            </div>
            <div class="card-body">
                @if($servicos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servicos as $servico)
                                <tr>
                                    <td>
                                        <a href="{{ route('servicos.show', $servico) }}" class="text-decoration-none">
                                            {{ Str::limit($servico->descricao, 50) }}
                                        </a>
                                    </td>
                                    <td>{{ $servico->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if($servico->status == 'pendente')
                                            <span class="badge bg-warning">Pendente</span>
                                        @elseif($servico->status == 'em_andamento')
                                            <span class="badge bg-info">Em Andamento</span>
                                        @elseif($servico->status == 'concluido')
                                            <span class="badge bg-success">Concluído</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $servico->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($servico->valor)
                                            R$ {{ number_format($servico->valor, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('servicos.show', $servico) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $servicos->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Nenhum serviço cadastrado para este cliente</p>
                        <a href="{{ route('servicos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-idealtech-blue">
                            <i class="fas fa-plus me-2"></i>Cadastrar Primeiro Serviço
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    padding: 0.375rem 0.75rem;
}

.pagination .page-link i {
    font-size: 0.875rem;
}

/* Estilo para links do WhatsApp */
a[href*="wa.me"]:hover {
    text-decoration: underline !important;
}
</style>
@endsection