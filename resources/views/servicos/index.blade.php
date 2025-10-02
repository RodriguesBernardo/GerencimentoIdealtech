@extends('layouts.app')

@section('title', 'Serviços')

@section('header-actions')
    <a href="{{ route('servicos.create') }}" class="btn btn-idealtech-blue">
        <i class="fas fa-plus me-2"></i>Novo Serviço
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Serviços</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicos as $servico)
                    <tr>
                        <td>{{ $servico->cliente->nome }}</td>
                        <td>{{ Str::limit($servico->descricao_servico, 50) }}</td>
                        <td>
                            @if(auth()->user()->podeVerValoresCompletos())
                                R$ {{ number_format($servico->valor, 2, ',', '.') }}
                            @else
                                ***
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($servico->status == 'Pago') badge-pago
                                @elseif($servico->status == 'Atrasado') badge-atrasado
                                @else badge-pendente @endif">
                                {{ $servico->status }}
                            </span>
                        </td>
                        <td>{{ $servico->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('servicos.show', $servico) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('servicos.edit', $servico) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('servicos.destroy', $servico) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
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
            {{ $servicos->links() }}
        </div>
    </div>
</div>
@endsection