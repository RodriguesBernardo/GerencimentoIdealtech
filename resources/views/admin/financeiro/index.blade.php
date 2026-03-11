@extends('layouts.app')

@section('title', 'Financeiro')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <span class="text-secondary">Visão geral do caixa da empresa</span>
        </div>
        
        <div class="d-flex flex-wrap align-items-center gap-3">
            <form action="{{ route('admin.financeiro.index') }}" method="GET" class="d-flex gap-2">
                <select name="mes" class="form-select border-0 shadow-sm fw-bold text-secondary rounded-pill px-4" onchange="this.form.submit()" style="background-color: #f8f9fa;">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ $i }}" {{ $mes == $i ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::create()->month($i)->locale('pt_BR')->monthName) }}
                        </option>
                    @endfor
                </select>
                <select name="ano" class="form-select border-0 shadow-sm fw-bold text-secondary rounded-pill px-4" onchange="this.form.submit()" style="background-color: #f8f9fa;">
                    <option value="2025" {{ $ano == 2025 ? 'selected' : '' }}>2025</option>
                    <option value="2026" {{ $ano == 2026 ? 'selected' : '' }}>2026</option>
                </select>
            </form>

            <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportarOfx">
                <i class="fas fa-file-import me-2"></i> Importar Extrato
            </button>
        </div>
    </div>

    {{-- CARDS DE RESUMO --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative bg-primary text-white">
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 text-uppercase fw-bold opacity-75 small">Serviços Recebidos</p>
                    <h3 class="fw-bold mb-0">R$ {{ number_format($ganhoServicosRegistrados, 2, ',', '.') }}</h3>
                    <small class="opacity-75"><i class="fas fa-laptop-code me-1"></i> Registrados no sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative text-white" style="background: linear-gradient(135deg, #059669 0%, #34d399 100%);">
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 text-uppercase fw-bold opacity-75 small">Entradas Extrato</p>
                    <h3 class="fw-bold mb-0">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h3>
                    <small class="opacity-75"><i class="fas fa-arrow-up me-1"></i> Dinheiro na conta</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative text-white" style="background: linear-gradient(135deg, #dc2626 0%, #f87171 100%);">
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 text-uppercase fw-bold opacity-75 small">Despesas Extrato</p>
                    <h3 class="fw-bold mb-0">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h3>
                    <small class="opacity-75"><i class="fas fa-arrow-down me-1"></i> Saídas bancárias</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative bg-dark text-white">
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 text-uppercase fw-bold opacity-75 small">Saldo Operacional</p>
                    <h3 class="fw-bold mb-0">R$ {{ number_format($saldo, 2, ',', '.') }}</h3>
                    <small class="opacity-75">
                        @if($saldo >= 0) <i class="fas fa-smile me-1"></i> Empresa no Azul
                        @else <i class="fas fa-exclamation-triangle me-1"></i> Atenção ao fluxo
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICO E CATEGORIAS --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 text-secondary">Comparativo Anual ({{ $ano }})</h5>
                    <small class="text-muted">Desempenho da empresa mês a mês</small>
                </div>
                <div class="card-body p-4">
                    <canvas id="graficoComparativo" style="max-height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 text-secondary">Despesas do Mês</h5>
                    <small class="text-muted">Distribuição por categoria</small>
                </div>
                <div class="card-body p-4">
                    @forelse($despesasPorCategoria as $categoria => $valor)
                        @php 
                            $percentual = $totalDespesas > 0 ? ($valor / $totalDespesas) * 100 : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-secondary">{{ $categoria }}</span>
                                <span class="small fw-bold">R$ {{ number_format($valor, 2, ',', '.') }}</span>
                            </div>
                            <div class="progress rounded-pill " style="height: 6px;">
                                <div class="progress-bar rounded-pill bg-danger" role="progressbar" style="width: {{ $percentual }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Nenhuma despesa no mês selecionado.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS E TABELA MODERNA --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <h5 class="fw-bold mb-0 text-secondary">
                    Lançamentos de {{ ucfirst(\Carbon\Carbon::create()->month((int) $mes)->locale('pt_BR')->monthName) }} / {{ $ano }}
                </h5>
                
                {{-- Filtros Rápidos --}}
                <form action="{{ route('admin.financeiro.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="mes" value="{{ $mes }}">
                    <input type="hidden" name="ano" value="{{ $ano }}">
                    
                    <input type="text" name="search" class="form-control form-control-sm border-secondary border-opacity-25 rounded-pill px-3" placeholder="Buscar..." value="{{ request('search') }}" style="min-width: 150px;">
                    
                    <select name="tipo" class="form-select form-select-sm border-secondary border-opacity-25 rounded-pill px-3" onchange="this.form.submit()">
                        <option value="">Tipos</option>
                        <option value="receita" {{ request('tipo') == 'receita' ? 'selected' : '' }}>Entradas</option>
                        <option value="despesa" {{ request('tipo') == 'despesa' ? 'selected' : '' }}>Saídas</option>
                    </select>

                    {{-- NOVO: Filtro de Categoria --}}
                    <select name="categoria" class="form-select form-select-sm border-secondary border-opacity-25 rounded-pill px-3" onchange="this.form.submit()" style="max-width: 200px;">
                        <option value="">Categorias</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <select name="ordenacao" class="form-select form-select-sm border-secondary border-opacity-25 rounded-pill px-3" onchange="this.form.submit()">
                        <option value="data_desc" {{ isset($ordenacao) && $ordenacao == 'data_desc' ? 'selected' : '' }}>Recentes</option>
                        <option value="data_asc" {{ isset($ordenacao) && $ordenacao == 'data_asc' ? 'selected' : '' }}>Antigos</option>
                        <option value="valor_desc" {{ isset($ordenacao) && $ordenacao == 'valor_desc' ? 'selected' : '' }}>Maior Valor</option>
                        <option value="valor_asc" {{ isset($ordenacao) && $ordenacao == 'valor_asc' ? 'selected' : '' }}>Menor Valor</option>
                    </select>
                    
                    @if(request()->hasAny(['search', 'tipo', 'categoria', 'ordenacao']))
                        <a href="{{ route('admin.financeiro.index', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-sm btn-light rounded-pill px-3 border">Limpar</a>
                    @endif
                </form>
            </div>
        </div>
        
        <div class="card-body p-0 mt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class=" text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0">Data</th>
                            <th class="py-3 border-0">Descrição</th>
                            <th class="py-3 border-0">Categoria</th>
                            <th class="py-3 border-0 text-center">Tipo</th>
                            <th class="py-3 border-0 text-end">Valor</th>
                            <th class="py-3 border-0 text-center pe-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($movimentacoes as $mov)
                        <tr>
                            <td class="ps-4 fw-medium text-secondary" style="width: 120px;">
                                {{ $mov->data_vencimento->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="fw-bold">{{ $mov->descricao }}</div>
                                @if($mov->descricao_original)
                                    <div class="text-muted" style="font-size: 0.65rem;" title="Lançamento Original: {{ $mov->descricao_original }}">
                                        Orig: {{ \Illuminate\Support\Str::limit($mov->descricao_original, 45) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border fw-normal px-3">
                                    {{ $mov->categoria }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($mov->tipo == 'receita')
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success px-3">
                                        <i class="fas fa-arrow-up me-1"></i> ENTRADA
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger px-3">
                                        <i class="fas fa-arrow-down me-1"></i> SAÍDA
                                    </span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $mov->tipo == 'receita' ? 'text-success' : 'text-danger' }}">
                                {{ $mov->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- NOVO: Botão de Visualizar --}}
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 border-0 bg-transparent" title="Visualizar" onclick='abrirVisualizarMovimentacao(@json($mov))'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.financeiro.destroy', $mov->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação? O saldo será recalculado.')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 bg-transparent" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3 opacity-25"><i class="fas fa-search fa-3x"></i></div>
                                Nenhuma movimentação encontrada com estes filtros.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPORTAÇÃO OFX --}}
<div class="modal fade" id="modalImportarOfx" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-secondary">Conciliar Extrato OFX</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pt-4 pb-4">                
                <div id="stepUpload">
                    <form id="formUploadOfx">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary rounded-3 small mb-4">
                            <i class="fas fa-robot me-2"></i> Nossa Inteligência Artificial limpará as descrições e categorizará as despesas.
                        </div>

                        <div class="mb-4">
                            <label class="small text-secondary fw-bold mb-2">Selecione o arquivo OFX do banco</label>
                            <input class="form-control rounded-3 border-secondary border-opacity-25 p-2" type="file" id="arquivo_ofx" accept=".ofx" required>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-5 shadow-sm fw-bold" id="btnAnalisar">
                                Analisar Arquivo
                            </button>
                        </div>
                    </form>
                </div>
                <div id="stepRevisao" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-secondary">Revise as informações ajustadas pela IA</span>
                    </div>

                    <div class="table-responsive border rounded-3" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class=" sticky-top">
                                <tr>
                                    <th class="ps-3">Data</th>
                                    <th>Descrição (Limpa)</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center pe-3">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaRevisao">
                                </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none" onclick="voltarUpload()">
                            <i class="fas fa-arrow-left me-2"></i> Voltar
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-5 shadow-sm fw-bold" id="btnSalvarLote" onclick="salvarOfxLote()">
                            Salvar Lançamentos
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- NOVO: MODAL VISUALIZAR DETALHES --}}
<div class="modal fade" id="modalVisualizarMovimentacao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-secondary">Detalhes da Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pt-4 pb-4">
                <div class="mb-4">
                    <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Descrição Limpa</small>
                    <span id="vis-descricao" class="fw-bold fs-5"></span>
                </div>
                <div class="mb-4  p-3 rounded-3 border">
                    <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Descrição Original (Extrato do Banco)</small>
                    <span id="vis-descricao-original" class="text-secondary font-monospace" style="font-size: 0.85rem; word-break: break-all;"></span>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Data</small>
                        <span id="vis-data" class="fw-medium"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Categoria</small>
                        <span id="vis-categoria" class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal px-3 py-2"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tipo</small>
                        <span id="vis-tipo"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Valor</small>
                        <span id="vis-valor" class="fw-bold fs-4"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light border rounded-pill px-5 w-100 fw-bold" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Inicializar Modal de Visualização (Nativo Bootstrap 5)
let modalVisualizador = null;
document.addEventListener("DOMContentLoaded", function() {
    modalVisualizador = new bootstrap.Modal(document.getElementById('modalVisualizarMovimentacao'));
});

// === Função de Visualizar ===
function abrirVisualizarMovimentacao(mov) {
    // 1. Formatar a Data (De YYYY-MM-DD para DD/MM/YYYY)
    const [ano, mes, dia] = mov.data_vencimento.split('T')[0].split('-');
    document.getElementById('vis-data').innerText = `${dia}/${mes}/${ano}`;
    
    // 2. Formatar Valor
    const valorFloat = parseFloat(mov.valor);
    const valorFormatado = valorFloat.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // 3. Preencher Textos
    document.getElementById('vis-descricao').innerText = mov.descricao || '-';
    document.getElementById('vis-descricao-original').innerText = mov.descricao_original || 'Sem registro original do banco';
    document.getElementById('vis-categoria').innerText = mov.categoria || 'Não Categorizado';
    
    // 4. Lógica Visual para Tipo/Valor
    const tipoEl = document.getElementById('vis-tipo');
    const valorEl = document.getElementById('vis-valor');
    
    if (mov.tipo === 'receita') {
        tipoEl.innerHTML = '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success px-3 py-2"><i class="fas fa-arrow-up me-1"></i> ENTRADA</span>';
        valorEl.innerText = '+ R$ ' + valorFormatado;
        valorEl.className = 'fw-bold fs-4 text-success';
    } else {
        tipoEl.innerHTML = '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2"><i class="fas fa-arrow-down me-1"></i> SAÍDA</span>';
        valorEl.innerText = '- R$ ' + valorFormatado;
        valorEl.className = 'fw-bold fs-4 text-danger';
    }
    
    // Mostra o modal
    modalVisualizador.show();
}

// === GRÁFICO ===
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('graficoComparativo');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($graficoLabels ?? []) !!},
                datasets: [
                    {
                        label: 'Serviços Registrados',
                        data: {!! json_encode($graficoServicos ?? []) !!},
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Receitas (Extrato)',
                        data: {!! json_encode($graficoReceitas ?? []) !!},
                        borderColor: '#198754',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.3
                    },
                    {
                        label: 'Despesas (Extrato)',
                        data: {!! json_encode($graficoDespesas ?? []) !!},
                        borderColor: '#dc3545',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => 'R$ ' + value.toLocaleString('pt-BR') }
                    }
                }
            }
        });
    }
});

