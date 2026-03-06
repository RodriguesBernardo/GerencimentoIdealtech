@extends('layouts.app')
@section('title', 'Visualizar Orçamento')

@section('content')
<div class="page-header fade-in">
    <div class="page-title-section">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('orcamentos.index') }}">Orçamentos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('orcamentos.pdf', $orcamento->id) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="fas fa-file-pdf"></i> Gerar PDF
        </a>
        <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('orcamentos.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row fade-in">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Informações do Cliente</span>
                @php
                    $statusClass = match($orcamento->status) {
                        'Aprovado' => 'status-pago',
                        'Rejeitado', 'Vencido' => 'status-nao-pago',
                        'Enviado' => 'status-pendente',
                        default => 'bg-secondary text-white'
                    };
                @endphp
                <span class="status-pagamento {{ $statusClass }}">{{ $orcamento->status }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nome do Cliente</div>
                    <div class="col-sm-8 fw-bold fs-5">{{ $orcamento->nome_cliente }}</div>
                </div>
                
                @if($orcamento->cliente_id)
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Tipo de Cadastro</div>
                        <div class="col-sm-8"><span class="badge bg-info text-dark">Cliente Registrado no Sistema</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">CPF/CNPJ</div>
                        <div class="col-sm-8">{{ $orcamento->cliente->cpf_cnpj ?? 'Não informado' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Contato</div>
                        <div class="col-sm-8">{{ $orcamento->cliente->celular ?? 'Não informado' }}</div>
                    </div>
                @else
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Tipo de Cadastro</div>
                        <div class="col-sm-8"><span class="badge bg-secondary">Cliente Avulso</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Contato</div>
                        <div class="col-sm-8">{{ $orcamento->cliente_contato_avulso ?? 'Não informado' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Produtos e Serviços</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="50%">Descrição</th>
                                <th width="15%" class="text-center">Quantidade</th>
                                <th width="15%" class="text-end">Vlr. Unitário</th>
                                <th width="15%" class="text-end pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orcamento->itens as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->descricao }}</div>
                                    @if($item->detalhes)
                                        <small class="text-muted">{!! nl2br(e($item->detalhes)) !!}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ rtrim(rtrim(number_format($item->quantidade, 2, ',', ''), '0'), ',') }}</td>
                                <td class="text-end">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold pe-4">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 border-info h-100">
                    <div class="card-header bg-transparent text-info fw-bold">Observações (Cliente)</div>
                    <div class="card-body rich-text-content">
                        @if($orcamento->observacoes)
                            {!! $orcamento->observacoes !!}
                        @else
                            <p class="text-muted mb-0 small">Nenhuma observação informada.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4 border-warning h-100">
                    <div class="card-header bg-transparent text-warning fw-bold">Notas Internas (Ocultas no PDF)</div>
                    <div class="card-body rich-text-content">
                        @if($orcamento->notas_internas)
                            {!! $orcamento->notas_internas !!}
                        @else
                            <p class="text-muted mb-0 small">Nenhuma nota interna registrada.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Condições Comerciais</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Data de Emissão</div>
                    <div class="fw-medium">{{ $orcamento->data_emissao->format('d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Validade</div>
                    <div class="fw-medium">{{ $orcamento->data_validade ? $orcamento->data_validade->format('d/m/Y') : 'Não definida' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Condições de Pagamento</div>
                    <div class="fw-medium rich-text-content">
                        @if($orcamento->condicoes_pagamento)
                            {!! $orcamento->condicoes_pagamento !!}
                        @else
                            Não definidas
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-muted small">Prazo de Entrega</div>
                    <div class="fw-medium">{{ $orcamento->prazo_entrega ?: 'Imediato' }}</div>
                </div>
            </div>
        </div>

        <div class="card border-primary">
            <div class="card-header bg-transparent fw-bold text-primary">Resumo Financeiro</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span>R$ {{ number_format($orcamento->subtotal, 2, ',', '.') }}</span>
                </div>
                
                @if($orcamento->desconto > 0)
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span>Desconto:</span>
                    <span>- R$ {{ number_format($orcamento->desconto, 2, ',', '.') }}</span>
                </div>
                @endif
                
                @if($orcamento->frete_acrescimos > 0)
                <div class="d-flex justify-content-between mb-3 text-info">
                    <span>Acréscimos:</span>
                    <span>+ R$ {{ number_format($orcamento->frete_acrescimos, 2, ',', '.') }}</span>
                </div>
                @endif
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="fs-5 text-muted">Total Final:</span>
                    <span class="fs-3 fw-bold text-success">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para o conteúdo vindo do Editor (Quill) */
    .rich-text-content p {
        margin-bottom: 0.5rem;
    }
    .rich-text-content p:last-child {
        margin-bottom: 0;
    }
    .rich-text-content ul, .rich-text-content ol {
        padding-left: 1.2rem;
        margin-bottom: 0.5rem;
    }
    .rich-text-content {
        font-size: 0.9rem;
    }
</style>
@endsection