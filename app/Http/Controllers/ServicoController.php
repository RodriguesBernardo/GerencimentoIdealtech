<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Exports\ServicosExport;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf; 
class ServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = Servico::with(['cliente', 'parcelasServico']);

        // Definir datas padrão (mês atual) - ESTAS SÃO AS DATAS PARA OS INSIGHTS
        $dataInicial = $request->data_inicial ?? now()->startOfMonth()->format('Y-m-d');
        $dataFinal = $request->data_final ?? now()->endOfMonth()->format('Y-m-d');

        // **QUERY PARA FILTRAGEM NA LISTA** (com todos os filtros)
        $queryFiltrada = clone $query;
        
        // Aplicar filtros apenas na query da listagem
        $queryFiltrada->whereBetween('data_servico', [$dataInicial, $dataFinal]);

        if ($request->search) {
            $queryFiltrada->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $queryFiltrada->where('status_pagamento', $request->status);
        }

        if ($request->tipo_pagamento) {
            $queryFiltrada->where('tipo_pagamento', $request->tipo_pagamento);
        }

        // Paginação apenas na query filtrada
        $servicos = $queryFiltrada->orderBy('data_servico', 'DESC')
                                ->orderBy('created_at', 'DESC')
                                ->paginate(15);

        // **INSIGHTS: usar query separada com apenas o filtro de período**
        $queryInsights = Servico::with(['cliente', 'parcelasServico'])
                            ->whereBetween('data_servico', [$dataInicial, $dataFinal]);
        
        $insights = auth()->user()->is_admin ? $this->calcularInsightsComParcelas($queryInsights) : [];

        return view('servicos.index', compact('servicos', 'insights', 'dataInicial', 'dataFinal'));
    }

    private function calcularInsightsComParcelas($query)
    {
        // Garantir que pegamos todos os registros (sem paginação)
        $servicosComParcelas = $query->with('parcelasServico')->get();
        
        // Cálculos considerando as parcelas
        $totalPago = 0;
        $totalPendente = 0;
        $totalDevedor = 0;
        $valorTotal = 0;
        
        foreach ($servicosComParcelas as $servico) {
            $valorTotal += $servico->valor ?? 0;
            
            if ($servico->tipo_pagamento == 'avista') {
                // Serviço à vista
                if ($servico->status_pagamento == 'pago') {
                    $totalPago += $servico->valor;
                } elseif ($servico->status_pagamento == 'pendente') {
                    $totalPendente += $servico->valor;
                    $totalDevedor += $servico->valor;
                } elseif ($servico->status_pagamento == 'nao_pago') {
                    $totalDevedor += $servico->valor;
                }
            } else {
                // Serviço parcelado - calcular baseado nas parcelas
                $parcelasPagas = $servico->parcelasServico->where('status', 'paga');
                $parcelasPendentes = $servico->parcelasServico->where('status', 'pendente');
                $parcelasNaoPagas = $servico->parcelasServico->where('status', 'nao_paga');
                
                $totalPago += $parcelasPagas->sum('valor_parcela');
                $totalPendente += $parcelasPendentes->sum('valor_parcela');
                $totalDevedor += $parcelasPendentes->sum('valor_parcela') + $parcelasNaoPagas->sum('valor_parcela');
            }
        }

        return [
            'total_clientes' => Cliente::count(),
            'total_servicos' => $servicosComParcelas->count(),
            'valor_total' => $valorTotal,
            'valor_mes_atual' => $valorTotal, // Já está filtrado pelo período
            'valor_ano_atual' => Servico::whereYear('data_servico', now()->year)->sum('valor') ?? 0,
            'total_devedor' => $totalDevedor,
            'total_pago' => $totalPago,
            'total_pendente' => $totalPendente,
        ];
    }

    private function calcularInsights($query)
    {
        return [
            'total_clientes' => Cliente::count(),
            'total_servicos' => $query->count(),
            'valor_total' => $query->sum('valor') ?? 0,
            'valor_mes_atual' => $query->whereMonth('data_servico', now()->month)
                                     ->whereYear('data_servico', now()->year)
                                     ->sum('valor') ?? 0,
            'valor_ano_atual' => $query->whereYear('data_servico', now()->year)
                                      ->sum('valor') ?? 0,
            'total_devedor' => $query->whereIn('status_pagamento', ['pendente', 'nao_pago'])
                                    ->sum('valor') ?? 0,
            'total_pago' => $query->where('status_pagamento', 'pago')->sum('valor') ?? 0,
        ];
    }

    private function calcularValorMesAtual($query)
    {
        $mesAtual = now()->month;
        $anoAtual = now()->year;
        
        $queryMes = clone $query;
        return $queryMes->whereMonth('data_servico', $mesAtual)
                       ->whereYear('data_servico', $anoAtual)
                       ->sum('valor');
    }

    private function calcularValorAnoAtual($query)
    {
        $anoAtual = now()->year;
        
        $queryAno = clone $query;
        return $queryAno->whereYear('data_servico', $anoAtual)
                       ->sum('valor');
    }

    private function calcularTotalDevedor($query)
    {
        $queryDevedor = clone $query;
        return $queryDevedor->whereIn('status_pagamento', ['pendente', 'nao_pago'])
                           ->sum('valor');
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado. Apenas administradores podem exportar.');
        }

        // Aplicar filtro de data
        $dataInicial = $request->data_inicial ?? now()->startOfMonth()->format('Y-m-d');
        $dataFinal = $request->data_final ?? now()->endOfMonth()->format('Y-m-d');

        // Query para os serviços
        $query = Servico::with(['cliente', 'parcelasServico'])
            ->whereBetween('data_servico', [$dataInicial, $dataFinal]);
        
        if ($request->search) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status_pagamento', $request->status);
        }
        
        if ($request->tipo_pagamento) {
            $query->where('tipo_pagamento', $request->tipo_pagamento);
        }

        $servicos = $query->latest('data_servico')->get();

        // Calcular insights considerando as parcelas - CÁLCULOS CORRETOS
        $totalPago = 0;
        $totalPendente = 0;
        $totalDevedor = 0;
        
        foreach ($servicos as $servico) {
            if ($servico->tipo_pagamento == 'avista') {
                // Serviço à vista
                if ($servico->status_pagamento == 'pago') {
                    $totalPago += $servico->valor;
                } elseif ($servico->status_pagamento == 'pendente') {
                    $totalPendente += $servico->valor;
                    $totalDevedor += $servico->valor;
                } elseif ($servico->status_pagamento == 'nao_pago') {
                    $totalDevedor += $servico->valor;
                }
            } else {
                // Serviço parcelado - calcular baseado nas parcelas
                $parcelasPagas = $servico->parcelasServico->where('status', 'paga');
                $parcelasPendentes = $servico->parcelasServico->where('status', 'pendente');
                
                $totalPago += $parcelasPagas->sum('valor_parcela');
                $totalPendente += $parcelasPendentes->sum('valor_parcela');
                $totalDevedor += $parcelasPendentes->sum('valor_parcela');
            }
        }

        $insights = [
            'total_clientes' => Cliente::count(),
            'total_servicos' => $servicos->count(),
            'valor_total' => $servicos->sum('valor') ?? 0,
            'valor_mes_atual' => $servicos->sum('valor') ?? 0,
            'total_devedor' => $totalDevedor,
            'total_pago' => $totalPago,
            'total_pendente' => $totalPendente,
        ];

        $pdf = Pdf::loadView('servicos.pdf', compact('servicos', 'insights'));
        return $pdf->download('servicos-' . now()->format('d-m-Y') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado. Apenas administradores podem exportar.');
        }

        return Excel::download(new ServicosExport($request), 'servicos-' . now()->format('d-m-Y') . '.xlsx');
    }
    /**
     * Obter serviços filtrados para exportação
     */
    private function getServicosFiltrados(Request $request)
    {
        $query = Servico::with(['cliente', 'parcelasServico']);

        // Aplicar os mesmos filtros da index
        if ($request->search) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status_pagamento', $request->status);
        }

        if ($request->tipo_pagamento) {
            $query->where('tipo_pagamento', $request->tipo_pagamento);
        }

        return $query->latest('data_servico')->get();
    }
    

    public function create()
    {
        // Remova ou comente esta linha que carrega todos os clientes
        // $clientes = Cliente::orderBy('nome')->get();
        
        return view('servicos.create', [
            // 'clientes' => $clientes, // Não precisamos mais disso
        ]);
    }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string',
            'data_servico' => 'required|date',
            'status_pagamento' => 'required|in:pago,nao_pago,pendente',
            'observacao_pagamento' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'tipo_pagamento' => 'required|in:avista,parcelado',
            'parcelas' => 'required|integer|min:1',
            'data_primeiro_vencimento' => 'nullable|date',
            'datas_parcelas' => 'nullable|array',
            'datas_parcelas.*' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'pago_at' => 'nullable|date'
        ]);

        // Remove campos desnecessários
        unset($validated['nome']);

        // Converte valor vazio para null
        if (empty($validated['valor'])) {
            $validated['valor'] = null;
        }

        // Se for à vista, parcelas = 1
        if ($validated['tipo_pagamento'] === 'avista') {
            $validated['parcelas'] = 1;
        }

        // Lógica para data de pagamento
        if ($validated['status_pagamento'] === 'pago' && empty($validated['pago_at'])) {
            $validated['pago_at'] = now();
        } elseif ($validated['status_pagamento'] !== 'pago') {
            $validated['pago_at'] = null;
        }

        // Remove campos extras
        $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
        $datasParcelas = $validated['datas_parcelas'] ?? [];
        unset($validated['data_primeiro_vencimento'], $validated['datas_parcelas']);

        $servico = Servico::create($validated);

        // Cria parcelas se for parcelado
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            $servico->criarParcelas([1 => $dataPrimeiroVencimento] + $datasParcelas);
        }

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function show(Servico $servico)
    {
        // Carrega as parcelas do serviço e o cliente
        $servico->load(['parcelasServico', 'cliente']);

        return view('servicos.show', compact('servico'));
    }


    public function edit(Servico $servico)
    {
        $servico->load('parcelasServico'); 

        return view('servicos.edit', compact('servico'));
    }

    public function update(Request $request, Servico $servico)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string',
            'data_servico' => 'required|date',
            'status_pagamento' => 'required|in:pago,nao_pago,pendente',
            'observacao_pagamento' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'tipo_pagamento' => 'required|in:avista,parcelado',
            'parcelas' => 'required|integer|min:1',
            'data_primeiro_vencimento' => 'nullable|date',
            'datas_parcelas' => 'nullable|array',
            'datas_parcelas.*' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'pago_at' => 'nullable|date'
        ]);

        // Remove o campo 'nome' se ele existir no array validado
        unset($validated['nome']);

        // Converte valor vazio para null
        if (empty($validated['valor'])) {
            $validated['valor'] = null;
        }

        // Se for à vista, parcelas = 1
        if ($validated['tipo_pagamento'] === 'avista') {
            $validated['parcelas'] = 1;
        }

        // Lógica para a data de pagamento
        if ($validated['status_pagamento'] === 'pago') {
            // Se está marcando como pago e não tem data, usa a data atual
            if (empty($validated['pago_at']) && $servico->status_pagamento !== 'pago') {
                $validated['pago_at'] = now();
            }
            // Se está mantendo como pago mas removeu a data, mantém a data existente
            elseif (empty($validated['pago_at']) && $servico->status_pagamento === 'pago') {
                $validated['pago_at'] = $servico->pago_at;
            }
        } else {
            // Se não está como pago, limpa a data
            $validated['pago_at'] = null;
        }

        // Remove campos que não existem na tabela
        $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
        $datasParcelas = $validated['datas_parcelas'] ?? [];
        unset($validated['data_primeiro_vencimento'], $validated['datas_parcelas']);

        $servico->update($validated);

        // Recria as parcelas se necessário
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            // Prepara o array de datas começando do índice 1
            $datasVencimento = [1 => $dataPrimeiroVencimento];
            foreach ($datasParcelas as $index => $data) {
                if ($data) {
                    $datasVencimento[$index] = $data;
                }
            }
            
            $servico->criarParcelas($datasVencimento);
        } else {
            // Se não é parcelado, remove todas as parcelas
            $servico->parcelasServico()->delete();
        }

        return redirect()->route('servicos.show', $servico)
            ->with('success', 'Serviço atualizado com sucesso!');
    }


    public function destroy(Servico $servico)
    {
        $servico->delete();

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }

    public function updatePaymentStatus(Request $request, Servico $servico)
    {
        $request->validate([
            'status_pagamento' => 'required|in:pago,nao_pago,pendente',
            'observacao_pagamento' => 'nullable|string',
            'pago_at' => 'nullable|date'
        ]);

        $data = [
            'status_pagamento' => $request->status_pagamento,
            'observacao_pagamento' => $request->observacao_pagamento
        ];

        // Lógica para a data de pagamento
        if ($request->status_pagamento === 'pago') {
            $data['pago_at'] = $request->pago_at ?? now();
        } else {
            $data['pago_at'] = null;
        }

        $servico->update($data);

        return back()->with('success', 'Status de pagamento atualizado com sucesso!');
    }

    // Novo método para marcar como pago rapidamente
    public function marcarPago(Servico $servico)
    {
        $servico->marcarComoPago();

        return back()->with('success', 'Serviço marcado como pago!');
    }

    public function searchAjax(Request $request)
    {
        $search = $request->get('q');
        
        $clientes = Cliente::when($search, function ($query, $search) {
            return $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%");
        })
        ->orderBy('nome')
        ->limit(20)
        ->get(['id', 'nome', 'email', 'telefone']);

        return response()->json($clientes);
    }

}
