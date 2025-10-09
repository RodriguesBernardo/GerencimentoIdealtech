@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Usuário: {{ $usuario->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $usuario->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" 
                                           placeholder="Deixe em branco para manter a senha atual">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Mínimo 6 caracteres
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation"
                                           placeholder="Confirme a nova senha">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control @error('telefone') is-invalid @enderror" 
                                           id="telefone" name="telefone" value="{{ old('telefone', $usuario->telefone) }}"
                                           placeholder="(11) 99999-9999">
                                    @error('telefone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Usuário</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" 
                                               value="1" {{ old('is_admin', $usuario->is_admin) ? 'checked' : '' }}
                                               {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="is_admin">
                                            Usuário Administrador
                                        </label>
                                        @if($usuario->id === auth()->id())
                                            <small class="form-text text-muted d-block">
                                                Você não pode remover seus próprios privilégios de administrador
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissões -->
                        <div class="mb-4">
                            <label class="form-label">Permissões Específicas</label>
                            <small class="form-text text-muted d-block mb-2">
                                Selecione as permissões específicas para este usuário (ignorar se for administrador)
                            </small>
                            
                            <div class="row">
                                @foreach($permissoes as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="permissoes_{{ $key }}" 
                                               name="permissoes[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, old('permissoes', is_array($usuario->permissoes) ? $usuario->permissoes : [])) ? 'checked' : '' }}
                                               {{ $usuario->id === auth()->id() && $key === 'usuarios.manage' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="permissoes_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('permissoes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Atualizar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara de telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d*)/, '($1');
            }
            
            e.target.value = value;
        });
    }

    // Se for administrador, marcar todas as permissões
    const adminCheckbox = document.getElementById('is_admin');
    if (adminCheckbox) {
        adminCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="permissoes[]"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = this.checked;
                }
            });
        });
    }
});
</script>
@endpush