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
                    <strong>Nome/Razão Social:</strong>
                    <p class="mb-1">{{ $cliente->nome }}</p>
                </div>

                @if($cliente->cpf_cnpj)
                <div class="mb-3">
                    <strong>CPF/CNPJ:</strong>
                    <p class="mb-1">{{ $cliente->cpf_cnpj }}</p>
                </div>
                @endif

                @if($cliente->email)
                <div class="mb-3">
                    <strong>E-mail:</strong>
                    <p class="mb-1">
                        <a href="mailto:{{ $cliente->email }}" class="text-decoration-none">
                            <i class="fas fa-envelope text-primary me-1"></i>
                            {{ $cliente->email }}
                        </a>
                    </p>
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

                <!-- Seção de Endereço -->
                @if($cliente->cep || $cliente->logradouro || $cliente->cidade)
                <div class="mb-3">
                    <strong>Endereço:</strong>
                    <p class="mb-1">
                        @if($cliente->enderecoCompleto)
                            {{ $cliente->enderecoCompleto }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                @endif

                @if($cliente->observacoes)
                <div class="mb-3">
                    <strong>Observações:</strong>
                    <p class="mb-1">{{ $cliente->observacoes }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <strong>Total de Serviços:</strong>
                            <p class="mb-1">
                                <span class="badge bg-primary">{{ $cliente->servicos_count ?? $cliente->servicos()->count() }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <a href="{{ route('servicos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-idealtech-blue btn-sm">
                        <i class="fas fa-plus me-1"></i>Novo Serviço
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

        <!-- Detalhes do Endereço -->
        @if($cliente->cep || $cliente->logradouro)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Detalhes do Endereço</h6>
            </div>
            <div class="card-body">
                @if($cliente->cep)
                <div class="mb-2">
                    <strong>CEP:</strong>
                    <span class="ms-1">{{ $cliente->cep }}</span>
                </div>
                @endif

                @if($cliente->logradouro)
                <div class="mb-2">
                    <strong>Rua:</strong>
                    <span class="ms-1">{{ $cliente->logradouro }}</span>
                </div>
                @endif

                @if($cliente->numero)
                <div class="mb-2">
                    <strong>Número:</strong>
                    <span class="ms-1">{{ $cliente->numero }}</span>
                </div>
                @endif

                @if($cliente->complemento)
                <div class="mb-2">
                    <strong>Complemento:</strong>
                    <span class="ms-1">{{ $cliente->complemento }}</span>
                </div>
                @endif

                @if($cliente->bairro)
                <div class="mb-2">
                    <strong>Bairro:</strong>
                    <span class="ms-1">{{ $cliente->bairro }}</span>
                </div>
                @endif

                @if($cliente->cidade)
                <div class="mb-2">
                    <strong>Cidade/UF:</strong>
                    <span class="ms-1">{{ $cliente->cidade }}{{ $cliente->uf ? '/' . $cliente->uf : '' }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Lista de Serviços -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Serviços Realizados</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('servicos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-idealtech-blue btn-sm">
                        <i class="fas fa-plus me-1"></i>Novo Serviço
                    </a>
                </div>
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
                                        @if($servico->observacoes)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                {{ Str::limit($servico->observacoes, 30) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $servico->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if($servico->status == 'Pendente')
                                            <span class="badge bg-warning">Pendente</span>
                                        @elseif($servico->status == 'Em Andamento')
                                            <span class="badge bg-info">Em Andamento</span>
                                        @elseif($servico->status == 'Concluído')
                                            <span class="badge bg-success">Concluído</span>
                                        @elseif($servico->status == 'Pago')
                                            <span class="badge bg-success">Pago</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $servico->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($servico->valor)
                                            <strong>R$ {{ number_format($servico->valor, 2, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('servicos.show', $servico) }}" class="btn btn-outline-primary" 
                                               title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-outline-secondary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($servico->status !== 'Pago')
                                            <a href="{{ route('servicos.pagar', $servico) }}" class="btn btn-outline-success"
                                               title="Marcar como pago"
                                               onclick="return confirm('Deseja marcar este serviço como pago?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Mostrando {{ $servicos->firstItem() }} a {{ $servicos->lastItem() }} de {{ $servicos->total() }} serviços
                        </div>
                        <div>
                            {{ $servicos->links('pagination::bootstrap-5') }}
                        </div>
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

        <!-- Resumo Financeiro -->
        @if($servicos->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Resumo Financeiro</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-1">Total Geral</h6>
                            <h5 class="text-primary mb-0">
                                R$ {{ number_format($servicos->sum('valor'), 2, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-1">Pagos</h6>
                            <h5 class="text-success mb-0">
                                R$ {{ number_format($servicos->where('status', 'Pago')->sum('valor'), 2, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-1">Pendentes</h6>
                            <h5 class="text-warning mb-0">
                                R$ {{ number_format($servicos->where('status', '!=', 'Pago')->sum('valor'), 2, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-1">Serviços</h6>
                            <h5 class="text-info mb-0">
                                {{ $servicos->count() }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
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

/* Estilo para links do WhatsApp e Email */
a[href*="wa.me"]:hover,
a[href^="mailto"]:hover {
    text-decoration: underline !important;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Cards de resumo financeiro */
.border.rounded {
    border-color: #e9ecef !important;
}
</style>
@endsection