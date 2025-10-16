@extends('layouts.app')

@section('title', 'Logs do Sistema')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-history"></i> Logs do Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="action">Ação</label>
                                <select name="action" id="action" class="form-control">
                                    <option value="">Todas as ações</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                            {{ ucfirst($action) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="model_type">Modelo</label>
                                <select name="model_type" id="model_type" class="form-control">
                                    <option value="">Todos os modelos</option>
                                    @foreach($modelTypes as $modelType)
                                        <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                            {{ class_basename($modelType) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="user_id">Usuário</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">Todos os usuários</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_start">Período</label>
                                <div class="input-group">
                                    <input type="date" name="date_start" id="date_start" class="form-control" 
                                           value="{{ request('date_start') }}">
                                    <input type="date" name="date_end" id="date_end" class="form-control" 
                                           value="{{ request('date_end') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Logs -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Modelo</th>
                                    <th>ID</th>
                                    <th>Descrição</th>
                                    <th>IP</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $log->user->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ getActionBadgeClass($log->action) }}">
                                                {{ $log->action_formatted }}
                                            </span>
                                        </td>
                                        <td>{{ $log->model_name }}</td>
                                        <td>{{ $log->model_id }}</td>
                                        <td>{{ $log->short_description }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>
                                            <a href="{{ route('admin.logs.show', $log) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nenhum log encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    function getActionBadgeClass($action) {
        return match($action) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            default => 'secondary'
        };
    }
@endphp

<style>
/* Correção específica para a paginação na página de logs */
.card .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
}

.card .page-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid var(--bs-border-color);
    color: var(--bs-body-color);
    background-color: var(--bs-body-bg);
}

.card .page-link:hover {
    background-color: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
}

.card .page-item.active .page-link {
    background-color: var(--primary-500);
    border-color: var(--primary-500);
    color: white;
}

.card .page-item.disabled .page-link {
    color: var(--bs-secondary-color);
    background-color: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
}

/* Garantir que os ícones tenham tamanho correto */
.card .page-link i {
    font-size: 0.875rem;
}

/* Badges corrigidas para Bootstrap 5 */
.badge {
    font-size: 0.75em;
    font-weight: 600;
    padding: 0.35em 0.65em;
}
</style>