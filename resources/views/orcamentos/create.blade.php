@extends('layouts.app')
@section('title', 'Criar Novo Orçamento')

@section('content')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<form action="{{ route('orcamentos.store') }}" method="POST" id="form-orcamento" class="fade-in">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Dados do Cliente</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="toggleClienteAvulso" {{ old('cliente_nome_avulso') ? 'checked' : '' }}>
                        <label class="form-check-label" for="toggleClienteAvulso">Orçamento para Cliente Avulso (Não cadastrado)</label>
                    </div>

                    <div id="div-cliente-cadastrado" class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="cliente_id" class="form-select select2-bootstrap-5" style="width: 100%;">
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }} {{ $cliente->cpf_cnpj ? ' - ' . $cliente->cpf_cnpj : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="div-cliente-avulso" class="row d-none">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Cliente Avulso <span class="text-danger">*</span></label>
                            <input type="text" name="cliente_nome_avulso" id="cliente_nome_avulso" class="form-control" value="{{ old('cliente_nome_avulso') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Itens do Orçamento</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                        <i class="fas fa-plus"></i> Adicionar Linha
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="tabela-itens">
                            <thead class="">
                                <tr>
                                    <th width="40%">Produto/Serviço</th>
                                    <th width="15%">Qtd</th>
                                    <th width="20%">Vlr. Unitário</th>
                                    <th width="20%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Textos Adicionais</div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Observações (Visível no PDF)</label>
                        <div id="editor-observacoes" style="height: 150px;">{!! old('observacoes') !!}</div>
                        <textarea name="observacoes" id="textarea-observacoes" class="d-none"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notas Internas (Invisível no PDF)</label>
                        <div id="editor-notas" style="height: 100px;">{!! old('notas_internas') !!}</div>
                        <textarea name="notas_internas" id="textarea-notas" class="d-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Configurações</div>
                <div class="card-body">
                    
                    <div class="form-check form-switch mb-3 p-3 border rounded">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="mostrar_valores_individuais" id="mostrar_valores" value="1" {{ old('mostrar_valores_individuais', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-primary" for="mostrar_valores">
                            <i class="fas fa-eye"></i> Exibir valores unitários no PDF
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" name="data_emissao" class="form-control" value="{{ old('data_emissao', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Validade do Orçamento</label>
                        <input type="date" name="data_validade" class="form-control" value="{{ old('data_validade', date('Y-m-d', strtotime('+5 days'))) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Rascunho" {{ old('status') == 'Rascunho' ? 'selected' : '' }}>Rascunho</option>
                            <option value="Enviado" {{ old('status') == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                            <option value="Aprovado" {{ old('status') == 'Aprovado' ? 'selected' : '' }}>Aprovado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 border rounded p-2">
                        <label class="form-label fw-bold">Condições de Pagamento</label>
                        <div id="editor-pagamento" style="height: 100px;">{!! old('condicoes_pagamento') !!}</div>
                        <textarea name="condicoes_pagamento" id="textarea-pagamento" class="d-none"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prazo de Entrega</label>
                        <input type="text" name="prazo_entrega" class="form-control" placeholder="Ex: 5 dias úteis" value="{{ old('prazo_entrega') }}">
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-primary">
                <div class="card-header bg-transparent">Resumo Financeiro</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold" id="display_subtotal">R$ 0,00</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-danger">Desconto (R$)</label>
                        <input type="number" step="0.01" min="0" name="desconto" id="input_desconto" class="form-control form-control-sm" value="{{ old('desconto', 0) }}" oninput="calcularTotaisGlobais()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary">Acréscimos / Frete (R$)</label>
                        <input type="number" step="0.01" min="0" name="frete_acrescimos" id="input_frete" class="form-control form-control-sm" value="{{ old('frete_acrescimos', 0) }}" oninput="calcularTotaisGlobais()">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5">Total:</span>
                        <span class="fs-4 fw-bold text-success" id="display_total">R$ 0,00</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-save"></i> Salvar Orçamento
                    </button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
@endsection

@push('scripts')
<script>
    // Inicializa o Editor Quill com barra de ferramentas básica (Negrito, Lista, etc)
    var quillOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    };

    var quillObservacoes = new Quill('#editor-observacoes', quillOptions);
    var quillNotas = new Quill('#editor-notas', quillOptions);
    var quillPagamento = new Quill('#editor-pagamento', quillOptions);

    // Antes de enviar o formulário, copia o HTML dos editores para os textareas ocultos
    document.getElementById('form-orcamento').onsubmit = function() {
        document.getElementById('textarea-observacoes').value = quillObservacoes.root.innerHTML === '<p><br></p>' ? '' : quillObservacoes.root.innerHTML;
        document.getElementById('textarea-notas').value = quillNotas.root.innerHTML === '<p><br></p>' ? '' : quillNotas.root.innerHTML;
        document.getElementById('textarea-pagamento').value = quillPagamento.root.innerHTML === '<p><br></p>' ? '' : quillPagamento.root.innerHTML;
    };

    $(document).ready(function() {
        if ($('.select2-bootstrap-5').length) {
            $('.select2-bootstrap-5').select2({ theme: 'bootstrap-5', placeholder: 'Selecione um cliente...' });
        }
        
        const toggleCliente = document.getElementById('toggleClienteAvulso');
        toggleCliente.addEventListener('change', function() {
            const isAvulso = this.checked;
            const divCadastrado = document.getElementById('div-cliente-cadastrado');
            const divAvulso = document.getElementById('div-cliente-avulso');
            const selectCliente = document.getElementById('cliente_id');
            const inputNomeAvulso = document.getElementById('cliente_nome_avulso');

            if (isAvulso) {
                divCadastrado.classList.add('d-none');
                divAvulso.classList.remove('d-none');
                $(selectCliente).val(null).trigger('change');
                inputNomeAvulso.required = true;
            } else {
                divCadastrado.classList.remove('d-none');
                divAvulso.classList.add('d-none');
                inputNomeAvulso.required = false;
            }
        });
        
        toggleCliente.dispatchEvent(new Event('change'));
        adicionarLinhaItem();
        calcularTotaisGlobais();
    });

    let itemIndex = 0;
    document.getElementById('btn-add-item').addEventListener('click', () => adicionarLinhaItem());

    function adicionarLinhaItem(descricao = '', detalhes = '', qtd = 1, valor_unitario = 0) {
        const tbody = document.querySelector('#tabela-itens tbody');
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>
                <input type="text" name="itens[${itemIndex}][descricao]" class="form-control mb-1" placeholder="Nome do item" value="${descricao}" required>
                <input type="text" name="itens[${itemIndex}][detalhes]" class="form-control form-control-sm text-muted" placeholder="Detalhes (opcional)" value="${detalhes}">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="itens[${itemIndex}][quantidade]" class="form-control input-qtd" value="${qtd}" required oninput="calcularLinha(this)">
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" step="0.01" min="0" name="itens[${itemIndex}][valor_unitario]" class="form-control input-vlr" value="${valor_unitario}" required oninput="calcularLinha(this)">
                </div>
            </td>
            <td class="align-middle text-end">
                <span class="fw-bold span-total-linha">R$ 0,00</span>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerLinha(this)">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        if(valor_unitario > 0) calcularLinha(tr.querySelector('.input-vlr'));
        itemIndex++;
    }

    function removerLinha(btn) {
        const tbody = document.querySelector('#tabela-itens tbody');
        if(tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcularTotaisGlobais();
        } else {
            alert('O orçamento precisa de pelo menos um item.');
        }
    }

    function calcularLinha(input) {
        const tr = input.closest('tr');
        const qtd = parseFloat(tr.querySelector('.input-qtd').value) || 0;
        const vlr = parseFloat(tr.querySelector('.input-vlr').value) || 0;
        tr.querySelector('.span-total-linha').innerText = formatarDinheiro(qtd * vlr);
        calcularTotaisGlobais();
    }

    function calcularTotaisGlobais() {
        let subtotal = 0;
        document.querySelectorAll('#tabela-itens tbody tr').forEach(tr => {
            const qtd = parseFloat(tr.querySelector('.input-qtd').value) || 0;
            const vlr = parseFloat(tr.querySelector('.input-vlr').value) || 0;
            subtotal += (qtd * vlr);
        });

        const desconto = parseFloat(document.getElementById('input_desconto').value) || 0;
        const frete = parseFloat(document.getElementById('input_frete').value) || 0;
        const total = (subtotal - desconto) + frete;

        document.getElementById('display_subtotal').innerText = formatarDinheiro(subtotal);
        document.getElementById('display_total').innerText = formatarDinheiro(total > 0 ? total : 0);
    }

    function formatarDinheiro(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
</script>
@endpush