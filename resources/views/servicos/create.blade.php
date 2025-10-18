@extends('layouts.app')

@section('title', 'Cadastrar Serviço')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('servicos.index') }}">Serviços</a></li>
<li class="breadcrumb-item active">Cadastrar Serviço</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('servicos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Informações Básicas -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informações do Serviço
                    </h6>
                </div>
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

            <!-- Informações Financeiras -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-money-bill-wave me-2 text-success"></i>Informações Financeiras
                    </h6>
                </div>
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
                        <label for="valor" class="form-label">Valor Total (R$)</label>
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

            <!-- Configuração de Pagamento -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-credit-card me-2 text-info"></i>Configuração de Pagamento
                    </h6>
                </div>
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

            <!-- Campos de Parcelamento -->
            <div class="card mb-4" id="parcelamento_card" style="display: none;">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2 text-warning"></i>Configuração de Parcelas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
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
                    </div>

                    <!-- Container para as datas individuais das parcelas -->
                    <div class="row mb-3" id="datas_parcelas_container" style="display: none;">
                        <div class="col-12">
                            <label class="form-label">Datas de Vencimento das Parcelas</label>
                            <div class="alert alert-info py-2">
                                <small><i class="fas fa-info-circle me-1"></i>Preencha as datas individuais para cada parcela ou deixe em branco para usar vencimentos mensais.</small>
                            </div>
                            <div id="datas_parcelas_fields" class="row g-2">
                                <!-- As datas das parcelas serão geradas aqui via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Container para os valores individuais das parcelas -->
                    <div class="row mb-3" id="valores_parcelas_container" style="display: none;">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Valores Individuais das Parcelas</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-calcular-automatico">
                                    <i class="fas fa-calculator me-1"></i>Calcular Automaticamente
                                </button>
                            </div>
                            <div class="alert alert-warning py-2">
                                <small><i class="fas fa-exclamation-circle me-1"></i><strong>Opcional:</strong> Você pode alterar manualmente o valor de cada parcela. As demais parcelas serão ajustadas automaticamente para manter o valor total.</small>
                            </div>
                            <div id="valores_parcelas_fields" class="row g-2">
                                <!-- Os valores das parcelas serão gerados aqui via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Resumo das Parcelas -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success" id="parcela_info">
                                <i class="fas fa-calculator me-2"></i>Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-sticky-note me-2 text-secondary"></i>Observações
                    </h6>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="observacao_pagamento" class="form-label">Observação do Pagamento</label>
                        <textarea class="form-control" id="observacao_pagamento" name="observacao_pagamento" rows="2"
                            placeholder="Ex: Boleto 30 dias, Pix, Cartão, Cheque, Aguardando pagamento...">{{ old('observacao_pagamento') }}</textarea>
                        @error('observacao_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações Gerais</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                            placeholder="Observações adicionais sobre o serviço">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Seção de Anexos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-paperclip me-2 text-primary"></i>Anexos (Máximo: 5 arquivos)
                    </h6>
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
                        <i class="fas fa-info-circle me-1"></i>Formatos aceitos: todos os tipos de arquivo. Tamanho máximo por arquivo: 10MB.
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Salvar Serviço
                </button>
                <a href="{{ route('servicos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

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
                else if (cliente.text && cliente.text.includes(' - ')) {
                    return cliente.text.split(' - ')[0];
                }
                else {
                    return cliente.text;
                }
            }
        });

        // Variável para controlar se estamos editando manualmente
        let editandoManual = false;

        // Controle do tipo de pagamento
        $('#tipo_pagamento').change(function() {
            if ($(this).val() === 'parcelado') {
                $('#parcelamento_card').show();
                $('#parcelas').val(2);
                calcularParcelas();
            } else {
                $('#parcelamento_card').hide();
                $('#parcelas').val(1);
                $('#parcela_info').html('<i class="fas fa-calculator me-2"></i>Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.');
                $('#datas_parcelas_container').hide();
                $('#valores_parcelas_container').hide();
            }
        });

        // Calcular parcelas quando o valor ou número de parcelas mudar
        $('#valor, #parcelas, #data_primeiro_vencimento').on('input change', function() {
            if (!editandoManual) {
                calcularParcelas();
            }
        });

        // Botão calcular automaticamente
        $('#btn-calcular-automatico').on('click', function() {
            calcularValoresAutomaticos();
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
                $('#parcela_info').html('<div class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Preencha o valor total e a data do primeiro vencimento para calcular as parcelas.</div>');
                $('#datas_parcelas_container').hide();
                $('#valores_parcelas_container').hide();
                return;
            }

            const valorParcelaPadrao = valorTotal / numParcelas;
            
            // Gerar campos de datas individuais
            gerarCamposDatasParcelas();
            
            // Gerar campos de valores individuais (só na primeira vez ou quando não há valores manuais)
            if (!$('.valor-parcela-input').length || !temValoresManuais()) {
                gerarCamposValoresParcelas(valorParcelaPadrao);
            }
            
            // Atualizar display
            atualizarDisplayParcelas(valorTotal, numParcelas, dataPrimeiroVencimento);
        }

        function temValoresManuais() {
            const valorTotal = parseFloat($('#valor').val()) || 0;
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const valorParcelaPadrao = valorTotal / numParcelas;
            let temCustomizado = false;
            
            $('.valor-parcela-input').each(function() {
                const valorAtual = parseFloat($(this).val()) || 0;
                if (Math.abs(valorAtual - valorParcelaPadrao) > 0.01) {
                    temCustomizado = true;
                    return false; // break
                }
            });
            
            return temCustomizado;
        }

        function calcularValoresAutomaticos() {
            const valorTotal = parseFloat($('#valor').val()) || 0;
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const valorParcelaPadrao = valorTotal / numParcelas;
            
            // Aplica o valor padrão para todas as parcelas
            $('.valor-parcela-input').each(function() {
                $(this).val(valorParcelaPadrao.toFixed(2));
            });
            
            // Atualiza o display
            const dataPrimeiroVencimento = $('#data_primeiro_vencimento').val();
            atualizarDisplayParcelas(valorTotal, numParcelas, dataPrimeiroVencimento);
        }

        function gerarCamposDatasParcelas() {
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const dataPrimeiroVencimento = $('#data_primeiro_vencimento').val();
            
            if (numParcelas > 1 && dataPrimeiroVencimento) {
                let datasHTML = '';
                const dataBase = new Date(dataPrimeiroVencimento);
                
                for (let i = 1; i <= numParcelas; i++) {
                    const dataParcela = new Date(dataBase);
                    dataParcela.setMonth(dataBase.getMonth() + (i - 1));
                    const dataFormatada = dataParcela.toISOString().split('T')[0];
                    
                    datasHTML += `
                        <div class="col-md-4 col-lg-3">
                            <div class="mb-2">
                                <label class="form-label small">Parcela ${i}</label>
                                <input type="date" class="form-control form-control-sm" name="datas_parcelas[${i}]" value="${dataFormatada}">
                            </div>
                        </div>
                    `;
                }
                
                $('#datas_parcelas_fields').html(datasHTML);
            }
        }

        function gerarCamposValoresParcelas(valorParcelaPadrao) {
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            
            if (numParcelas > 1) {
                let valoresHTML = '';
                
                for (let i = 1; i <= numParcelas; i++) {
                    valoresHTML += `
                        <div class="col-md-4 col-lg-3">
                            <div class="mb-2">
                                <label class="form-label small">Valor Parcela ${i} (R$)</label>
                                <input type="number" class="form-control form-control-sm valor-parcela-input" 
                                    name="valores_parcelas[${i}]" 
                                    value="${valorParcelaPadrao.toFixed(2)}"
                                    step="0.01" min="0.01"
                                    data-parcela="${i}"
                                    placeholder="0,00">
                            </div>
                        </div>
                    `;
                }
                
                $('#valores_parcelas_fields').html(valoresHTML);
                
                // Adiciona eventos aos inputs de valor
                adicionarEventosValoresParcelas();
            }
        }

        function adicionarEventosValoresParcelas() {
            $('.valor-parcela-input').off('input').on('input', function() {
                editandoManual = true;
                
                setTimeout(() => {
                    const valorTotal = parseFloat($('#valor').val()) || 0;
                    const numParcelas = parseInt($('#parcelas').val()) || 2;
                    
                    if (valorTotal <= 0 || numParcelas < 2) return;
                    
                    // Calcula a soma dos valores atuais
                    let somaAtual = 0;
                    const valoresAtuais = [];
                    const inputAtual = $(this);
                    
                    $('.valor-parcela-input').each(function(index) {
                        const valor = parseFloat($(this).val()) || 0;
                        valoresAtuais[index] = valor;
                        somaAtual += valor;
                    });
                    
                    // Se a soma for diferente do total, redistribui
                    if (Math.abs(somaAtual - valorTotal) > 0.01) {
                        const diferenca = valorTotal - somaAtual;
                        const outrasParcelas = $('.valor-parcela-input').not(inputAtual).length;
                        
                        if (outrasParcelas > 0) {
                            const ajustePorParcela = diferenca / outrasParcelas;
                            
                            $('.valor-parcela-input').each(function(index) {
                                if (!$(this).is(inputAtual)) {
                                    const novoValor = (valoresAtuais[index] || 0) + ajustePorParcela;
                                    $(this).val(Math.max(novoValor, 0.01).toFixed(2));
                                }
                            });
                        }
                    }
                    
                    // Atualiza o display
                    const dataPrimeiroVencimento = $('#data_primeiro_vencimento').val();
                    atualizarDisplayParcelas(valorTotal, numParcelas, dataPrimeiroVencimento);
                    
                    editandoManual = false;
                }, 100);
            });
        }

        function atualizarDisplayParcelas(valorTotal, numParcelas, dataPrimeiroVencimento) {
            // Calcula valores atuais das parcelas
            let valoresCustomizados = false;
            const valoresParcelas = [];
            let somaValores = 0;
            
            for (let i = 1; i <= numParcelas; i++) {
                const inputValor = $(`input[name="valores_parcelas[${i}]"]`);
                const valorParcela = inputValor.length > 0 ? parseFloat(inputValor.val()) || 0 : valorTotal / numParcelas;
                valoresParcelas[i] = valorParcela;
                somaValores += valorParcela;
                
                if (Math.abs(valorParcela - (valorTotal / numParcelas)) > 0.01) {
                    valoresCustomizados = true;
                }
            }

            // Gerar informações das parcelas
            let infoHTML = `<strong><i class="fas fa-list-alt me-2"></i>Resumo das Parcelas:</strong><br>`;
            
            if (valoresCustomizados) {
                infoHTML += `<span class="text-warning"><i class="fas fa-star me-1"></i>Valores customizados aplicados</span><br>`;
            } else {
                infoHTML += `<span class="text-success"><i class="fas fa-calculator me-1"></i>Valores calculados automaticamente</span><br>`;
            }
            
            infoHTML += `Valor total: R$ ${valorTotal.toFixed(2)}<br>`;
            infoHTML += `Número de parcelas: ${numParcelas}<br>`;
            
            if (!valoresCustomizados) {
                infoHTML += `Valor de cada parcela: R$ ${(valorTotal / numParcelas).toFixed(2)}<br><br>`;
            }
            
            infoHTML += `<strong>Detalhamento:</strong><br>`;
            
            for (let i = 1; i <= numParcelas; i++) {
                const inputData = $(`input[name="datas_parcelas[${i}]"]`);
                const dataParcela = inputData.length > 0 ? inputData.val() : calcularDataMensal(dataPrimeiroVencimento, i-1);
                const valorParcela = valoresParcelas[i];
                
                infoHTML += `Parcela ${i}: ${formatarData(dataParcela)} - R$ ${valorParcela.toFixed(2)}<br>`;
            }
            
            $('#parcela_info').html(infoHTML);
            $('#datas_parcelas_container').show();
            $('#valores_parcelas_container').show();
        }

        function calcularDataMensal(dataBase, meses) {
            const data = new Date(dataBase);
            data.setMonth(data.getMonth() + meses);
            return data.toISOString().split('T')[0];
        }

        function formatarData(dataString) {
            if (!dataString) return 'Não definida';
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR');
        }

        // Gerenciamento de anexos
        const anexosContainer = document.getElementById('anexos-container');
        const btnAdicionarAnexo = document.getElementById('btn-adicionar-anexo');
        const maxAnexos = 5;

        function atualizarBotoesRemover() {
            const botoesRemover = document.querySelectorAll('.btn-remover-anexo');
            botoesRemover.forEach((btn, index) => {
                btn.style.display = botoesRemover.length > 1 ? 'block' : 'none';
            });

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

    // No evento de submit do formulário, remova required dos campos ocultos
    $('form').on('submit', function() {
        $('#parcelamento_card :input').each(function() {
            if ($(this).is(':hidden') || $(this).closest(':hidden').length > 0) {
                $(this).prop('required', false);
            }
        });
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
    .card-header h6 {
        font-weight: 600;
    }

</style>
@endpush