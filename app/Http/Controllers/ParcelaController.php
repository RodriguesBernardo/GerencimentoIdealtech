<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function marcarPaga(Parcela $parcela)
    {
        $parcela->marcarComoPaga();
        
        return back()->with('success', 'Parcela marcada como paga!');
    }

    public function marcarPendente(Parcela $parcela)
    {
        $parcela->update([
            'status' => 'pendente',
            'data_pagamento' => null,
        ]);
        
        return back()->with('success', 'Parcela marcada como pendente!');
    }

    public function atualizarStatus(Request $request, Parcela $parcela)
    {
        $request->validate([
            'status' => 'required|in:paga,pendente,atrasada',
            'data_pagamento' => 'nullable|date',
            'observacao' => 'nullable|string'
        ]);

        $data = [
            'status' => $request->status,
            'observacao' => $request->observacao
        ];

        // Se está marcando como paga e não tem data, usa a data atual
        if ($request->status === 'paga' && empty($request->data_pagamento)) {
            $data['data_pagamento'] = now();
        } elseif ($request->status === 'paga' && $request->data_pagamento) {
            $data['data_pagamento'] = $request->data_pagamento;
        } else {
            $data['data_pagamento'] = null;
        }

        $parcela->update($data);

        return back()->with('success', 'Parcela atualizada com sucesso!');
    }

    public function destroy(Parcela $parcela)
    {
        $parcela->delete();
        
        return back()->with('success', 'Parcela excluída com sucesso!');
    }
}