@extends('layouts.app')

@section('title', 'Editar Serviço')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.index') }}">Serviços</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.show', $servico) }}">{{ $servico->nome }}</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Serviço: {{ $servico->nome }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('servicos.update', $servico) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-control" id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $servico->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Serviço *</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $servico->nome) }}" required>
                        @error('nome')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição do Serviço *</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required>{{ old('descricao', $servico->descricao) }}</textarea>
                @error('descricao')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_servico" class="form-label">Data do Serviço *</label>
                        <input type="date" class="form-control" id="data_servico" name="data_servico" value="{{ old('data_servico', $servico->data_servico->format('Y-m-d')) }}" required>
                        @error('data_servico')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="number" class="form-control" id="valor" name="valor" value="{{ old('valor', $servico->valor) }}" step="0.01" min="0">
                        @error('valor')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tipo_pagamento" class="form-label">Tipo de Pagamento *</label>
                        <select class="form-control" id="tipo_pagamento" name="tipo_pagamento" required>
                            <option value="avista" {{ old('tipo_pagamento', $servico->tipo_pagamento) == 'avista' ? 'selected' : '' }}>À Vista</option>
                            <option value="parcelado" {{ old('tipo_pagamento', $servico->tipo_pagamento) == 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                        </select>
                        @error('tipo_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Campos de Parcelamento -->
            @php
            $displayParcelamento = $servico->tipo_pagamento == 'parcelado' ? 'block' : 'none';
            $dataPrimeiroVencimento = $servico->parcelasServico->isNotEmpty() ? $servico->parcelasServico->first()->data_vencimento->format('Y-m-d') : date('Y-m-d');
            @endphp
            <div class="row" id="parcelamento_fields">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parcelas" class="form-label">Número de Parcelas *</label>
                        <input type="number" class="form-control" id="parcelas" name="parcelas"
                            value="{{ old('parcelas', $servico->parcelas) }}" min="2" max="24">
                        @error('parcelas')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_primeiro_vencimento" class="form-label">Data do Primeiro Vencimento</label>
                        <input type="date" class="form-control" id="data_primeiro_vencimento" name="data_primeiro_vencimento"
                            value="{{ old('data_primeiro_vencimento', $dataPrimeiroVencimento) }}">
                        @error('data_primeiro_vencimento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="alert alert-info" id="parcela_info">
                        @if($servico->tipo_pagamento == 'parcelado' && $servico->parcelas > 1)
                        <strong>Resumo das Parcelas:</strong><br>
                        • Total: R$ {{ number_format($servico->valor, 2, ',', '.') }}<br>
                        • {{ $servico->parcelas }} parcelas de R$ {{ number_format($servico->valor / $servico->parcelas, 2, ',', '.') }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status_pagamento" class="form-label">Status do Pagamento *</label>
                        <select class="form-control" id="status_pagamento" name="status_pagamento" required>
                            <option value="pendente" {{ old('status_pagamento', $servico->status_pagamento) == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ old('status_pagamento', $servico->status_pagamento) == 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="nao_pago" {{ old('status_pagamento', $servico->status_pagamento) == 'nao_pago' ? 'selected' : '' }}>Não Pago</option>
                        </select>
                        @error('status_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="pago_at" class="form-label">Data do Pagamento</label>
                        <input type="datetime-local" class="form-control" id="pago_at" name="pago_at"
                            value="{{ old('pago_at', $servico->pago_at ? $servico->pago_at->format('Y-m-d\TH:i') : '') }}"
                            {{ $servico->status_pagamento !== 'pago' ? 'disabled' : '' }}>
                        @error('pago_at')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Preencha apenas se o serviço foi pago</div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="observacao_pagamento" class="form-label">Observação do Pagamento</label>
                <textarea class="form-control" id="observacao_pagamento" name="observacao_pagamento" rows="2"
                    placeholder="Ex: Boleto 30 dias, Pix, Cartão, Cheque...">{{ old('observacao_pagamento', $servico->observacao_pagamento) }}</textarea>
                @error('observacao_pagamento')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações Gerais</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $servico->observacoes) }}</textarea>
                @error('observacoes')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-idealtech-blue">
                    <i class="fas fa-save me-2"></i>Atualizar Serviço
                </button>
                <a href="{{ route('servicos.show', $servico) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <a href="{{ route('servicos.show', $servico) }}" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>Ver Detalhes
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusPagamento = document.getElementById('status_pagamento');
        const pagoAt = document.getElementById('pago_at');
        const tipoPagamento = document.getElementById('tipo_pagamento');
        const parcelamentoFields = document.getElementById('parcelamento_fields');
        const parcelasInput = document.getElementById('parcelas');
        const valorInput = document.getElementById('valor');
        const parcelaInfo = document.getElementById('parcela_info');

        function togglePagoAtField() {
            if (statusPagamento.value === 'pago') {
                pagoAt.disabled = false;
                if (!pagoAt.value) {
                    const now = new Date();
                    pagoAt.value = now.toISOString().slice(0, 16);
                }
            } else {
                pagoAt.disabled = true;
                pagoAt.value = '';
            }
        }

        function toggleParcelamentoFields() {
            if (tipoPagamento.value === 'parcelado') {
                parcelamentoFields.style.display = 'block';
                calcularParcelas();
            } else {
                parcelamentoFields.style.display = 'none';
                parcelaInfo.innerHTML = '';
            }
        }

        function calcularParcelas() {
            const valor = parseFloat(valorInput.value) || 0;
            const numParcelas = parseInt(parcelasInput.value) || 2;

            if (valor > 0 && numParcelas > 1) {
                const valorParcela = valor / numParcelas;
                parcelaInfo.innerHTML = `
                <strong>Resumo das Parcelas:</strong><br>
                • Total: R$ ${valor.toFixed(2).replace('.', ',')}<br>
                • ${numParcelas} parcelas de R$ ${valorParcela.toFixed(2).replace('.', ',')}<br>
                • Primeira parcela vence em: ${document.getElementById('data_primeiro_vencimento').value}
            `;
            } else {
                parcelaInfo.innerHTML = 'Informe o valor total e número de parcelas para ver o resumo.';
            }
        }

        // Inicializa os campos
        togglePagoAtField();
        toggleParcelamentoFields();

        // Event listeners
        statusPagamento.addEventListener('change', togglePagoAtField);
        tipoPagamento.addEventListener('change', toggleParcelamentoFields);
        valorInput.addEventListener('input', calcularParcelas);
        parcelasInput.addEventListener('input', calcularParcelas);

        const dataVencimentoInput = document.getElementById('data_primeiro_vencimento');
        if (dataVencimentoInput) {
            dataVencimentoInput.addEventListener('change', calcularParcelas);
        }
    });
</script>
@endpush