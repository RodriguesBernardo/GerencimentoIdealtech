@extends('layouts.app')

@section('title', 'Agenda de Atendimentos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Agenda de Atendimentos</h5>
                    @if(Auth::user()->is_admin || Auth::user()->hasPermission('create_calendar_events'))
                    <button class="btn btn-pipa-red" data-bs-toggle="modal" data-bs-target="#atendimentoModal">
                        <i class="fas fa-plus me-2"></i> Novo Atendimento
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="agendado">Agendado</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterFuncionario" class="form-select">
                                <option value="">Todos os funcionários</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}">{{ $funcionario->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para criar/editar atendimento -->
@if(Auth::user()->is_admin || Auth::user()->hasPermission('create_calendar_events'))
<div class="modal fade" id="atendimentoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="atendimentoForm">
                <div class="modal-body">
                    <input type="hidden" id="atendimentoId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cliente_id" class="form-label">Cliente *</label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}">{{ $cliente->nome }} - {{ $cliente->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Responsável *</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Selecione o responsável</option>
                                    @foreach($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}">{{ $funcionario->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título do Atendimento *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ex: Manutenção preventiva" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreva o propósito do atendimento..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Data e Hora Início *</label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_fim" class="form-label">Data e Hora Fim *</label>
                                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="agendado">Agendado</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Atendimento *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="presencial">Presencial</option>
                                    <option value="online">Online</option>
                                    <option value="telefone">Telefone</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="local" class="form-label">Local / Endereço</label>
                        <input type="text" class="form-control" id="local" name="local" placeholder="Endereço ou link da reunião">
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="2" placeholder="Observações adicionais..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="cor" class="form-label">Cor do Evento</label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <div class="color-option" data-color="#087c04" style="background-color: #087c04;"></div>
                            <div class="color-option" data-color="#4a6fdc" style="background-color: #4a6fdc;"></div>
                            <div class="color-option" data-color="#e67e22" style="background-color: #e67e22;"></div>
                            <div class="color-option" data-color="#b82424" style="background-color: #b82424;"></div>
                            <div class="color-option" data-color="#9b59b6" style="background-color: #9b59b6;"></div>
                        </div>
                        <input type="hidden" id="cor" name="cor" value="#087c04">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-pipa-red">Salvar Atendimento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal para visualizar atendimento -->
