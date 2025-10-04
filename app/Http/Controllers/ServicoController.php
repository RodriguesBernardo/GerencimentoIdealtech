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
        
        $servicos = Servico::with('cliente')
            ->when($search, function($query) use ($search) {
                return $query->where('nome', 'like', '%' . $search . '%')
                           ->orWhere('descricao', 'like', '%' . $search . '%')
                           ->orWhereHas('cliente', function($q) use ($search) {
                               $q->where('nome', 'like', '%' . $search . '%');
                           });
            })
            ->when($status, function($query) use ($status) {
                return $query->where('status_pagamento', $status);
            })
            ->orderBy('data_servico', 'desc')
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
            'observacoes' => 'nullable|string'
        ]);

        // Converte valor vazio para null
        if (empty($validated['valor'])) {
            $validated['valor'] = null;
        }

        Servico::create($validated);

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function show(Servico $servico)
    {
        return view('servicos.show', compact('servico'));
    }

    public function edit(Servico $servico)
    {
        $clientes = Cliente::orderBy('nome')->get();
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
            'observacoes' => 'nullable|string'
        ]);

        // Converte valor vazio para null
        if (empty($validated['valor'])) {
            $validated['valor'] = null;
        }

        $servico->update($validated);

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
            'observacao_pagamento' => 'nullable|string'
        ]);

        $servico->update([
            'status_pagamento' => $request->status_pagamento,
            'observacao_pagamento' => $request->observacao_pagamento
        ]);

        return back()->with('success', 'Status de pagamento atualizado com sucesso!');
    }
}