@extends('layouts.app')

@section('title', 'Editar Serviço')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.index') }}">Serviços</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.show', $servico) }}">{{ $servico->descricao }}</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Serviço: {{ $servico->descricao }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('servicos.update', $servico) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-control select2-cliente" id="cliente_id" name="cliente_id" required>
                            @if($servico->cliente_id && $servico->cliente)
                                <option value="{{ $servico->cliente_id }}" selected>
                                    {{ $servico->cliente->nome }}
                                    @if($servico->cliente->cpf_cnpj)
                                        - {{ $servico->cliente->cpf_cnpj }}
                                    @endif
                                </option>
                            @endif
                        </select>
                        @error('cliente_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição do Serviço *</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao', $servico->descricao) }}" required>
                        @error('descricao')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_servico" class="form-label">Data do Serviço *</label>
                        <input type="date" class="form-control" id="data_servico" name="data_servico"
                            value="{{ old('data_servico', $servico->data_servico->format('Y-m-d')) }}" required>
                        @error('data_servico')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="number" class="form-control" id="valor" name="valor"
                            value="{{ old('valor', $servico->valor) }}" step="0.01" min="0" placeholder="0,00">
                        @error('valor')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status_pagamento" class="form-label">Status do Pagamento *</label>
                        <select class="form-control" id="status_pagamento" name="status_pagamento" required>
                            <option value="">Selecione o status</option>
                            <option value="pendente" {{ old('status_pagamento', $servico->status_pagamento) == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ old('status_pagamento', $servico->status_pagamento) == 'pago' ? 'selected' : '' }}>Pago</option>
                        </select>
                        @error('status_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Campos de Parcelamento -->
            @php
                $displayParcelamento = $servico->tipo_pagamento == 'parcelado' ? 'block' : 'none';
                $dataPrimeiroVencimento = $servico->parcelasServico->isNotEmpty() ? $servico->parcelasServico->first()->data_vencimento->format('Y-m-d') : date('Y-m-d');
                
                // Prepara as datas existentes das parcelas para o JavaScript
                $datasExistentes = [];
                foreach ($servico->parcelasServico as $parcela) {
                    $datasExistentes[$parcela->numero_parcela] = $parcela->data_vencimento->format('Y-m-d');
                }
            @endphp
            <div class="row" id="parcelamento_fields" style="display: {{ $displayParcelamento }};">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parcelas" class="form-label">Número de Parcelas *</label>
                        <input type="number" class="form-control" id="parcelas" name="parcelas"
                            value="{{ old('parcelas', $servico->parcelas) }}" 
                            min="1" max="24" {{ $servico->tipo_pagamento == 'avista' ? 'readonly' : '' }}>
                        @error('parcelas')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_primeiro_vencimento" class="form-label">Data do Primeiro Vencimento</label>
                        <input type="date" class="form-control" id="data_primeiro_vencimento" name="data_primeiro_vencimento"
                            value="{{ old('data_primeiro_vencimento', $dataPrimeiroVencimento) }}"
                            {{ $servico->tipo_pagamento == 'avista' ? 'disabled' : '' }}>
                        @error('data_primeiro_vencimento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Container para as datas individuais das parcelas -->
                <div class="col-12" id="datas_parcelas_container" style="display: {{ $servico->parcelas > 1 ? 'block' : 'none' }};">
                    <div class="mb-3">
                        <label class="form-label">Datas de Vencimento das Parcelas</label>
                        <div id="datas_parcelas_fields" class="row">
                            <!-- As datas das parcelas serão geradas aqui via JavaScript -->
                            @for($i = 2; $i <= $servico->parcelas; $i++)
                                <div class="col-md-4 mb-2">
                                    <label for="datas_parcelas_{{ $i }}" class="form-label small">Parcela {{ $i }}</label>
                                    <input type="date" class="form-control form-control-sm" 
                                           id="datas_parcelas_{{ $i }}" 
                                           name="datas_parcelas[{{ $i }}]" 
                                           value="{{ old("datas_parcelas.$i", $datasExistentes[$i] ?? '') }}"
                                           {{ $servico->tipo_pagamento == 'avista' ? 'disabled' : '' }}>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mt-3" id="parcela_info">
                        Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.
                    </div>
                </div>
            </div>

            <div class="row">
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
                    placeholder="Ex: Boleto 30 dias, Pix, Cartão, Cheque, Aguardando pagamento...">{{ old('observacao_pagamento', $servico->observacao_pagamento) }}</textarea>
                @error('observacao_pagamento')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações Gerais</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                    placeholder="Observações adicionais sobre o serviço">{{ old('observacoes', $servico->observacoes) }}</textarea>
                @error('observacoes')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Seção de Anexos Existentes -->
            @if($servico->anexos->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Anexos Existentes</h6>
                </div>
                <div class="card-body">
                    @foreach($servico->anexos as $anexo)
                    <div class="anexo-existente mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-md-1">
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
                            <div class="col-md-6">
                                <strong>{{ $anexo->nome_arquivo }}</strong>
                                @if($anexo->descricao)
                                <br><small class="text-muted">{{ $anexo->descricao }}</small>
                                @endif
                                <br><small class="text-muted">{{ $anexo->tamanho_formatado }} • {{ $anexo->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="col-md-5 text-end">
                                <a href="{{ route('servicos.anexos.download', [$servico, $anexo]) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                                <form action="{{ route('servicos.anexos.destroy', [$servico, $anexo]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Tem certeza que deseja excluir este anexo?')">
                                        <i class="fas fa-trash me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Seção para Adicionar Novos Anexos -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Adicionar Novos Anexos (Máximo: 5 arquivos no total)</h6>
                </div>
                <div class="card-body">
                    <div id="anexos-container">
                        <div class="anexo-item mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Arquivo</label>
                                    <input type="file" class="form-control" name="anexos[]">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Descrição (opcional)</label>
                                    <input type="text" class="form-control" name="descricoes_anexos[]" placeholder="Descrição do arquivo">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-remover-anexo" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-adicionar-anexo">
                        <i class="fas fa-plus me-1"></i>Adicionar outro arquivo
                    </button>
                    
                    <div class="form-text">
                        Formatos aceitos: todos os tipos de arquivo. Tamanho máximo por arquivo: 10MB.
                        Você já possui {{ $servico->anexos->count() }} de 5 anexos.
                    </div>
                </div>
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
$(document).ready(function() {
    // Verifica se há um cliente selecionado
    const selectedClienteId = $('.select2-cliente').val();
    const selectedClienteText = $('.select2-cliente').find('option:selected').text();
    
    // Inicializa o Select2
    $('.select2-cliente').select2({
        theme: 'bootstrap-5',
        language: 'pt-BR',
        placeholder: 'Digite o nome ou CPF/CNPJ do cliente...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route('clientes.search-ajax') }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.data,
                    pagination: {
                        more: (params.page * 10) < data.total
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: function (cliente) {
            if (cliente.loading) {
                return cliente.text;
            }

            var $container = $(
                '<div class="select2-client-result">' +
                    '<div class="client-name"><strong>' + cliente.nome + '</strong></div>' +
                    (cliente.cpf_cnpj ? '<div class="client-document text-muted small">' + cliente.cpf_cnpj + '</div>' : '') +
                    (cliente.celular ? '<div class="client-phone text-muted small">' + cliente.celular + '</div>' : '') +
                '</div>'
            );

            return $container;
        },
        templateSelection: function (cliente) {
            if (cliente.id === '') {
                return cliente.text;
            }

            if (cliente.nome) {
                return cliente.nome;
            }
            // Se for da opção HTML inicial, extrai apenas o nome
            else if (cliente.text && cliente.text.includes(' - ')) {
                return cliente.text.split(' - ')[0];
            }
            else {
                return cliente.text;
            }
        }
    });

    setTimeout(function() {
        const option = $('.select2-cliente').find('option:selected');
        if (option.length > 0 && option.text().includes(' - ')) {
            const apenasNome = option.text().split(' - ')[0];
            option.text(apenasNome);
            $('.select2-cliente').trigger('change.select2');
        }
    }, 100);

    setTimeout(function() {
        if (selectedClienteId && !$('.select2-cliente').select2('data')[0]) {
            $.ajax({
                url: '{{ route('clientes.search-ajax') }}',
                data: {
                    specific_id: selectedClienteId
                },
                dataType: 'json',
                success: function (data) {
                    if (data.data && data.data.length > 0) {
                        const cliente = data.data[0];
                        const option = $('.select2-cliente').find('option[value="' + selectedClienteId + '"]');
                        if (option.length > 0) {
                            option.text(cliente.nome);
                            option.attr('data-nome', cliente.nome);
                            option.attr('data-cpf_cnpj', cliente.cpf_cnpj);
                            $('.select2-cliente').val(selectedClienteId).trigger('change.select2');
                            console.log('Cliente carregado com sucesso - Nome:', cliente.nome);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao buscar cliente:', error);
                }
            });
        }
    }, 500);

    // Limpar seleção quando o usuário clicar no "x"
    $('.select2-cliente').on('select2:unselecting', function() {
        $(this).data('unselecting', true);
    }).on('select2:opening', function(e) {
        if ($(this).data('unselecting')) {
            $(this).removeData('unselecting');
            e.preventDefault();
        }
    });

    // Debug quando o Select2 muda
    $('.select2-cliente').on('change', function() {
        console.log('Select2 change - Valor:', $(this).val());
        console.log('Select2 change - Dados:', $(this).select2('data'));
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const statusPagamento = document.getElementById('status_pagamento');
    const pagoAt = document.getElementById('pago_at');
    const tipoPagamento = document.getElementById('tipo_pagamento');
    const parcelamentoFields = document.getElementById('parcelamento_fields');
    const parcelasInput = document.getElementById('parcelas');
    const valorInput = document.getElementById('valor');
    const dataPrimeiroVencimento = document.getElementById('data_primeiro_vencimento');
    const parcelaInfo = document.getElementById('parcela_info');
    const datasParcelasContainer = document.getElementById('datas_parcelas_container');
    const datasParcelasFields = document.getElementById('datas_parcelas_fields');

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
            // Habilita os campos
            parcelasInput.readonly = false;
            parcelasInput.min = 2;
            dataPrimeiroVencimento.disabled = false;
            
            // Habilita todos os campos de datas das parcelas
            document.querySelectorAll('input[name^="datas_parcelas"]').forEach(input => {
                input.disabled = false;
            });
            
            calcularParcelas();
            gerarCamposDatasParcelas();
        } else {
            parcelamentoFields.style.display = 'none';
            // Para à vista, define parcelas como 1 e desabilita
            parcelasInput.value = 1;
            parcelasInput.readonly = true;
            parcelasInput.min = 1;
            dataPrimeiroVencimento.disabled = true;
            
            // Desabilita todos os campos de datas das parcelas
            document.querySelectorAll('input[name^="datas_parcelas"]').forEach(input => {
                input.disabled = true;
            });
            
            datasParcelasContainer.style.display = 'none';
            parcelaInfo.innerHTML = 'Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.';
        }
    }

    function gerarCamposDatasParcelas() {
        const numParcelas = parseInt(parcelasInput.value) || 2;
        
        if (numParcelas > 1 && tipoPagamento.value === 'parcelado') {
            datasParcelasContainer.style.display = 'block';
            
            // Salva os valores atuais antes de regenerar
            const valoresAtuais = {};
            for (let i = 2; i <= numParcelas; i++) {
                const input = document.querySelector(`input[name="datas_parcelas[${i}]"]`);
                if (input && input.value) {
                    valoresAtuais[i] = input.value;
                }
            }
            
            // Limpa e regenera os campos
            datasParcelasFields.innerHTML = '';
            
            for (let i = 2; i <= numParcelas; i++) {
                const dataBase = dataPrimeiroVencimento.value ? new Date(dataPrimeiroVencimento.value) : new Date();
                const dataSugerida = new Date(dataBase);
                dataSugerida.setMonth(dataSugerida.getMonth() + (i - 1));
                
                const dataSugeridaStr = dataSugerida.toISOString().split('T')[0];
                
                // Usa o valor salvo se existir, caso contrário usa o sugerido
                const valorFinal = valoresAtuais[i] || dataSugeridaStr;
                
                const div = document.createElement('div');
                div.className = 'col-md-4 mb-2';
                div.innerHTML = `
                    <label for="datas_parcelas_${i}" class="form-label small">Parcela ${i}</label>
                    <input type="date" class="form-control form-control-sm" 
                        id="datas_parcelas_${i}" 
                        name="datas_parcelas[${i}]" 
                        value="${valorFinal}"
                        ${tipoPagamento.value === 'avista' ? 'disabled' : ''}>
                `;
                datasParcelasFields.appendChild(div);
            }
        } else {
            datasParcelasContainer.style.display = 'none';
        }
    }

    function calcularParcelas() {
        const valor = parseFloat(valorInput.value) || 0;
        const numParcelas = parseInt(parcelasInput.value) || 2;
        const primeiraData = dataPrimeiroVencimento.value;
        
        if (valor > 0 && numParcelas > 1 && primeiraData && tipoPagamento.value === 'parcelado') {
            const valorParcela = valor / numParcelas;
            let infoHTML = `
                <strong>Resumo das Parcelas:</strong><br>
                • Total: R$ ${valor.toFixed(2).replace('.', ',')}<br>
                • ${numParcelas} parcelas de R$ ${valorParcela.toFixed(2).replace('.', ',')}<br>
                • Primeira parcela: ${formatarData(primeiraData)}
            `;
            
            // Adiciona informações das demais parcelas
            for (let i = 2; i <= numParcelas; i++) {
                const inputData = document.querySelector(`input[name="datas_parcelas[${i}]"]`);
                let dataParcela;
                
                if (inputData && inputData.value) {
                    // Usa a data que já está no campo
                    dataParcela = inputData.value;
                } else {
                    // Calcula uma data sugerida
                    dataParcela = calcularDataMensal(primeiraData, i-1);
                    
                    // Preenche automaticamente o campo se estiver vazio
                    if (inputData && !inputData.value) {
                        inputData.value = dataParcela;
                    }
                }
                
                infoHTML += `<br>• Parcela ${i}: ${formatarData(dataParcela)}`;
            }
            
            if (parcelaInfo) {
                parcelaInfo.innerHTML = infoHTML;
            }
        } else if (parcelaInfo) {
            parcelaInfo.innerHTML = 'Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.';
        }
    }

    function formatarData(dataString) {
        if (!dataString) return 'Não definida';
        const data = new Date(dataString);
        return data.toLocaleDateString('pt-BR');
    }

    function calcularDataMensal(dataBase, meses) {
        const data = new Date(dataBase);
        data.setMonth(data.getMonth() + meses);
        return data.toISOString().split('T')[0];
    }

    // Inicializa os campos
    togglePagoAtField();
    toggleParcelamentoFields();

    // Event listeners
    statusPagamento.addEventListener('change', togglePagoAtField);
    tipoPagamento.addEventListener('change', toggleParcelamentoFields);
    valorInput.addEventListener('input', calcularParcelas);
    parcelasInput.addEventListener('input', function() {
        gerarCamposDatasParcelas();
        calcularParcelas();
    });
    
    dataPrimeiroVencimento.addEventListener('change', function() {
        gerarCamposDatasParcelas();
        calcularParcelas();
        
        // Preenche automaticamente todas as parcelas baseado na primeira data
        const numParcelas = parseInt(parcelasInput.value) || 2;
        const primeiraData = this.value;
        
        if (primeiraData && tipoPagamento.value === 'parcelado') {
            for (let i = 2; i <= numParcelas; i++) {
                const inputData = document.querySelector(`input[name="datas_parcelas[${i}]"]`);
                if (inputData && !inputData.value) {
                    const dataCalculada = calcularDataMensal(primeiraData, i-1);
                    inputData.value = dataCalculada;
                }
            }
            calcularParcelas();
        }
    });

    // Delegation para os campos de data dinâmicos
    document.addEventListener('change', function(e) {
        if (e.target.name && e.target.name.startsWith('datas_parcelas')) {
            calcularParcelas();
        }
    });

    // Formata o valor para aceitar casas decimais
    if (valorInput) {
        valorInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    }

    // JavaScript para gerenciar anexos
    const anexosContainer = document.getElementById('anexos-container');
    const btnAdicionarAnexo = document.getElementById('btn-adicionar-anexo');
    const maxAnexos = 5;

    function atualizarBotoesRemover() {
        const botoesRemover = document.querySelectorAll('.btn-remover-anexo');
        botoesRemover.forEach((btn, index) => {
            // Mostra o botão remover apenas se houver mais de um anexo
            btn.style.display = botoesRemover.length > 1 ? 'block' : 'none';
        });

        // Desabilita o botão de adicionar se atingiu o limite
        const anexosAtuais = document.querySelectorAll('.anexo-item').length;
        const anexosExistentes = {{ isset($servico) ? $servico->anexos->count() : 0 }};
        const totalAnexos = anexosAtuais + anexosExistentes;
        
        btnAdicionarAnexo.disabled = totalAnexos >= maxAnexos;
        if (totalAnexos >= maxAnexos) {
            btnAdicionarAnexo.innerHTML = '<i class="fas fa-ban me-1"></i>Limite de anexos atingido';
            btnAdicionarAnexo.classList.add('btn-secondary');
            btnAdicionarAnexo.classList.remove('btn-outline-primary');
        }
    }

    function adicionarCampoAnexo() {
        const novoAnexo = document.createElement('div');
        novoAnexo.className = 'anexo-item mb-3';
        novoAnexo.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Arquivo</label>
                    <input type="file" class="form-control" name="anexos[]">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Descrição (opcional)</label>
                    <input type="text" class="form-control" name="descricoes_anexos[]" placeholder="Descrição do arquivo">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-remover-anexo">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        anexosContainer.appendChild(novoAnexo);
        
        // Adiciona evento ao botão remover
        const btnRemover = novoAnexo.querySelector('.btn-remover-anexo');
        btnRemover.addEventListener('click', function() {
            novoAnexo.remove();
            atualizarBotoesRemover();
        });
        
        atualizarBotoesRemover();
    }

    // Evento para adicionar novo anexo
    btnAdicionarAnexo.addEventListener('click', adicionarCampoAnexo);

    // Adiciona eventos aos botões remover existentes
    document.querySelectorAll('.btn-remover-anexo').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.anexo-item').remove();
            atualizarBotoesRemover();
        });
    });

    // Inicializa os botões
    atualizarBotoesRemover();
});
</script>

<style>
    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>
@endpush