let lancamentosPendentes = [];

document.getElementById('formUploadOfx').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('arquivo_ofx');
    const btn = document.getElementById('btnAnalisar');
    if (fileInput.files.length === 0) return;

    const formData = new FormData();
    formData.append('arquivo_ofx', fileInput.files[0]);

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processando IA...';
    btn.disabled = true;

    fetch('{{ route("admin.financeiro.analisar_ofx") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) return Promise.reject(data.message || 'Erro no servidor.');
        return data;
    })
    .then(data => {
        if (data.success) {
            lancamentosPendentes = data.dados;
            renderizarTabelaRevisao();
            document.getElementById('stepUpload').classList.add('d-none');
            document.getElementById('stepRevisao').classList.remove('d-none');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('Falha na requisição:\n' + err);
    })
    .finally(() => {
        btn.innerHTML = 'Analisar Arquivo';
        btn.disabled = false;
    });
});

function renderizarTabelaRevisao() {
    const tbody = document.getElementById('tabelaRevisao');
    tbody.innerHTML = '';    
    const categorias = ['Receita de Serviços', 'Fornecedores', 'Impostos e Taxas', 'Tarifas Bancárias', 'Salários e Pró-labore', 'Equipamentos', 'Marketing', 'Hospedagem/Software', 'Custos de Operação', 'Fatura', 'Outros'];

    lancamentosPendentes.forEach((lanc, index) => {
        let options = categorias.map(c => `<option value="${c}" ${c === lanc.categoria ? 'selected' : ''}>${c}</option>`).join('');
        if (!categorias.includes(lanc.categoria)) {
            options += `<option value="${lanc.categoria}" selected>${lanc.categoria}</option>`;
        }

        const corValor = lanc.tipo === 'receita' ? 'text-success' : 'text-danger';
        const sinal = lanc.tipo === 'receita' ? '+' : '-';
        
        const descOriginal = lanc.descricao_original || lanc.descricao;
        const textoOriginalPequeno = descOriginal.length > 40 ? descOriginal.substring(0, 40) + '...' : descOriginal;
        
        tbody.innerHTML += `
            <tr id="row_${index}">
                <td class="ps-3"><input type="date" class="form-control form-control-sm border-0" value="${lanc.data_vencimento}" onchange="atualizarLancamento(${index}, 'data_vencimento', this.value)"></td>
                
                <td>
                    <input type="text" class="form-control form-control-sm border-0 fw-bold" value="${lanc.descricao}" onchange="atualizarLancamento(${index}, 'descricao', this.value)">
                    <small class="text-muted d-block ms-2" style="font-size:0.65rem" title="${descOriginal}">Orig: ${textoOriginalPequeno}</small>
                </td>
                
                <td>
                    <select class="form-select form-select-sm border-secondary border-opacity-25" onchange="atualizarLancamento(${index}, 'categoria', this.value)">
                        ${options}
                    </select>
                </td>
                
                <td class="text-end fw-bold ${corValor}">${sinal} R$ ${parseFloat(lanc.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                
                <td class="text-center pe-3">
                    <button class="btn btn-sm btn-link text-danger p-0 m-0" onclick="removerLinha(${index})"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });
}

function atualizarLancamento(index, campo, valor) {
    lancamentosPendentes[index][campo] = valor;
}

function removerLinha(index) {
    lancamentosPendentes.splice(index, 1);
    renderizarTabelaRevisao();
}

function voltarUpload() {
    document.getElementById('stepRevisao').classList.add('d-none');
    document.getElementById('stepUpload').classList.remove('d-none');
    document.getElementById('arquivo_ofx').value = '';
}

function salvarOfxLote() {
    if (lancamentosPendentes.length === 0) return alert('Nenhum lançamento para salvar.');

    const btn = document.getElementById('btnSalvarLote');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Salvando...';
    btn.disabled = true;

    fetch('{{ route("admin.financeiro.salvar_lote") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ lancamentos: lancamentosPendentes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload(); 
        } else {
            alert(data.message || 'Erro ao salvar.');
        }
    })
    .catch(() => alert('Erro de conexão com o servidor.'))
    .finally(() => {
        btn.innerHTML = 'Salvar Lançamentos';
        btn.disabled = false;
    });
}
</script>
@endpush