<div class="modal fade" id="viewAtendimentoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="viewColorBadge" style="height: 8px; border-radius: 4px;"></div>
                
                <h6 id="viewTitulo" class="mb-2"></h6>
                <p id="viewDescricao" class="text-muted"></p>
                
                <div class="row mt-3">
                    <div class="col-6">
                        <strong>Início:</strong>
                        <span id="viewDataInicio"></span>
                    </div>
                    <div class="col-6">
                        <strong>Fim:</strong>
                        <span id="viewDataFim"></span>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-6">
                        <strong>Cliente:</strong>
                        <span id="viewCliente"></span>
                    </div>
                    <div class="col-6">
                        <strong>Responsável:</strong>
                        <span id="viewResponsavel"></span>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-6">
                        <strong>Status:</strong>
                        <span id="viewStatus" class="badge"></span>
                    </div>
                    <div class="col-6">
                        <strong>Tipo:</strong>
                        <span id="viewTipo"></span>
                    </div>
                </div>
                
                <div class="mt-2">
                    <strong>Local:</strong>
                    <span id="viewLocal"></span>
                </div>
                
                <div class="mt-3">
                    <strong>Observações:</strong>
                    <p id="viewObservacoes" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                @if(Auth::user()->is_admin || Auth::user()->hasPermission('edit_calendar_events'))
                <button type="button" class="btn btn-pipa-red" id="editAtendimentoBtn">Editar</button>
                @endif
                @if(Auth::user()->is_admin || Auth::user()->hasPermission('delete_calendar_events'))
                <button type="button" class="btn btn-danger" id="deleteAtendimentoBtn">Excluir</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        height: 700px;
        font-family: 'Inter', sans-serif;
    }
    
    .fc-event {
        cursor: pointer;
        border: none;
        border-radius: 6px;
        padding: 2px 4px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .badge-agendado { background-color: #6c757d; }
    .badge-confirmado { background-color: #17a2b8; }
    .badge-em_andamento { background-color: #ffc107; color: #000; }
    .badge-concluido { background-color: #28a745; }
    .badge-cancelado { background-color: #dc3545; }
    
    .color-option {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        transition: var(--transition-base);
        border: 2px solid transparent;
    }
    
    .color-option:hover, .color-option.selected {
        transform: scale(1.2);
        border-color: var(--dark-color);
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js'></script>
<script>
  let calendar;
let currentAtendimentoId = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    setupEventListeners();
});

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        events: {
            url: '/api/atendimentos-events', // CORRIGIDO - com barra no início
            extraParams: function() {
                return {
                    status: $('#filterStatus').val(),
                    user_id: $('#filterFuncionario').val()
                };
            },
            failure: function(error) {
                console.error('Erro ao carregar eventos:', error);
                showToast('Erro ao carregar eventos do calendário', 'error');
            }
        },
        eventClick: function(info) {
            viewAtendimento(info.event);
        },
        dateClick: function(info) {
            @if(Auth::user()->is_admin || Auth::user()->hasPermission('create_calendar_events'))
            createAtendimento(info.dateStr);
            @endif
        },
        eventDidMount: function(info) {
            // Aplicar estilo baseado no status
            const status = info.event.extendedProps.status;
            info.el.classList.add(`event-status-${status}`);
            
            if (info.event.extendedProps.cor) {
                info.el.style.borderLeft = `4px solid ${info.event.extendedProps.cor}`;
            }
        }
    });
    calendar.render();
}

function setupEventListeners() {
    // Filtros
    $('#filterStatus, #filterFuncionario').change(function() {
        calendar.refetchEvents();
    });

    // Seleção de cor
    $('.color-option').click(function() {
        $('.color-option').removeClass('selected');
        $(this).addClass('selected');
        $('#cor').val($(this).data('color'));
    });

    // Formulário
    $('#atendimentoForm').submit(function(e) {
        e.preventDefault();
        saveAtendimento();
    });

    $('#editAtendimentoBtn').click(function() {
        editAtendimento(currentAtendimentoId);
    });

    $('#deleteAtendimentoBtn').click(function() {
        deleteAtendimento(currentAtendimentoId);
    });
}

function createAtendimento(dateStr) {
    $('#atendimentoForm')[0].reset();
    $('#atendimentoId').val('');
    $('.color-option').first().addClass('selected');
    $('#cor').val('#087c04');
    
    // Preencher data/hora
    const startDate = new Date(dateStr + 'T09:00:00');
    const endDate = new Date(dateStr + 'T10:00:00');
    
    $('#data_inicio').val(formatDateForInput(startDate));
    $('#data_fim').val(formatDateForInput(endDate));
    
    $('#atendimentoModal').modal('show');
}

function viewAtendimento(event) {
    fetch('/atendimentos/' + event.id) // CORRIGIDO - com barra no início
        .then(response => response.json())
        .then(data => {
            $('#viewTitulo').text(data.atendimento.titulo);
            $('#viewDescricao').text(data.atendimento.descricao || 'Sem descrição');
            $('#viewDataInicio').text(new Date(data.atendimento.data_inicio).toLocaleString('pt-BR'));
            $('#viewDataFim').text(new Date(data.atendimento.data_fim).toLocaleString('pt-BR'));
            $('#viewCliente').text(data.cliente.nome);
            $('#viewResponsavel').text(data.responsavel.name);
            $('#viewStatus').text(getStatusText(data.atendimento.status)).addClass(`badge-${data.atendimento.status}`);
            $('#viewTipo').text(getTipoText(data.atendimento.tipo));
            $('#viewLocal').text(data.atendimento.local || 'Não informado');
            $('#viewObservacoes').text(data.atendimento.observacoes || 'Nenhuma observação');
            
            if (data.atendimento.cor) {
                $('#viewColorBadge').css('background-color', data.atendimento.cor);
            }
            
            currentAtendimentoId = event.id;
            $('#viewAtendimentoModal').modal('show');
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro ao carregar atendimento', 'error');
        });
}

function editAtendimento(atendimentoId) {
    fetch('/atendimentos/' + atendimentoId + '/edit') // CORRIGIDO - com barra no início
        .then(response => response.json())
        .then(data => {
            // Preencher formulário com dados do atendimento
            Object.keys(data.atendimento).forEach(key => {
                $(`#${key}`).val(data.atendimento[key]);
            });
            
            // Selecionar cor
            $('.color-option').removeClass('selected');
            $(`.color-option[data-color="${data.atendimento.cor || '#087c04'}"]`).addClass('selected');
            
            $('#atendimentoModal').modal('show');
            $('#viewAtendimentoModal').modal('hide');
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro ao carregar atendimento para edição', 'error');
        });
}

function saveAtendimento() {
    const formData = new FormData(document.getElementById('atendimentoForm'));
    const atendimentoId = $('#atendimentoId').val();
    const url = atendimentoId ? '/atendimentos/' + atendimentoId : '/atendimentos'; // CORRIGIDO - com barra no início
    const method = atendimentoId ? 'PUT' : 'POST';

    // Mostrar loading no botão
    const submitButton = $('#atendimentoForm').find('button[type="submit"]');
    const originalText = submitButton.html();
    submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Erro ao salvar atendimento');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            $('#atendimentoModal').modal('hide');
            showToast(data.message, 'success');
            
            // FORÇAR a atualização do calendário
            calendar.refetchEvents();
            
        } else {
            throw new Error(data.error || 'Erro ao salvar atendimento');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast(error.message, 'error');
    })
    .finally(() => {
        // Restaurar botão
        submitButton.prop('disabled', false).html(originalText);
    });
}

function deleteAtendimento(atendimentoId) {
    if (confirm('Tem certeza que deseja excluir este atendimento?')) {
        fetch('/atendimentos/' + atendimentoId, { // CORRIGIDO - com barra no início
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            $('#viewAtendimentoModal').modal('hide');
            showToast('Atendimento excluído com sucesso!', 'success');
            calendar.refetchEvents();
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro ao excluir atendimento', 'error');
        });
    }
}

function getStatusText(status) {
    const statusMap = {
        'agendado': 'Agendado',
        'confirmado': 'Confirmado',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusMap[status] || status;
}

function getTipoText(tipo) {
    const tipoMap = {
        'presencial': 'Presencial',
        'online': 'Online',
        'telefone': 'Telefone'
    };
    return tipoMap[tipo] || tipo;
}

function formatDateForInput(date) {
    return date.toISOString().slice(0, 16);
}

function showToast(message, type = 'success') {
    // Remover toasts anteriores
    $('.alert-toast').remove();
    
    // Ícone baseado no tipo
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    // Criar toast
    const toast = $(`
        <div class="alert ${alertClass} alert-dismissible fade show alert-toast position-fixed" 
            style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Remover automaticamente após 5 segundos
    setTimeout(() => {
        toast.alert('close');
    }, 5000);
}

</script>
@endpush