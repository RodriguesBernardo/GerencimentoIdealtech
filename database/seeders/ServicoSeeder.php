<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servico;
use Carbon\Carbon;

class ServicoSeeder extends Seeder
{
    public function run()
    {
        $servicos = [
            [
                'cliente_id' => 1,
                'user_id' => 1,
                'descricao_servico' => 'Formatação e instalação do Windows 11 + Pacote Office',
                'valor' => 150.00,
                'status' => 'Pago',
                'observacoes' => 'Cliente solicitou backup dos dados antes da formatação',
                'data_servico' => Carbon::now()->subDays(5),
                'data_pagamento' => Carbon::now()->subDays(3),
                'forma_pagamento' => 'PIX'
            ],
            [
                'cliente_id' => 2,
                'user_id' => 1,
                'descricao_servico' => 'Manutenção preventiva em rede corporativa - 10 computadores',
                'valor' => 850.00,
                'status' => 'Boleto 30 dias',
                'observacoes' => 'Emitir nota fiscal para CNPJ',
                'data_servico' => Carbon::now()->subDays(2),
                'data_vencimento' => Carbon::now()->addDays(28),
                'forma_pagamento' => 'Boleto'
            ],
            [
                'cliente_id' => 3,
                'user_id' => 2,
                'descricao_servico' => 'Remoção de vírus e otimização do sistema',
                'valor' => 120.00,
                'status' => 'Pago',
                'observacoes' => 'Computador muito lento, realizar limpeza completa',
                'data_servico' => Carbon::now()->subDays(1),
                'data_pagamento' => Carbon::now()->subDays(1),
                'forma_pagamento' => 'Cartão de Crédito'
            ],
            [
                'cliente_id' => 4,
                'user_id' => 3,
                'descricao_servico' => 'Instalação de servidor local e configuração de backup',
                'valor' => 1200.00,
                'status' => 'Pendente',
                'observacoes' => 'Aguardando aprovação do orçamento',
                'data_servico' => Carbon::now()->subDays(3),
                'data_vencimento' => Carbon::now()->addDays(15),
                'forma_pagamento' => 'Transferência'
            ],
            [
                'cliente_id' => 5,
                'user_id' => 2,
                'descricao_servico' => 'Troca de HD por SSD e instalação do sistema',
                'valor' => 300.00,
                'status' => 'Pago',
                'observacoes' => 'Cliente comprou o SSD separadamente',
                'data_servico' => Carbon::now()->subDays(7),
                'data_pagamento' => Carbon::now()->subDays(5),
                'forma_pagamento' => 'Dinheiro'
            ],
            [
                'cliente_id' => 1,
                'user_id' => 3,
                'descricao_servico' => 'Configuração de roteador e rede Wi-Fi',
                'valor' => 80.00,
                'status' => 'Pago',
                'observacoes' => 'Problema de sinal resolvido com sucesso',
                'data_servico' => Carbon::now()->subDays(10),
                'data_pagamento' => Carbon::now()->subDays(8),
                'forma_pagamento' => 'PIX'
            ],
            [
                'cliente_id' => 2,
                'user_id' => 1,
                'descricao_servico' => 'Consultoria em segurança da informação',
                'valor' => 500.00,
                'status' => 'Atrasado',
                'observacoes' => 'Enviar lembrança de pagamento',
                'data_servico' => Carbon::now()->subDays(40),
                'data_vencimento' => Carbon::now()->subDays(10),
                'forma_pagamento' => 'Boleto'
            ]
        ];

        foreach ($servicos as $servico) {
            Servico::create($servico);
        }
    }
}