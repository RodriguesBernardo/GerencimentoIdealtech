@extends('layouts.app')

@section('title', 'Gerenciar Usuários')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Gerenciar Usuários
                    </h5>
                    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Novo Usuário
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $usuario)
                                <tr class="{{ $usuario->trashed() ? 'table-danger' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                <span class="text-white fw-bold">
                                                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <strong>{{ $usuario->name }}</strong>
                                                @if($usuario->id === auth()->id())
                                                    <span class="badge bg-info ms-1">Você</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->telefone ?? 'Não informado' }}</td>
                                    <td>
                                        @if($usuario->is_admin)
                                            <span class="badge bg-success">Administrador</span>
                                        @else
                                            <span class="badge bg-secondary">Usuário</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usuario->trashed())
                                            <span class="badge bg-danger">Inativo</span>
                                        @else
                                            <span class="badge bg-success">Ativo</span>
                                        @endif
                                    </td>
                                    <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @if(!$usuario->trashed())
                                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                @if($usuario->id !== auth()->id())
                                                    <form action="{{ route('admin.usuarios.destroy', $usuario) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <form action="{{ route('admin.usuarios.restore', $usuario->id) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurar">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                                
                                                @if($usuario->id !== auth()->id())
                                                    <form action="{{ route('admin.usuarios.force-delete', $usuario->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Tem certeza que deseja excluir PERMANENTEMENTE este usuário? Esta ação não pode ser desfeita.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir Permanentemente">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Nenhum usuário encontrado.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} usuários
                        </div>
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
}
.table th {
    border-top: none;
    font-weight: 600;
}
</style>
@endpush