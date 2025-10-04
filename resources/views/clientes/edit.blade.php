@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">Editar Cliente</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Cliente: {{ $cliente->nome }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('clientes.update', $cliente) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $cliente->nome) }}" required>
                        @error('nome')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}">
                        @error('cpf_cnpj')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="celular" class="form-label">Celular</label>
                        <input type="text" class="form-control" id="celular" name="celular" value="{{ old('celular', $cliente->celular) }}">
                        @error('celular')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                @error('observacoes')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-idealtech-blue">
                    <i class="fas fa-save me-2"></i>Atualizar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>Ver Detalhes
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscaras para os campos
    $('#cpf_cnpj').mask('000.000.000-00', {reverse: true});
    
    $('#celular').mask('(00) 00000-0000');

    // Alternar máscara do CPF/CNPJ baseado no tamanho
    $('#cpf_cnpj').keyup(function() {
        const value = $(this).val().replace(/\D/g, '');
        if (value.length > 11) {
            $(this).mask('00.000.000/0000-00', {reverse: true});
        } else {
            $(this).mask('000.000.000-00', {reverse: true});
        }
    }).trigger('keyup');
});
</script>
@endpush
@endsection