<?php

namespace App\Http\Controllers;
use App\Models\Servico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Exports\ServicosExport;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\AnexoServico;
use Illuminate\Support\Facades\Storage; 

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
            'pago_at' => 'nullable|date',
            'anexos' => 'nullable|array|max:5', 
            'anexos.*' => 'file|max:10240',
            'descricoes_anexos' => 'nullable|array',
            'descricoes_anexos.*' => 'nullable|string|max:255'
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
        $anexos = $request->file('anexos') ?? [];
        $descricoesAnexos = $validated['descricoes_anexos'] ?? [];
        
        unset($validated['data_primeiro_vencimento'], $validated['datas_parcelas'], $validated['anexos'], $validated['descricoes_anexos']);

        $servico = Servico::create($validated);

        // Cria parcelas se for parcelado
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            $servico->criarParcelas([1 => $dataPrimeiroVencimento] + $datasParcelas);
        }

        // Upload de anexos
        if (!empty($anexos)) {
            $this->processarAnexos($servico, $anexos, $descricoesAnexos);
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
        \Log::info('=== UPDATE SERVICO INICIADO ===');
        \Log::info('Serviço ID: ' . $servico->id);
        \Log::info('Dados recebidos:', $request->except(['anexos'])); // Exclui anexos do log por segurança

        try {
            // Calcula quantos anexos já existem
            $anexosExistentesCount = $servico->anexos()->count();
            $maxAnexosPermitidos = 5;
            $maxNovosAnexos = max(0, $maxAnexosPermitidos - $anexosExistentesCount);

            // Regras base de validação
            $rules = [
                'cliente_id' => 'required|exists:clientes,id',
                'descricao' => 'required|string|max:255',
                'data_servico' => 'required|date',
                'status_pagamento' => 'required|in:pago,nao_pago,pendente',
                'observacao_pagamento' => 'nullable|string|max:500',
                'valor' => 'nullable|numeric|min:0',
                'tipo_pagamento' => 'required|in:avista,parcelado',
                'observacoes' => 'nullable|string',
                'pago_at' => 'nullable|date',
                'anexos' => 'nullable|array|max:' . $maxNovosAnexos,
                'anexos.*' => 'file|max:10240', // 10MB max por arquivo
                'descricoes_anexos' => 'nullable|array',
                'descricoes_anexos.*' => 'nullable|string|max:255'
            ];

            // Validação condicional para campos de parcelamento
            if ($request->tipo_pagamento === 'parcelado') {
                $rules['parcelas'] = 'required|integer|min:2|max:24';
                $rules['data_primeiro_vencimento'] = 'required|date';
                $rules['datas_parcelas'] = 'nullable|array';
                $rules['datas_parcelas.*'] = 'nullable|date';
            } else {
                // Para à vista, parcelas é sempre 1
                $rules['parcelas'] = 'sometimes|integer|min:1|max:1';
            }

            $validated = $request->validate($rules);

            \Log::info('Validação passou, dados validados:', $validated);

            // Remove campos desnecessários
            unset($validated['nome']);

            // Converte valor vazio para null
            if (empty($validated['valor'])) {
                $validated['valor'] = null;
            }

            // Garante que parcelas seja 1 para pagamento à vista
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

            // Remove campos extras antes de atualizar o serviço
            $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
            $datasParcelas = $validated['datas_parcelas'] ?? [];
            $anexos = $request->file('anexos') ?? [];
            $descricoesAnexos = $validated['descricoes_anexos'] ?? [];
            
            unset(
                $validated['data_primeiro_vencimento'], 
                $validated['datas_parcelas'], 
                $validated['anexos'], 
                $validated['descricoes_anexos']
            );

            // Atualiza o serviço
            $servico->update($validated);
            \Log::info('Serviço atualizado com sucesso');

            // Recria as parcelas se necessário
            if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
                // Prepara o array de datas começando do índice 1
                $datasVencimento = [1 => $dataPrimeiroVencimento];
                
                // Adiciona as demais datas das parcelas
                foreach ($datasParcelas as $index => $data) {
                    if ($data) {
                        $datasVencimento[$index] = $data;
                    }
                }
                
                // Garante que temos todas as datas necessárias
                for ($i = 2; $i <= $servico->parcelas; $i++) {
                    if (!isset($datasVencimento[$i]) || empty($datasVencimento[$i])) {
                        // Calcula uma data baseada na primeira parcela + (i-1) meses
                        $dataBase = new \DateTime($dataPrimeiroVencimento);
                        $dataBase->modify('+' . ($i - 1) . ' months');
                        $datasVencimento[$i] = $dataBase->format('Y-m-d');
                    }
                }
                
                // Remove parcelas existentes e cria novas
                $servico->parcelasServico()->delete();
                $servico->criarParcelas($datasVencimento);
                \Log::info('Parcelas recriadas com sucesso');
            } else {
                // Se não é parcelado, remove todas as parcelas
                $servico->parcelasServico()->delete();
                \Log::info('Parcelas removidas (serviço à vista)');
            }

            // Upload de novos anexos (se ainda houver espaço)
            if (!empty($anexos) && $maxNovosAnexos > 0) {
                $this->processarAnexos($servico, $anexos, $descricoesAnexos);
                \Log::info('Anexos processados com sucesso');
            }

            \Log::info('=== UPDATE SERVICO CONCLUÍDO COM SUCESSO ===');

            return redirect()->route('servicos.show', $servico)
                ->with('success', 'Serviço atualizado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação no update:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Erro geral no update do serviço: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->withErrors(['error' => 'Erro ao atualizar serviço: ' . $e->getMessage()])->withInput();
        }
    }

    private function processarAnexos(Servico $servico, $anexos, $descricoesAnexos)
    {
        foreach ($anexos as $index => $anexo) {
            if ($anexo->isValid()) {
                // Verifica se já atingiu o limite de 5 anexos
                if ($servico->anexos()->count() >= 5) {
                    break;
                }

                $nomeOriginal = $anexo->getClientOriginalName();
                $caminho = $anexo->store('anexos_servicos', 'public');
                $descricao = $descricoesAnexos[$index] ?? null;

                $servico->anexos()->create([
                    'nome_arquivo' => $nomeOriginal,
                    'caminho_arquivo' => $caminho,
                    'mime_type' => $anexo->getMimeType(),
                    'tamanho' => $anexo->getSize(),
                    'descricao' => $descricao,
                ]);
            }
        }
    }

    public function destroyAnexo(Servico $servico, AnexoServico $anexo)
    {
        // Verifica se o anexo pertence ao serviço
        if ($anexo->servico_id !== $servico->id) {
            abort(404);
        }

        // Remove o arquivo do storage
        Storage::disk('public')->delete($anexo->caminho_arquivo);

        // Remove o registro do banco
        $anexo->delete();

        return back()->with('success', 'Anexo excluído com sucesso!');
    }

    public function downloadAnexo(Servico $servico, AnexoServico $anexo)
    {
        // Verifica se o anexo pertence ao serviço
        if ($anexo->servico_id !== $servico->id) {
            abort(404);
        }

        return Storage::disk('public')->download($anexo->caminho_arquivo, $anexo->nome_arquivo);
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

    public function storeAnexo(Request $request, Servico $servico)
    {
        // Verifica se já atingiu o limite de 5 anexos
        if ($servico->anexos()->count() >= 5) {
            return back()->with('error', 'Limite máximo de 5 anexos atingido para este serviço.');
        }

        $request->validate([
            'anexo' => 'required|file|max:10240', // 10MB max
            'descricao' => 'nullable|string|max:255'
        ]);

        try {
            $anexo = $request->file('anexo');
            
            if ($anexo->isValid()) {
                $nomeOriginal = $anexo->getClientOriginalName();
                $caminho = $anexo->store('anexos_servicos', 'public');
                $descricao = $request->descricao;

                $servico->anexos()->create([
                    'nome_arquivo' => $nomeOriginal,
                    'caminho_arquivo' => $caminho,
                    'mime_type' => $anexo->getMimeType(),
                    'tamanho' => $anexo->getSize(),
                    'descricao' => $descricao,
                ]);

                return back()->with('success', 'Anexo adicionado com sucesso!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao fazer upload do anexo: ' . $e->getMessage());
        }

        return back()->with('error', 'Erro ao processar o arquivo.');
    }

    
}
