@extends('layouts.app')

@section('title', 'Cadastrar Cliente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">Cadastrar Cliente</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Cliente</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('clientes.store') }}" method="POST" id="clienteForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome/Razão Social *</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" placeholder="00.000.000/0000-00">
                            <button type="button" class="btn btn-outline-primary" id="btnBuscarCnpj" style="display: none;">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                        </div>
                        <small class="text-muted">Digite um CNPJ válido para buscar os dados automaticamente</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="celular" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="celular" name="celular" value="{{ old('celular') }}" placeholder="(00) 00000-0000">
                    </div>
                </div>
            </div>

            <!-- Seção de Endereço -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Endereço</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" name="cep" value="{{ old('cep') }}" placeholder="00000-000">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarCep">
                                        <i class="fas fa-search me-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="logradouro" class="form-label">Rua</label>
                                <input type="text" class="form-control" id="logradouro" name="logradouro" value="{{ old('logradouro') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="complemento" name="complemento" value="{{ old('complemento') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" value="{{ old('cidade') }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="mb-3">
                                <label for="uf" class="form-label">UF</label>
                                <input type="text" class="form-control" id="uf" name="uf" value="{{ old('uf') }}" maxlength="2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-idealtech-blue">
                    <i class="fas fa-save me-2"></i>Salvar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mb-0" id="loadingMessage">Buscando dados...</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <h5 class="mb-3" id="successTitle">Dados carregados!</h5>
                <p class="text-muted mb-0" id="successMessage">Os dados foram carregados com sucesso.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const btnBuscarCnpj = document.getElementById('btnBuscarCnpj');
    const btnBuscarCep = document.getElementById('btnBuscarCep');
    const cepInput = document.getElementById('cep');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const loadingMessage = document.getElementById('loadingMessage');
    const successTitle = document.getElementById('successTitle');
    const successMessage = document.getElementById('successMessage');

    // Função para validar CNPJ
    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]/g, '');
        return cnpj.length === 14;
    }

    // Função para validar CEP
    function validarCEP(cep) {
        cep = cep.replace(/[^\d]/g, '');
        return cep.length === 8;
    }

    // Mostrar/ocultar botão de buscar CNPJ
    cpfCnpjInput.addEventListener('input', function() {
        const valor = this.value.replace(/[^\d]/g, '');
        if (validarCNPJ(valor)) {
            btnBuscarCnpj.style.display = 'block';
        } else {
            btnBuscarCnpj.style.display = 'none';
        }
    });

    // Buscar dados do CNPJ usando Brasil API
    btnBuscarCnpj.addEventListener('click', async function() {
        const cnpj = cpfCnpjInput.value.replace(/[^\d]/g, '');
        
        if (!validarCNPJ(cnpj)) {
            alert('Por favor, digite um CNPJ válido');
            return;
        }


        try {
            const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
            
            if (!response.ok) {
                throw new Error('CNPJ não encontrado ou erro na consulta');
            }

            const data = await response.json();

            // Preencher os campos com os dados da API
            document.getElementById('nome').value = data.razao_social || '';
            document.getElementById('logradouro').value = data.logradouro || '';
            document.getElementById('numero').value = data.numero || '';
            document.getElementById('complemento').value = data.complemento || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.municipio || '';
            document.getElementById('uf').value = data.uf || '';
            document.getElementById('cep').value = data.cep || '';

            // Formatar telefone se existir
            if (data.ddd_telefone_1) {
                const telefone = `(${data.ddd_telefone_1.substring(0,2)}) ${data.ddd_telefone_1.substring(2)}`;
                document.getElementById('celular').value = telefone;
            }

            // Email da empresa se existir
            if (data.email) {
                document.getElementById('email').value = data.email;
            }

            loadingModal.hide();
            successModal.show();

        } catch (error) {
            loadingModal.hide();
            alert('Erro ao buscar CNPJ: ' + error.message);
            console.error('Erro na busca do CNPJ:', error);
        }
    });

    // Buscar dados do CEP
    btnBuscarCep.addEventListener('click', buscarCep);
    cepInput.addEventListener('blur', buscarCep);

    async function buscarCep() {
        const cep = cepInput.value.replace(/[^\d]/g, '');
        
        if (!validarCEP(cep)) {
            alert('Por favor, digite um CEP válido (8 dígitos)');
            return;
        }
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            
            if (!response.ok) {
                throw new Error('CEP não encontrado');
            }

            const data = await response.json();

            if (data.erro) {
                throw new Error('CEP não encontrado');
            }

            // Preencher os campos de endereço
            document.getElementById('logradouro').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('uf').value = data.uf || '';
            document.getElementById('complemento').value = data.complemento || '';

            // Focar no campo número após buscar o CEP
            document.getElementById('numero').focus();

            loadingModal.hide();
            successTitle.textContent = 'Endereço encontrado!';
            successMessage.textContent = 'O endereço foi carregado com sucesso!.';
            successModal.show();

        } catch (error) {
            loadingModal.hide();
            alert('Erro ao buscar CEP: ' + error.message);
            console.error('Erro na busca do CEP:', error);
        }
    }

    // Máscara para CPF/CNPJ
    cpfCnpjInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length <= 11) {
            // CPF: 000.000.000-00
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            // CNPJ: 00.000.000/0000-00
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        }
        
        this.value = value;
    });

    // Máscara para telefone
    const celularInput = document.getElementById('celular');
    celularInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length > 10) {
            // Com DDD e 9º dígito: (00) 00000-0000
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        } else if (value.length > 6) {
            // Com DDD: (00) 0000-0000
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
        }
        
        this.value = value;
    });

    // Máscara para CEP
    cepInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length > 5) {
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        }
        
        this.value = value;
    });
});
</script>
@endpush