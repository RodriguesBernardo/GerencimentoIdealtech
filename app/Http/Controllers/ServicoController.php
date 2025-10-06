<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $servicos = Servico::with(['cliente', 'parcelasServico']) // Mude para parcelasServico
                ->latest('data_servico')
                ->paginate(15);
    

        $totalPago = Servico::where('status_pagamento', 'pago')->sum('valor');
        $totalPendente = Servico::where('status_pagamento', 'pendente')->sum('valor');
        $totalNaoPago = Servico::where('status_pagamento', 'nao_pago')->sum('valor');

        return view('servicos.index', compact('servicos', 'totalPago', 'totalPendente', 'totalNaoPago'));
    }


    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $cliente_id = $request->get('cliente_id');

        return view('servicos.create', compact('clientes', 'cliente_id'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'data_servico' => 'required|date',
            'status_pagamento' => 'required|in:pago,nao_pago,pendente',
            'observacao_pagamento' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'tipo_pagamento' => 'required|in:avista,parcelado',
            'parcelas' => 'required|integer|min:1',
            'data_primeiro_vencimento' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'pago_at' => 'nullable|date'
        ]);

        // Converte valor vazio para null
        if (empty($validated['valor'])) {
            $validated['valor'] = null;
        }

        // Se for à vista, parcelas = 1
        if ($validated['tipo_pagamento'] === 'avista') {
            $validated['parcelas'] = 1;
        }

        // Se o status for "pago" e não foi informada data, usa a data atual
        if ($validated['status_pagamento'] === 'pago' && empty($validated['pago_at'])) {
            $validated['pago_at'] = now();
        }

        // Se o status não for "pago", limpa a data de pagamento
        if ($validated['status_pagamento'] !== 'pago') {
            $validated['pago_at'] = null;
        }

        // Remove campo que não existe na tabela
        $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
        unset($validated['data_primeiro_vencimento']);

        $servico = Servico::create($validated);

        // Cria as parcelas se for parcelado
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            $servico->criarParcelas($dataPrimeiroVencimento);
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
        $clientes = Cliente::orderBy('nome')->get();
        // Carrega as parcelas do serviço
        $servico->load('parcelasServico'); // Mude para parcelasServico

        return view('servicos.edit', compact('servico', 'clientes'));
    }

    public function update(Request $request, Servico $servico)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'data_servico' => 'required|date',
            'status_pagamento' => 'required|in:pago,nao_pago,pendente',
            'observacao_pagamento' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'tipo_pagamento' => 'required|in:avista,parcelado',
            'parcelas' => 'required|integer|min:1',
            'data_primeiro_vencimento' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'pago_at' => 'nullable|date'
        ]);

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

        // Remove campo que não existe na tabela
        $dataPrimeiroVencimento = $validated['data_primeiro_vencimento'] ?? null;
        unset($validated['data_primeiro_vencimento']);

        $servico->update($validated);

        // Recria as parcelas se necessário
        if ($servico->tipo_pagamento === 'parcelado' && $servico->parcelas > 1) {
            $servico->criarParcelas($dataPrimeiroVencimento);
        } else {
            // Se não é parcelado, remove todas as parcelas
            $servico->parcelasServico()->delete(); // Mude para parcelasServico
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
}
