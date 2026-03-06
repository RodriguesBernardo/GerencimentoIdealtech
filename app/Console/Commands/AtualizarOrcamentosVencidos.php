<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orcamento;
use Carbon\Carbon;

class AtualizarOrcamentosVencidos extends Command
{
    protected $signature = 'orcamentos:verificar-vencidos';
    protected $description = 'Verifica a data de validade dos orçamentos e altera o status para Vencido automaticamente';

    public function handle()
    {
        $hoje = Carbon::today();

        // Faz o Update direto no banco de dados
        $quantidadeAfetada = Orcamento::whereNotNull('data_validade')
            ->whereNotIn('status', ['Aprovado', 'Rejeitado', 'Vencido'])
            ->whereDate('data_validade', '<', $hoje)
            ->update([
                'status' => 'Vencido'
            ]);

        $this->info("Sucesso! {$quantidadeAfetada} orçamento(s) alterado(s) para 'Vencido'.");
    }
}