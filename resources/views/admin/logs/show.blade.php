@extends('layouts.app')

@section('title', 'Detalhes do Log')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-eye"></i> Detalhes do Log
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações Básicas</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Data/Hora:</th>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Usuário:</th>
                                    <td>{{ $log->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Ação:</th>
                                        <td>
                                            <span class="badge badge-{{ getActionBadgeClass($log->action) }}">
                                                {{ $log->action_formatted }}
                                            </span>
                                        </td>
                                </tr>
                                <tr>
                                    <th>Modelo:</th>
                                    <td>{{ $log->model_name }} ({{ $log->model_type }})</td>
                                </tr>
                                <tr>
                                    <th>ID do Registro:</th>
                                    <td>{{ $log->model_id }}</td>
                                </tr>
                                <tr>
                                    <th>IP:</th>
                                    <td>{{ $log->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th>User Agent:</th>
                                    <td>{{ $log->user_agent }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Dados da Ação</h6>
                            
                            @if($log->action === 'created')
                                <div class="alert alert-success">
                                    <strong>Novo registro criado</strong>
                                </div>
                                @if($log->new_data)
                                    <pre>{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            
                            @elseif($log->action === 'updated')
                                <div class="alert alert-warning">
                                    <strong>Registro atualizado</strong>
                                </div>
                                <div class="row">
                                    @if($log->old_data)
                                        <div class="col-md-6">
                                            <h6>Dados Antigos:</h6>
                                            <pre>{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                    @if($log->new_data)
                                        <div class="col-md-6">
                                            <h6>Dados Novos:</h6>
                                            <pre>{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            
                            @elseif($log->action === 'deleted')
                                <div class="alert alert-danger">
                                    <strong>Registro excluído</strong>
                                </div>
                                @if($log->old_data)
                                    <pre>{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            
                            @elseif($log->action === 'restored')
                                <div class="alert alert-info">
                                    <strong>Registro restaurado</strong>
                                </div>
                            @endif
                        </div>
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