/**
 * Parcelas vencidas (com detalhes para cobrança) - CORRIGIDO
 */
private function getParcelasVencidas()
{
    return Parcela::where('status', 'pendente')
        ->where('data_vencimento', '<', now())
        ->whereHas('servico', function($query) {
            $query->whereNull('deleted_at'); // Filtra apenas serviços não excluídos
        })
        ->with(['servico' => function($query) {
            $query->withTrashed(); // Inclui informações mesmo se o serviço foi excluído
        }, 'servico.cliente'])
        ->orderBy('data_vencimento')
        ->take(15)
        ->get()
        ->filter(function($parcela) {
            // Filtra apenas parcelas com serviço e cliente válidos
            return $parcela->servico && $parcela->servico->cliente;
        });
}

/**
 * Parcelas vencidas para relatório - CORRIGIDO
 */
private function getParcelasVencidasRelatorio($dataInicio, $dataFim)
{
    return Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
        ->where('status', 'pendente')
        ->where('data_vencimento', '<', now())
        ->whereHas('servico', function($query) {
            $query->whereNull('deleted_at'); // Filtra serviços não excluídos
        })
        ->with(['servico' => function($query) {
            $query->withTrashed(); // Carrega serviço mesmo se excluído
        }, 'servico.cliente'])
        ->orderBy('data_vencimento')
        ->get()
        ->map(function($parcela) {
            // Verifica se o serviço e cliente existem
            if (!$parcela->servico || !$parcela->servico->cliente) {
                return [
                    'cliente' => 'Cliente não encontrado',
                    'servico' => $parcela->servico ? $parcela->servico->descricao : 'Serviço não encontrado',
                    'valor' => $parcela->valor_parcela,
                    'vencimento' => $parcela->data_vencimento,
                    'dias_atraso' => now()->diffInDays($parcela->data_vencimento),
                    'erro' => true
                ];
            }

            return [
                'cliente' => $parcela->servico->cliente->nome,
                'servico' => $parcela->servico->descricao,
                'valor' => $parcela->valor_parcela,
                'vencimento' => $parcela->data_vencimento,
                'dias_atraso' => now()->diffInDays($parcela->data_vencimento)
            ];
        })
        ->filter(function($item) {
            // Remove itens com erro ou valores nulos
            return !isset($item['erro']) && $item['cliente'] !== 'Cliente não encontrado';
        });
}

/**
 * Serviços com pagamento pendente ou não pago - CORRIGIDO
 */
private function getServicosPendentes()
{
    return Servico::whereIn('status_pagamento', ['pendente', 'nao_pago'])
        ->whereNull('deleted_at') // Filtra apenas serviços não excluídos
        ->with('cliente')
        ->orderBy('data_servico')
        ->take(10)
        ->get();
}