@extends('layouts.app')

@section('title', 'Cadastrar Serviço')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.index') }}">Serviços</a></li>
<li class="breadcrumb-item active">Cadastrar Serviço</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Serviço</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('servicos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-control select2-cliente" id="cliente_id" name="cliente_id" required>
                            @if(old('cliente_id') || isset($cliente_id))
                                @php
                                    $clienteSelecionado = \App\Models\Cliente::find(old('cliente_id', $cliente_id ?? null));
                                @endphp
                                @if($clienteSelecionado)
                                    <option value="{{ $clienteSelecionado->id }}" selected>
                                        {{ $clienteSelecionado->nome }}
                                        @if($clienteSelecionado->cpf_cnpj)
                                            - {{ $clienteSelecionado->cpf_cnpj }}
                                        @endif
                                    </option>
                                @endif
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
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao') }}" required>
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
                            value="{{ old('data_servico', date('Y-m-d')) }}" required>
                        @error('data_servico')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="number" class="form-control" id="valor" name="valor"
                            value="{{ old('valor') }}" step="0.01" min="0" placeholder="0,00">
                        @error('valor')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status_pagamento" class="form-label">Status do Pagamento *</label>
                        <select class="form-control" id="status_pagamento" name="status_pagamento" required>
                            <option value="pendente" {{ old('status_pagamento') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ old('status_pagamento') == 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="nao_pago" {{ old('status_pagamento') == 'nao_pago' ? 'selected' : '' }}>Não Pago</option>
                        </select>
                        @error('status_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Campos de Parcelamento -->
            <div class="row" id="parcelamento_fields" style="display: none;">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parcelas" class="form-label">Número de Parcelas *</label>
                        <input type="number" class="form-control" id="parcelas" name="parcelas"
                            value="{{ old('parcelas', 2) }}" min="2" max="24">
                        @error('parcelas')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_primeiro_vencimento" class="form-label">Data do Primeiro Vencimento</label>
                        <input type="date" class="form-control" id="data_primeiro_vencimento" name="data_primeiro_vencimento"
                            value="{{ old('data_primeiro_vencimento', date('Y-m-d')) }}">
                        @error('data_primeiro_vencimento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Container para as datas individuais das parcelas -->
                <div class="col-12" id="datas_parcelas_container" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Datas de Vencimento das Parcelas</label>
                        <div class="alert alert-info">
                            <small>Preencha as datas individuais para cada parcela ou deixe em branco para usar vencimentos mensais.</small>
                        </div>
                        <div id="datas_parcelas_fields" class="row">
                            <!-- As datas das parcelas serão geradas aqui via JavaScript -->
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="alert alert-info" id="parcela_info">
                        <!-- Informações das parcelas serão exibidas aqui -->
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tipo_pagamento" class="form-label">Tipo de Pagamento *</label>
                        <select class="form-control" id="tipo_pagamento" name="tipo_pagamento" required>
                            <option value="avista" {{ old('tipo_pagamento') == 'avista' ? 'selected' : '' }}>À Vista</option>
                            <option value="parcelado" {{ old('tipo_pagamento') == 'parcelado' ? 'selected' : '' }}>Parcelado</option>
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
                            value="{{ old('pago_at') }}"
                            disabled>
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
                    placeholder="Ex: Boleto 30 dias, Pix, Cartão, Cheque, Aguardando pagamento...">{{ old('observacao_pagamento') }}</textarea>
                @error('observacao_pagamento')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações Gerais</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                    placeholder="Observações adicionais sobre o serviço">{{ old('observacoes') }}</textarea>
                @error('observacoes')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Seção de Anexos -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Anexos (Máximo: 5 arquivos)</h6>
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
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-idealtech-blue">
                    <i class="fas fa-save me-2"></i>Salvar Serviço
                </button>
                <a href="{{ route('servicos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
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

        // Controle do tipo de pagamento
        $('#tipo_pagamento').change(function() {
            if ($(this).val() === 'parcelado') {
                $('#parcelamento_fields').show();
                $('#parcelas').val(2);
                calcularParcelas();
            } else {
                $('#parcelamento_fields').hide();
                $('#parcelas').val(1);
                $('#parcela_info').html('');
                $('#datas_parcelas_container').hide();
            }
        });

        // Calcular parcelas quando o valor ou número de parcelas mudar
        $('#valor, #parcelas, #data_primeiro_vencimento').on('input change', function() {
            calcularParcelas();
        });

        // Status do pagamento controla a data de pagamento
        $('#status_pagamento').change(function() {
            if ($(this).val() === 'pago') {
                $('#pago_at').prop('disabled', false);
                if (!$('#pago_at').val()) {
                    $('#pago_at').val(new Date().toISOString().slice(0, 16));
                }
            } else {
                $('#pago_at').prop('disabled', true);
                $('#pago_at').val('');
            }
        });

        // Inicializar estado dos campos
        $('#tipo_pagamento').trigger('change');
        $('#status_pagamento').trigger('change');

        function calcularParcelas() {
            const valorTotal = parseFloat($('#valor').val()) || 0;
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const dataPrimeiroVencimento = $('#data_primeiro_vencimento').val();
            
            if (valorTotal <= 0 || numParcelas < 2 || !dataPrimeiroVencimento) {
                $('#parcela_info').html('<div class="text-warning">Preencha o valor total e a data do primeiro vencimento para calcular as parcelas.</div>');
                $('#datas_parcelas_container').hide();
                return;
            }

            const valorParcela = valorTotal / numParcelas;
            
            // Gerar informações das parcelas
            let infoHTML = `<strong>Resumo das Parcelas:</strong><br>`;
            infoHTML += `Valor total: R$ ${valorTotal.toFixed(2)}<br>`;
            infoHTML += `Número de parcelas: ${numParcelas}<br>`;
            infoHTML += `Valor de cada parcela: R$ ${valorParcela.toFixed(2)}<br><br>`;
            
            // Gerar campos de datas individuais
            let datasHTML = '';
            const dataBase = new Date(dataPrimeiroVencimento);
            
            for (let i = 1; i <= numParcelas; i++) {
                const dataParcela = new Date(dataBase);
                dataParcela.setMonth(dataBase.getMonth() + (i - 1));
                
                const dataFormatada = dataParcela.toISOString().split('T')[0];
                const valorFormatado = valorParcela.toFixed(2);
                
                infoHTML += `Parcela ${i}: ${new Date(dataFormatada).toLocaleDateString('pt-BR')} - R$ ${valorFormatado}<br>`;
                
                datasHTML += `
                    <div class="col-md-4 mb-2">
                        <div class="input-group">
                            <span class="input-group-text">Parcela ${i}</span>
                            <input type="date" class="form-control" name="datas_parcelas[${i}]" value="${dataFormatada}">
                        </div>
                    </div>
                `;
            }
            
            $('#parcela_info').html(infoHTML);
            $('#datas_parcelas_fields').html(datasHTML);
            $('#datas_parcelas_container').show();
        }

        // Gerenciamento de anexos
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
            btnAdicionarAnexo.disabled = anexosAtuais >= maxAnexos;
            
            if (anexosAtuais >= maxAnexos) {
                btnAdicionarAnexo.innerHTML = '<i class="fas fa-ban me-1"></i>Limite de anexos atingido';
                btnAdicionarAnexo.classList.add('btn-secondary');
                btnAdicionarAnexo.classList.remove('btn-outline-primary');
            } else {
                btnAdicionarAnexo.innerHTML = '<i class="fas fa-plus me-1"></i>Adicionar outro arquivo';
                btnAdicionarAnexo.classList.remove('btn-secondary');
                btnAdicionarAnexo.classList.add('btn-outline-primary');
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
    .select2-client-result .client-name {
        font-weight: bold;
    }
    .select2-client-result .client-document,
    .select2-client-result .client-phone {
        font-size: 0.85em;
        color: #6c757d;
    }
</style>
@endpush
@endsection