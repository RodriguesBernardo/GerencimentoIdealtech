<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrcamentoController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Orcamento::with('cliente');

        // Filtro de Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('cliente_nome_avulso', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($qCliente) use ($search) {
                      $qCliente->where('nome', 'like', "%{$search}%")
                               ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro de Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Totalizadores
        $totalizador = [
            'quantidade' => $query->count(),
            'valor_geral' => $query->sum('valor_total'),
            'valor_aprovados' => (clone $query)->where('status', 'Aprovado')->sum('valor_total'),
        ];

        $orcamentos = $query->latest()->paginate(15)->withQueryString();

        return view('orcamentos.index', compact('orcamentos', 'totalizador'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('orcamentos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'itens' => 'required|array|min:1',
            'itens.*.descricao' => 'required|string',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except('itens');
            
            // Captura o valor do checkbox corretamente
            $data['mostrar_valores_individuais'] = $request->has('mostrar_valores_individuais');
            
            // Lógica do Cliente Híbrido
            if ($request->filled('cliente_id')) {
                $data['cliente_nome_avulso'] = null;
                $data['cliente_contato_avulso'] = null;
            }

            $orcamento = Orcamento::create($data);
            $subtotal = 0;

            foreach ($request->itens as $item) {
                $valor_total_item = $item['quantidade'] * $item['valor_unitario'];
                $subtotal += $valor_total_item;

                $orcamento->itens()->create([
                    'descricao' => $item['descricao'],
                    'detalhes' => $item['detalhes'] ?? null,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total' => $valor_total_item,
                ]);
            }

            // Atualiza os totais
            $desconto = $request->desconto ?? 0;
            $acrescimos = $request->frete_acrescimos ?? 0;
            
            $orcamento->update([
                'subtotal' => $subtotal,
                'valor_total' => ($subtotal - $desconto) + $acrescimos
            ]);

            DB::commit();

            return redirect()->route('orcamentos.index')->with('success', 'Orçamento criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar orçamento: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'itens']);
        return view('orcamentos.show', compact('orcamento'));
    }

    public function edit(Orcamento $orcamento)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $orcamento->load('itens'); 
        
        return view('orcamentos.edit', compact('orcamento', 'clientes'));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        $request->validate([
            'itens' => 'required|array|min:1',
            'itens.*.descricao' => 'required|string',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except('itens');
            
            // Captura o valor do checkbox corretamente na edição
            $data['mostrar_valores_individuais'] = $request->has('mostrar_valores_individuais');
            
            if ($request->filled('cliente_id')) {
                $data['cliente_nome_avulso'] = null;
                $data['cliente_contato_avulso'] = null;
            }

            // Atualiza os dados principais do orçamento
            $orcamento->update($data);            
            
            // Apaga itens antigos e recria os novos
            $orcamento->itens()->delete();
            $subtotal = 0;

            foreach ($request->itens as $item) {
                $valor_total_item = $item['quantidade'] * $item['valor_unitario'];
                $subtotal += $valor_total_item;

                $orcamento->itens()->create([
                    'descricao' => $item['descricao'],
                    'detalhes' => $item['detalhes'] ?? null,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total' => $valor_total_item,
                ]);
            }

            $desconto = $request->desconto ?? 0;
            $acrescimos = $request->frete_acrescimos ?? 0;
            
            $orcamento->update([
                'subtotal' => $subtotal,
                'valor_total' => ($subtotal - $desconto) + $acrescimos
            ]);

            DB::commit();

            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('success', 'Orçamento salvo com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar orçamento: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Orcamento $orcamento)
    {
        $orcamento->delete();
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento excluído com sucesso!');
    }

    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load('itens', 'cliente');
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));
        return $pdf->stream('orcamento-' . str_pad($orcamento->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function aprovar(Orcamento $orcamento)
    {
        $orcamento->update(['status' => 'Aprovado']);
        return back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function cancelar(Orcamento $orcamento)
    {
        $orcamento->update(['status' => 'Rejeitado']);
        return back()->with('success', 'Orçamento cancelado/rejeitado.');
    }
}