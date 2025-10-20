@extends('layouts.app')

@section('title', $servico->cliente->nome)

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

        <!-- Parcelas -->
        @if($servico->tipo_pagamento == 'parcelado' && $servico->parcelas > 1)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Gestão de Parcelas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Parcela</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th>Data Pagamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servico->parcelasServico->sortBy('numero_parcela') as $parcela)
                            <tr class="{{ $parcela->estaAtrasada() ? 'table-danger' : '' }}">
                                <td>{{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }}</td>
                                <td>R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}</td>
                                <td>{{ $parcela->data_vencimento->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($parcela->status == 'paga') badge-pago
                                        @elseif($parcela->status == 'atrasada') badge-nao-pago
                                        @else badge-pendente @endif">
                                        {{ $parcela->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($parcela->data_pagamento)
                                        {{ $parcela->data_pagamento->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($parcela->status != 'paga')
                                        <form action="{{ route('parcelas.marcar-paga', $parcela) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" 
                                                    onclick="return confirm('Marcar parcela {{ $parcela->numero_parcela }} como paga?')"
                                                    title="Marcar como Paga">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($parcela->status == 'paga')
                                        <form action="{{ route('parcelas.marcar-pendente', $parcela) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success" 
                                                    onclick="return confirm('Marcar parcela {{ $parcela->numero_parcela }} como pendente?')"
                                                    title="Marcar como Pendente">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Resumo das Parcelas -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Pago</h6>
                                <h4 class="text-success">R$ {{ number_format($servico->total_pago, 2, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Pendente</h6>
                                <h4 class="text-warning">R$ {{ number_format($servico->total_pendente, 2, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Progresso</h6>
                                <h4>{{ $servico->parcelas_pagas }}/{{ $servico->total_parcelas }}</h4>
                                @php
                                    $progresso = $servico->total_parcelas > 0 ? ($servico->parcelas_pagas / $servico->total_parcelas) * 100 : 0;
                                @endphp
                                <small>{{ number_format($progresso, 0) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Anexos -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Anexos ({{ $servico->anexos->count() }}/5)</h5>
                @if($servico->anexos->count() < 5)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adicionarAnexoModal">
                    <i class="fas fa-plus me-1"></i>Adicionar Anexo
                </button>
                @else
                <span class="badge bg-secondary">Limite atingido</span>
                @endif
            </div>
            
            @if($servico->anexos->count() > 0)
            <div class="card-body">
                <div class="row">
                    @foreach($servico->anexos as $anexo)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        @if($anexo->isImage())
                                        <i class="fas fa-image fa-2x text-primary"></i>
                                        @elseif(strpos($anexo->mime_type, 'pdf') !== false)
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        @elseif(strpos($anexo->mime_type, 'word') !== false || strpos($anexo->mime_type, 'document') !== false)
                                        <i class="fas fa-file-word fa-2x text-primary"></i>
                                        @elseif(strpos($anexo->mime_type, 'excel') !== false || strpos($anexo->mime_type, 'spreadsheet') !== false)
                                        <i class="fas fa-file-excel fa-2x text-success"></i>
                                        @else
                                        <i class="fas fa-file fa-2x text-secondary"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="card-title">{{ $anexo->nome_arquivo }}</h6>
                                        @if($anexo->descricao)
                                        <p class="card-text small">{{ $anexo->descricao }}</p>
                                        @endif
                                        <p class="card-text small text-muted">
                                            {{ $anexo->tamanho_formatado }} • 
                                            {{ $anexo->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100">
                                    <a href="{{ route('servicos.anexos.download', [$servico, $anexo]) }}" 
                                    class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    @if(auth()->user()->is_admin)
                                    <form action="{{ route('servicos.anexos.destroy', [$servico, $anexo]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('Tem certeza que deseja excluir este anexo?')">
                                            <i class="fas fa-trash me-1"></i>Excluir
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card-body text-center py-5">
                <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Nenhum anexo adicionado ainda.</p>
                <p class="text-muted">Clique no botão "Adicionar Anexo" para enviar arquivos.</p>
            </div>
            @endif
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
                            <!-- <option value="nao_pago" {{ $servico->status_pagamento == 'nao_pago' ? 'selected' : '' }}>Não Pago</option> -->
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

<!-- Modal para Adicionar Anexo -->
<div class="modal fade" id="adicionarAnexoModal" tabindex="-1" aria-labelledby="adicionarAnexoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adicionarAnexoModalLabel">Adicionar Anexo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('servicos.anexos.store', $servico) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="anexo" class="form-label">Arquivo *</label>
                        <input type="file" class="form-control" id="anexo" name="anexo" accept="*" required>
                        <div class="form-text">
                            Formatos aceitos: todos os tipos de arquivo. Tamanho máximo: 10MB.
                            Você pode adicionar até {{ 5 - $servico->anexos->count() }} anexo(s) restante(s).
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição (opcional)</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição do arquivo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Enviar Anexo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge-pago {
        background-color: #28a745;
        color: white;
    }
    .badge-pendente {
        background-color: #ffc107;
        color: black;
    }
    .badge-nao-pago {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush