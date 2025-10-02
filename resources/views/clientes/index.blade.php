@extends('layouts.app')

@section('title', 'Clientes')

@section('header-actions')
    <a href="{{ route('clientes.create') }}" class="btn btn-idealtech-blue">
        <i class="fas fa-plus me-2"></i>Novo Cliente
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Clientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF/CNPJ</th>
                        <th>WhatsApp</th>
                        <th>Total Serviços</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nome }}</td>
                        <td>{{ $cliente->cpf_cnpj ?? '-' }}</td>
                        <td>{{ $cliente->whatsapp ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $cliente->servicos_count ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
@endsection