@extends('layouts.app')

@section('title', 'Orçamentos')

@section('content')
    <div class="page-header fade-in">
        <div>
            @if (auth()->user()->is_admin || in_array('orcamentos.create', auth()->user()->permissoes ?? []))
                <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Orçamento
                </a>
            @endif
        </div>
    </div>


    @if (auth()->user()->is_admin)
        <div class="row mb-4 fade-in">
            <div class="col-md-4">
                <div class="card border-primary h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Total de Orçamentos</div>
                        <div class="fs-4 fw-bold text-primary">{{ $totalizador['quantidade'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Valor Total (Aprovados)</div>
                        <div class="fs-4 fw-bold text-success">R$
                            {{ number_format($totalizador['valor_aprovados'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-secondary h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Valor Total (Todos)</div>
                        <div class="fs-4 fw-bold text-secondary">R$
                            {{ number_format($totalizador['valor_geral'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4 fade-in">
        <div class="card-body">
            <form action="{{ route('orcamentos.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Buscar (Nome, CPF/CNPJ ou ID)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            value="{{ request('search') }}" placeholder="Digite para pesquisar...">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Filtrar por Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos os status</option>
                        <option value="Rascunho" {{ request('status') == 'Rascunho' ? 'selected' : '' }}>Rascunho</option>
                        <option value="Enviado" {{ request('status') == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                        <option value="Aprovado" {{ request('status') == 'Aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="Rejeitado" {{ request('status') == 'Rejeitado' ? 'selected' : '' }}>Rejeitado
                        </option>
                        <option value="Vencido" {{ request('status') == 'Vencido' ? 'selected' : '' }}>Vencido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>

            @if (request()->anyFilled(['search', 'status']))
                <div class="mt-2">
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-sm btn-outline-secondary">Limpar Filtros</a>
                </div>
            @endif
        </div>
    </div>

    <div class="card fade-in">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Cliente</th>
                            <th>Emissão</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th class="text-end">Valor Total</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orcamentos as $orcamento)
                            <tr>
                                <td class="ps-4"><strong>{{ str_pad($orcamento->id, 4, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $orcamento->nome_cliente }}</div>
                                    <small class="text-muted">
                                        {{ $orcamento->cliente_id ? 'Cliente Cadastrado' : 'Cliente Avulso' }}
                                    </small>
                                </td>
                                <td>{{ $orcamento->data_emissao->format('d/m/Y') }}</td>
                                <td>{{ $orcamento->data_validade ? $orcamento->data_validade->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($orcamento->status) {
                                            'Aprovado' => 'status-pago',
                                            'Rejeitado', 'Vencido' => 'status-nao-pago',
                                            'Enviado' => 'status-pendente',
                                            default => 'bg-secondary text-white',
                                        };
                                    @endphp
                                    <span class="status-pagamento status-pagamento-sm {{ $statusClass }}">
                                        {{ $orcamento->status }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group" role="group">

                                        @php
                                            $podeAprovar =
                                                (auth()->user()->is_admin ||
                                                    in_array('orcamentos.edit', auth()->user()->permissoes ?? [])) &&
                                                $orcamento->status !== 'Aprovado';
                                        @endphp
                                        <form action="{{ route('orcamentos.aprovar', $orcamento->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Confirmar aprovação deste orçamento?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-success {{ !$podeAprovar ? 'disabled opacity-25' : '' }}"
                                                title="{{ $podeAprovar ? 'Aprovar Orçamento' : 'Ação indisponível' }}"
                                                {{ !$podeAprovar ? 'disabled' : '' }}>
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        @php
                                            $podeCancelar =
                                                (auth()->user()->is_admin ||
                                                    in_array('orcamentos.edit', auth()->user()->permissoes ?? [])) &&
                                                !in_array($orcamento->status, ['Rejeitado', 'Aprovado']);
                                        @endphp
                                        <form action="{{ route('orcamentos.cancelar', $orcamento->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Deseja realmente cancelar/rejeitar este orçamento?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-warning {{ !$podeCancelar ? 'disabled opacity-25' : '' }}"
                                                title="{{ $podeCancelar ? 'Cancelar/Rejeitar Orçamento' : 'Ação indisponível' }}"
                                                {{ !$podeCancelar ? 'disabled' : '' }}>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>

                                        <a href="{{ route('orcamentos.pdf', $orcamento->id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary" title="Baixar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>

                                        @php $podeVer = auth()->user()->is_admin || in_array('orcamentos.view', auth()->user()->permissoes ?? []); @endphp
                                        <a href="{{ $podeVer ? route('orcamentos.show', $orcamento->id) : '#' }}"
                                            class="btn btn-sm btn-outline-info {{ !$podeVer ? 'disabled opacity-25' : '' }}"
                                            title="Visualizar" {{ !$podeVer ? 'disabled' : '' }}>
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @php $podeEditar = auth()->user()->is_admin || in_array('orcamentos.edit', auth()->user()->permissoes ?? []); @endphp
                                        <a href="{{ $podeEditar ? route('orcamentos.edit', $orcamento->id) : '#' }}"
                                            class="btn btn-sm btn-outline-primary {{ !$podeEditar ? 'disabled opacity-25' : '' }}"
                                            title="Editar" {{ !$podeEditar ? 'disabled' : '' }}>
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @php $podeExcluir = auth()->user()->is_admin || in_array('orcamentos.delete', auth()->user()->permissoes ?? []); @endphp
                                        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger {{ !$podeExcluir ? 'disabled opacity-25' : '' }}"
                                                title="Excluir" {{ !$podeExcluir ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fs-2 mb-3 d-block"></i>
                                    Nenhum orçamento encontrado com os filtros atuais.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orcamentos->hasPages())
            <div class="card-footer bg-transparent border-top">
                <div class="mt-2">
                    {{ $orcamentos->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
