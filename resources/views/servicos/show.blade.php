@extends('layouts.app')

@section('title', $servico->nome)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('servicos.index') }}">Serviços</a></li>
    <li class="breadcrumb-item active">{{ $servico->nome }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalhes do Serviço</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Cliente:</strong>
                            <p class="mb-1">
                                <a href="{{ route('clientes.show', $servico->cliente) }}" class="text-decoration-none">
                                    {{ $servico->cliente->nome }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Data do Serviço:</strong>
                            <p class="mb-1">{{ $servico->data_servico->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Nome do Serviço:</strong>
                    <p class="mb-1">{{ $servico->nome }}</p>
                </div>

                <div class="mb-3">
                    <strong>Descrição:</strong>
                    <p class="mb-1">{{ $servico->descricao }}</p>
                </div>

                @if($servico->valor)
                <div class="mb-3">
                    <strong>Valor:</strong>
                    <p class="mb-1"><strong>R$ {{ number_format($servico->valor, 2, ',', '.') }}</strong></p>
                </div>
                @endif

                @if($servico->observacoes)
                <div class="mb-3">
                    <strong>Observações Gerais:</strong>
                    <p class="mb-1">{{ $servico->observacoes }}</p>
                </div>
                @endif

                <div class="mb-3">
                    <strong>Data de Cadastro:</strong>
                    <p class="mb-1">{{ $servico->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Status do Pagamento -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status do Pagamento</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($servico->status_pagamento == 'pago')
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h4 class="text-success">PAGO</h4>
                    @elseif($servico->status_pagamento == 'pendente')
                        <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                        <h4 class="text-warning">PENDENTE</h4>
                    @else
                        <i class="fas fa-times-circle fa-3x text-danger mb-2"></i>
                        <h4 class="text-danger">NÃO PAGO</h4>
                    @endif
                </div>

                @if($servico->observacao_pagamento)
                <div class="mb-3">
                    <strong>Observação:</strong>
                    <p class="mb-1">{{ $servico->observacao_pagamento }}</p>
                </div>
                @endif

                <!-- Form para alterar status -->
                <form action="{{ route('servicos.update-payment-status', $servico) }}" method="POST" class="mt-3">
                    @csrf
                    <div class="mb-3">
                        <label for="status_pagamento" class="form-label">Alterar Status:</label>
                        <select class="form-control" id="status_pagamento" name="status_pagamento" required>
                            <option value="pendente" {{ $servico->status_pagamento == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ $servico->status_pagamento == 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="nao_pago" {{ $servico->status_pagamento == 'nao_pago' ? 'selected' : '' }}>Não Pago</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacao_pagamento" class="form-label">Observação:</label>
                        <textarea class="form-control" id="observacao_pagamento" name="observacao_pagamento" rows="2" 
                                  placeholder="Ex: Boleto 30 dias, Pix...">{{ $servico->observacao_pagamento }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-idealtech-blue w-100">
                        <i class="fas fa-sync-alt me-2"></i>Atualizar Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Ações -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Editar Serviço
                    </a>
                    <form action="{{ route('servicos.destroy', $servico) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" 
                                onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                            <i class="fas fa-trash me-2"></i>Excluir Serviço
                        </button>
                    </form>
                    <a href="{{ route('servicos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection