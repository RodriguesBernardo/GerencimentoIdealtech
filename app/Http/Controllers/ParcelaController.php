<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParcelaController extends Controller
{
    public function marcarPaga(Parcela $parcela)
    {
        try {
            DB::transaction(function () use ($parcela) {
                $parcela->update([
                    'status' => 'paga',
                    'data_pagamento' => now()
                ]);
                $parcela->servico->verificarEAtualizarStatusServico();
            });

            return back()->with('success', 'Parcela marcada como paga!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao marcar parcela como paga: ' . $e->getMessage());
        }
    }

    public function marcarPendente(Parcela $parcela)
    {
        try {
            DB::transaction(function () use ($parcela) {
                $parcela->update([
                    'status' => 'pendente',
                    'data_pagamento' => null
                ]);
                $parcela->servico->verificarEAtualizarStatusServico();
            });

            return back()->with('success', 'Parcela marcada como pendente!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao marcar parcela como pendente: ' . $e->getMessage());
        }
    }


    public function atualizarStatus(Request $request, Parcela $parcela)
    {
        $request->validate([
            'status' => 'required|in:paga,pendente,nao_paga',
            'data_pagamento' => 'nullable|date'
        ]);

        try {
            DB::transaction(function () use ($parcela, $request) {
                $parcela->update([
                    'status' => $request->status,
                    'data_pagamento' => $request->status === 'paga' ? ($request->data_pagamento ?? now()) : null
                ]);

                $parcela->servico->verificarEAtualizarStatusServico();
            });

            return back()->with('success', 'Status da parcela atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar status da parcela: ' . $e->getMessage());
        }
    }

    public function destroy(Parcela $parcela)
    {
        $parcela->delete();
        
        return back()->with('success', 'Parcela excluída com sucesso!');
    }

    
    public function comprovante(Parcela $parcela)
    {
        $servico = $parcela->servico;
        $cliente = $servico->cliente;
        return view('servicos.comprovante', compact('parcela', 'servico', 'cliente'));
    }

}