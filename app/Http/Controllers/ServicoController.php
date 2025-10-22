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
            $queryFiltrada->whereHas('cliente', function ($q) use ($request) {
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
            $query->whereHas('cliente', function ($q) use ($request) {
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

    public function create()
    {
        return view('servicos.create');
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
            'valores_parcelas' => 'nullable|array',
            'valores_parcelas.*' => 'nullable|numeric|min:0.01',
            'observacoes' => 'nullable|string',
            'pago_at' => 'nullable|date',
            'anexos' => 'nullable|array|max:5',
            'anexos.*' => 'file|max:10240',
            'descricoes_anexos' => 'nullable|array',
            'descricoes_anexos.*' => 'nullable|string|max:255',
            'servico_recorrente' => 'nullable|boolean',
            'frequencia' => 'nullable|in:mensal,bimestral,trimestral,semestral,anual',
            'quantidade_repeticoes' => 'nullable|integer|min:1|max:60',
            'data_final' => 'nullable|date|after:data_servico'
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

        // CAPTURA OS CAMPOS EXTRAS
        $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
        $datasParcelas = $validated['datas_parcelas'] ?? [];
        $valoresParcelas = $validated['valores_parcelas'] ?? [];
        $anexos = $request->file('anexos') ?? [];
        $descricoesAnexos = $validated['descricoes_anexos'] ?? [];
        $servicoRecorrente = $validated['servico_recorrente'] ?? false;
        $frequencia = $validated['frequencia'] ?? null;
        $quantidadeRepeticoes = $validated['quantidade_repeticoes'] ?? null;
        $dataFinal = $validated['data_final'] ?? null;

        unset(
            $validated['data_primeiro_vencimento'],
            $validated['datas_parcelas'],
            $validated['valores_parcelas'],
            $validated['anexos'],
            $validated['descricoes_anexos'],
            $validated['servico_recorrente'],
            $validated['frequencia'],
            $validated['quantidade_repeticoes'],
            $validated['data_final']
        );

        // Cria o serviço principal
        $servico = Servico::create($validated);

        // Cria parcelas se for parcelado - PASSA OS VALORES PERSONALIZADOS
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            $servico->criarParcelas(
                [1 => $dataPrimeiroVencimento] + $datasParcelas,
                $valoresParcelas
            );
        }

        // Cria serviços recorrentes se necessário
        if ($servicoRecorrente && $frequencia) {
            $totalServicos = $this->criarServicosRecorrentes(
                $servico,
                $frequencia,
                $quantidadeRepeticoes,
                $dataFinal
            );

            $mensagemSucesso = 'Serviço cadastrado com sucesso!';
            if ($totalServicos > 0) {
                $mensagemSucesso .= " + {$totalServicos} serviços recorrentes criados.";
            }
        } else {
            $mensagemSucesso = 'Serviço cadastrado com sucesso!';
        }

        // Upload de anexos
        if (!empty($anexos)) {
            $this->processarAnexos($servico, $anexos, $descricoesAnexos);
        }

        return redirect()->route('servicos.index')->with('success', $mensagemSucesso);
    }

    private function criarServicosRecorrentes(Servico $servicoOriginal, $frequencia, $quantidadeRepeticoes = null, $dataFinal = null)
    {
        $mesesPorFrequencia = [
            'mensal' => 1,
            'bimestral' => 2,
            'trimestral' => 3,
            'semestral' => 6,
            'anual' => 12
        ];

        $meses = $mesesPorFrequencia[$frequencia] ?? 1;

        // Calcula o número total de serviços a criar (incluindo o original)
        $totalServicos = $this->calcularTotalServicos(
            $servicoOriginal->data_servico,
            $quantidadeRepeticoes,
            $dataFinal,
            $meses
        );

        $servicosCriados = 0;

        // Cria os serviços recorrentes (começando do 1, pois o 0 é o serviço original)
        for ($i = 1; $i < $totalServicos; $i++) {
            try {
                // Calcula a data para este serviço recorrente
                $dataServico = (clone $servicoOriginal->data_servico)->addMonths($meses * $i);

                // Se tem data final e a data calculada ultrapassa, para
                if ($dataFinal && $dataServico->gt(new \Carbon\Carbon($dataFinal))) {
                    break;
                }

                // Cria o novo serviço recorrente
                $dadosServico = $servicoOriginal->toArray();

                // Remove campos que não devem ser replicados
                unset($dadosServico['id'], $dadosServico['created_at'], $dadosServico['updated_at']);

                // Atualiza a data do serviço
                $dadosServico['data_servico'] = $dataServico;
                $dadosServico['created_at'] = now();
                $dadosServico['updated_at'] = now();

                // Cria o serviço
                $novoServico = Servico::create($dadosServico);

                // Se for parcelado, cria as parcelas com datas corretas
                if ($novoServico->tipo_pagamento === 'parcelado' && $novoServico->parcelas > 1) {
                    $this->criarParcelasRecorrentes($novoServico, $servicoOriginal, $i, $meses);
                }

                $servicosCriados++;
            } catch (\Exception $e) {
                \Log::error("Erro ao criar serviço recorrente {$i}: " . $e->getMessage());
                continue;
            }
        }

        return $servicosCriados;
    }

    private function calcularTotalServicos($dataInicial, $quantidadeRepeticoes, $dataFinal, $mesesPorRepeticao)
    {
        if ($quantidadeRepeticoes) {
            return $quantidadeRepeticoes + 1;
        }

        if ($dataFinal) {
            $dataInicio = \Carbon\Carbon::parse($dataInicial);
            $dataFim = \Carbon\Carbon::parse($dataFinal);

            $diferencaMeses = $dataInicio->diffInMonths($dataFim);
            $totalServicos = floor($diferencaMeses / $mesesPorRepeticao) + 1;

            return min($totalServicos, 60);
        }

        return 13;
    }

    private function criarParcelasRecorrentes(Servico $servico, Servico $servicoOriginal, $numeroRepeticao, $mesesPorFrequencia)
    {
        // Busca as parcelas do serviço original para replicar a estrutura
        $parcelasOriginais = $servicoOriginal->parcelasServico;

        if ($parcelasOriginais->isEmpty()) {
            return;
        }

        foreach ($parcelasOriginais as $parcelaOriginal) {
            // Calcula a data de vencimento baseada na repetição atual
            $mesesAdicionais = $mesesPorFrequencia * $numeroRepeticao;
            $dataVencimento = (clone $parcelaOriginal->data_vencimento)->addMonths($mesesAdicionais);

            $servico->parcelasServico()->create([
                'numero_parcela' => $parcelaOriginal->numero_parcela,
                'total_parcelas' => $parcelaOriginal->total_parcelas,
                'valor_parcela' => $parcelaOriginal->valor_parcela,
                'data_vencimento' => $dataVencimento,
                'status' => 'pendente',
                'data_pagamento' => null,
            ]);
        }
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
        \Log::info('Dados recebidos:', $request->except(['anexos']));

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
                'anexos.*' => 'file|max:10240',
                'descricoes_anexos' => 'nullable|array',
                'descricoes_anexos.*' => 'nullable|string|max:255'
            ];

            // Validação condicional para campos de parcelamento
            if ($request->tipo_pagamento === 'parcelado') {
                $rules['parcelas'] = 'required|integer|min:2|max:24';
                $rules['data_primeiro_vencimento'] = 'required|date';
                $rules['datas_parcelas'] = 'nullable|array';
                $rules['datas_parcelas.*'] = 'nullable|date';
                $rules['valores_parcelas'] = 'nullable|array';
                $rules['valores_parcelas.*'] = 'nullable|numeric|min:0.01';
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
            $valoresParcelas = $validated['valores_parcelas'] ?? [];
            $anexos = $request->file('anexos') ?? [];
            $descricoesAnexos = $validated['descricoes_anexos'] ?? [];

            unset(
                $validated['data_primeiro_vencimento'],
                $validated['datas_parcelas'],
                $validated['valores_parcelas'],
                $validated['anexos'],
                $validated['descricoes_anexos']
            );

            // Atualiza o serviço
            $servico->update($validated);
            \Log::info('Serviço atualizado com sucesso');

            // Recria as parcelas se necessário - PASSA OS VALORES PERSONALIZADOS
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

                // Remove parcelas existentes e cria novas COM VALORES PERSONALIZADOS
                $servico->parcelasServico()->delete();
                $servico->criarParcelas($datasVencimento, $valoresParcelas);
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
        try {
            \Log::info("=== EXCLUSÃO PERMANENTE DE SERVIÇO INICIADA ===");
            \Log::info("Serviço ID: " . $servico->id);

            // PRIMEIRO: Excluir permanentemente todas as parcelas
            $parcelasCount = $servico->parcelasServico()->count();
            \Log::info("Parcelas a serem excluídas permanentemente: " . $parcelasCount);

            // FORCE DELETE nas parcelas (remove do banco)
            $servico->parcelasServico()->forceDelete();
            \Log::info("Parcelas excluídas permanentemente do banco");

            // DEPOIS: Excluir permanentemente o serviço
            $servico->forceDelete();
            \Log::info("Serviço excluído permanentemente do banco");

            \Log::info("=== EXCLUSÃO PERMANENTE CONCLUÍDA ===");

            return redirect()->route('servicos.index')
                ->with('success', 'Serviço e suas parcelas foram excluídos PERMANENTEMENTE do banco de dados!');
        } catch (\Exception $e) {
            \Log::error("Erro ao excluir permanentemente serviço ID {$servico->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());

            return redirect()->route('servicos.index')
                ->with('error', 'Erro ao excluir serviço: ' . $e->getMessage());
        }
    }

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
            'anexo' => 'required|file|max:10240',
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

    public function forceDestroy($id)
    {
        try {
            $servico = Servico::withTrashed()->findOrFail($id);

            \Log::info("=== EXCLUSÃO PERMANENTE DE SERVIÇO INICIADA ===");
            \Log::info("Serviço ID: " . $servico->id);

            // PRIMEIRO: Excluir permanentemente todas as parcelas
            $parcelasCount = $servico->parcelasServico()->withTrashed()->count();
            \Log::info("Parcelas a serem excluídas permanentemente: " . $parcelasCount);

            // FORCE DELETE nas parcelas
            $servico->parcelasServico()->withTrashed()->forceDelete();
            \Log::info("Parcelas excluídas permanentemente");

            // DEPOIS: Excluir permanentemente o serviço
            $servico->forceDelete();
            \Log::info("Serviço excluído permanentemente");

            \Log::info("=== EXCLUSÃO PERMANENTE CONCLUÍDA ===");

            return redirect()->route('servicos.index')
                ->with('success', 'Serviço e suas parcelas foram excluídos permanentemente!');
        } catch (\Exception $e) {
            \Log::error("Erro ao excluir permanentemente serviço ID {$id}: " . $e->getMessage());

            return redirect()->route('servicos.index')
                ->with('error', 'Erro ao excluir serviço: ' . $e->getMessage());
        }
    }

    public function forceDeleteAll($id)
    {
        try {
            $servico = Servico::withTrashed()->findOrFail($id);

            \Log::info("=== EXCLUSÃO PERMANENTE MANUAL ===");
            \Log::info("Serviço ID: " . $servico->id);

            // Excluir permanentemente as parcelas
            $parcelasCount = $servico->parcelasServico()->withTrashed()->count();
            \Log::info("Parcelas encontradas: " . $parcelasCount);

            $servico->parcelasServico()->withTrashed()->forceDelete();
            \Log::info("Parcelas excluídas permanentemente");

            // Excluir permanentemente o serviço
            $servico->forceDelete();
            \Log::info("Serviço excluído permanentemente");

            return redirect()->route('servicos.index')
                ->with('success', 'Serviço e parcelas removidos permanentemente da database!');
        } catch (\Exception $e) {
            \Log::error("Erro na exclusão permanente: " . $e->getMessage());
            return redirect()->route('servicos.index')
                ->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function lixeira()
    {
        $servicosExcluidos = Servico::onlyTrashed()
            ->with(['cliente', 'parcelasServico' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('deleted_at', 'DESC')
            ->paginate(15);

        return view('servicos.lixeira', compact('servicosExcluidos'));
    }
}
