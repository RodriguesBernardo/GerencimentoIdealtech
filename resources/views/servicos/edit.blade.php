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
        <h5 class="card-title mb-0">
            <i class="fas fa-edit text-primary me-2"></i>Editar Serviço: {{ $servico->descricao }}
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('servicos.update', $servico) }}" method="POST" enctype="multipart/form-data" id="servicoForm">
            @csrf
            @method('PUT')

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
                            value="{{ old('data_servico', $servico->data_servico->format('Y-m-d')) }}" required>
                        @error('data_servico')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor Total (R$) *</label>
                        <input type="number" class="form-control" id="valor" name="valor"
                            value="{{ old('valor', $servico->valor) }}" step="0.01" min="0.01" placeholder="0,00" required>
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

            <!-- Seção de Parcelas -->
            <div class="card mb-4" id="parcelamento_card" style="display: {{ $servico->tipo_pagamento == 'parcelado' ? 'block' : 'none' }};">
                <div class="card-header bg-warning bg-opacity-10">
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
                                    value="{{ old('parcelas', $servico->parcelas) }}" min="2" max="24">
                                @error('parcelas')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_primeiro_vencimento" class="form-label">Data do Primeiro Vencimento *</label>
                                <input type="date" class="form-control" id="data_primeiro_vencimento" name="data_primeiro_vencimento"
                                    value="{{ old('data_primeiro_vencimento', $servico->parcelasServico->isNotEmpty() ? $servico->parcelasServico->first()->data_vencimento->format('Y-m-d') : date('Y-m-d')) }}">
                                @error('data_primeiro_vencimento')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Configuração Avançada de Parcelas -->
                    <div class="row" id="config_parcelas_avancada" style="display: {{ $servico->parcelas > 1 ? 'block' : 'none' }};">
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="customizar_parcelas">
                                <label class="form-check-label" for="customizar_parcelas">
                                    <strong>Customizar datas e valores das parcelas</strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Datas Individuais das Parcelas -->
                        <div class="col-12 mb-3" id="datas_parcelas_container" style="display: none;">
                            <div class="card border-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-calendar me-2 text-info"></i>Datas de Vencimento Individuais
                                    </h6>
                                    <div class="alert alert-info py-2 mb-3">
                                        <small><i class="fas fa-info-circle me-1"></i>Preencha as datas individuais para cada parcela ou deixe em branco para usar vencimentos mensais automáticos.</small>
                                    </div>
                                    <div id="datas_parcelas_fields" class="row g-2">
                                        <!-- Campos serão gerados dinamicamente aqui -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Valores Individuais das Parcelas -->
                        <div class="col-12" id="valores_parcelas_container" style="display: none;">
                            <div class="card border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-money-bill me-2 text-success"></i>Valores Individuais das Parcelas
                                        </h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-calcular-automatico">
                                            <i class="fas fa-calculator me-1"></i>Calcular Automaticamente
                                        </button>
                                    </div>
                                    <div class="alert alert-warning py-2 mb-3">
                                        <small><i class="fas fa-exclamation-circle me-1"></i><strong>Opcional:</strong> Você pode alterar manualmente o valor de cada parcela. O sistema ajustará automaticamente as demais parcelas para manter o valor total.</small>
                                    </div>
                                    <div id="valores_parcelas_fields" class="row g-2">
                                        <!-- Campos serão gerados dinamicamente aqui -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo das Parcelas -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border-0 bg-success bg-opacity-10">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-list-alt me-2 text-success"></i>Resumo das Parcelas
                                    </h6>
                                    <div id="parcela_info" class="small">
                                        Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.
                                    </div>
                                </div>
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
                            placeholder="Ex: Boleto 30 dias, Pix, Cartão, Cheque, Aguardando pagamento...">{{ old('observacao_pagamento', $servico->observacao_pagamento) }}</textarea>
                        @error('observacao_pagamento')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações Gerais</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                            placeholder="Observações adicionais sobre o serviço">{{ old('observacoes', $servico->observacoes) }}</textarea>
                        @error('observacoes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success">
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
        let valoresParcelasExistentes = @json($servico->parcelasServico->pluck('valor_parcela', 'numero_parcela')->toArray());
        let datasParcelasExistentes = @json($servico->parcelasServico->pluck('data_vencimento', 'numero_parcela')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        })->toArray());

        // Controle do tipo da parcela
        $('#tipo_pagamento').change(function() {
            if ($(this).val() === 'parcelado') {
                $('#parcelamento_card').show();
                $('#parcelas').prop('required', true);
                $('#parcelas').prop('min', '2'); // Garante min 2 para parcelado
                $('#data_primeiro_vencimento').prop('required', true);
                calcularParcelas();
            } else {
                $('#parcelamento_card').hide();
                $('#parcelas').prop('required', false);
                $('#parcelas').prop('min', '1'); // Muda para min 1 para à vista
                $('#parcelas').val('1'); // Força valor 1 para à vista
                $('#data_primeiro_vencimento').prop('required', false);
                $('#parcela_info').html('<div class="text-muted">Informe o valor total, número de parcelas e primeira data de vencimento para ver o resumo.</div>');
                $('#config_parcelas_avancada').hide();
                $('#datas_parcelas_container').hide();
                $('#valores_parcelas_container').hide();
                $('#customizar_parcelas').prop('checked', false);
            }
        });

        // Controle da customização de parcelas
        $('#customizar_parcelas').change(function() {
            if ($(this).is(':checked')) {
                $('#datas_parcelas_container').show();
                $('#valores_parcelas_container').show();
                gerarCamposDatasParcelas();
                gerarCamposValoresParcelas();
            } else {
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
        
        // Se já tiver parcelas, inicializar os campos
        if ($('#tipo_pagamento').val() === 'parcelado' && {{ $servico->parcelas }} > 1) {
            $('#config_parcelas_avancada').show();
            calcularParcelas();
        }

        function calcularParcelas() {
            const valorTotal = parseFloat($('#valor').val()) || 0;
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const dataPrimeiroVencimento = $('#data_primeiro_vencimento').val();
            
            if (valorTotal <= 0 || numParcelas < 2 || !dataPrimeiroVencimento) {
                $('#parcela_info').html('<div class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Preencha o valor total e a data do primeiro vencimento para calcular as parcelas.</div>');
                $('#config_parcelas_avancada').hide();
                return;
            }

            // Mostrar configuração avançada se houver mais de 1 parcela
            if (numParcelas > 1) {
                $('#config_parcelas_avancada').show();
            } else {
                $('#config_parcelas_avancada').hide();
            }

            // Atualizar display
            atualizarDisplayParcelas(valorTotal, numParcelas, dataPrimeiroVencimento);
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
                
                for (let i = 2; i <= numParcelas; i++) {
                    const dataParcela = new Date(dataBase);
                    dataParcela.setMonth(dataBase.getMonth() + (i - 1));
                    const dataFormatada = dataParcela.toISOString().split('T')[0];
                    
                    // Verifica se já existe um valor para esta parcela
                    const valorAtual = datasParcelasExistentes[i] || dataFormatada;
                    
                    datasHTML += `
                        <div class="col-md-4 col-lg-3">
                            <div class="mb-2">
                                <label class="form-label small">Parcela ${i}</label>
                                <input type="date" class="form-control form-control-sm" 
                                    name="datas_parcelas[${i}]" 
                                    value="${valorAtual}">
                            </div>
                        </div>
                    `;
                }
                
                $('#datas_parcelas_fields').html(datasHTML);
            }
        }

        function gerarCamposValoresParcelas() {
            const valorTotal = parseFloat($('#valor').val()) || 0;
            const numParcelas = parseInt($('#parcelas').val()) || 2;
            const valorParcelaPadrao = valorTotal / numParcelas;
            
            if (numParcelas > 1) {
                let valoresHTML = '';
                
                for (let i = 1; i <= numParcelas; i++) {
                    // Verifica se já existe um valor manual para esta parcela
                    const valorAtual = valoresParcelasExistentes[i] || valorParcelaPadrao.toFixed(2);
                    
                    valoresHTML += `
                        <div class="col-md-4 col-lg-3">
                            <div class="mb-2">
                                <label class="form-label small">Valor Parcela ${i} (R$)</label>
                                <input type="number" class="form-control form-control-sm valor-parcela-input" 
                                    name="valores_parcelas[${i}]" 
                                    value="${valorAtual}"
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
                    
                    // Calcula a soma dos valores atuais (apenas para exibir no display)
                    let somaAtual = 0;
                    $('.valor-parcela-input').each(function() {
                        const valor = parseFloat($(this).val()) || 0;
                        somaAtual += valor;
                    });
                    
                    // REMOVIDO: A redistribuição automática dos valores
                    // Agora os valores ficam exatamente como o usuário digitou
                    
                    // Atualiza apenas o display
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
            
            // Se estamos customizando, pega os valores dos inputs
            if ($('#customizar_parcelas').is(':checked')) {
                for (let i = 1; i <= numParcelas; i++) {
                    const inputValor = $(`input[name="valores_parcelas[${i}]"]`);
                    const valorParcela = inputValor.length > 0 ? parseFloat(inputValor.val()) || 0 : valorTotal / numParcelas;
                    valoresParcelas[i] = valorParcela;
                    somaValores += valorParcela;
                    
                    if (Math.abs(valorParcela - (valorTotal / numParcelas)) > 0.01) {
                        valoresCustomizados = true;
                    }
                }
            } else {
                // Se não está customizando, usa valores iguais
                for (let i = 1; i <= numParcelas; i++) {
                    valoresParcelas[i] = valorTotal / numParcelas;
                }
                somaValores = valorTotal;
            }

            // Gerar informações das parcelas
            let infoHTML = `<strong>Resumo das Parcelas:</strong><br>`;
            
            if (valoresCustomizados) {
                infoHTML += `<span class="text-warning"><i class="fas fa-star me-1"></i>Valores customizados aplicados</span><br>`;
            } else {
                infoHTML += `<span class="text-success"><i class="fas fa-calculator me-1"></i>Valores calculados automaticamente</span><br>`;
            }
            
            infoHTML += `<strong>Valor total:</strong> R$ ${valorTotal.toFixed(2)}<br>`;
            infoHTML += `<strong>Número de parcelas:</strong> ${numParcelas}<br>`;
            
            if (!valoresCustomizados) {
                infoHTML += `<strong>Valor de cada parcela:</strong> R$ ${(valorTotal / numParcelas).toFixed(2)}<br>`;
            }
            
            infoHTML += `<br><strong>Detalhamento:</strong><br>`;
            
            for (let i = 1; i <= numParcelas; i++) {
                let dataParcela;
                
                if ($('#customizar_parcelas').is(':checked')) {
                    const inputData = $(`input[name="datas_parcelas[${i}]"]`);
                    dataParcela = inputData.length > 0 ? inputData.val() : calcularDataMensal(dataPrimeiroVencimento, i-1);
                } else {
                    dataParcela = calcularDataMensal(dataPrimeiroVencimento, i-1);
                }
                
                const valorParcela = valoresParcelas[i];
                
                infoHTML += `<div class="d-flex justify-content-between border-bottom py-1">
                    <span>Parcela ${i}:</span>
                    <span>${formatarData(dataParcela)} - R$ ${valorParcela.toFixed(2)}</span>
                </div>`;
            }
            
            // Verificação de consistência
            if (Math.abs(somaValores - valorTotal) > 0.01) {
                infoHTML += `<div class="text-danger mt-2"><i class="fas fa-exclamation-triangle me-1"></i>A soma das parcelas (R$ ${somaValores.toFixed(2)}) não corresponde ao valor total!</div>`;
            }
            
            $('#parcela_info').html(infoHTML);
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

        // Validação do formulário
        $('#servicoForm').on('submit', function(e) {
            // Remove required dos campos ocultos
            $('#parcelamento_card :input').each(function() {
                if ($('#parcelamento_card').is(':hidden') || $(this).closest(':hidden').length > 0) {
                    $(this).prop('required', false);
                }
            });
            
            // Validação específica para parcelas
            if ($('#tipo_pagamento').val() === 'parcelado') {
                const valorTotal = parseFloat($('#valor').val()) || 0;
                const numParcelas = parseInt($('#parcelas').val()) || 0;
                
                if (numParcelas < 2) {
                    e.preventDefault();
                    alert('Para pagamento parcelado, é necessário pelo menos 2 parcelas.');
                    $('#parcelas').focus();
                    return false;
                }
                
                // Verifica se a soma das parcelas corresponde ao valor total
                if ($('#customizar_parcelas').is(':checked')) {
                    let somaValores = 0;
                    $('.valor-parcela-input').each(function() {
                        somaValores += parseFloat($(this).val()) || 0;
                    });
                    
                    if (Math.abs(somaValores - valorTotal) > 0.01) {
                        e.preventDefault();
                        alert(`A soma das parcelas (R$ ${somaValores.toFixed(2)}) não corresponde ao valor total (R$ ${valorTotal.toFixed(2)}). Por favor, ajuste os valores.`);
                        return false;
                    }
                }
            }
            
            return true;
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
    .valor-parcela-input:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
</style>
@endpush