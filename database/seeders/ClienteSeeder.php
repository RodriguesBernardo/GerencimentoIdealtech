<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        $clientes = [
            [
                'nome' => 'João Silva',
                'cpf_cnpj' => '123.456.789-00',
                'whatsapp' => '(11) 99999-9999',
                'endereco' => 'Rua das Flores, 123 - Centro, São Paulo/SP',
                'observacoes' => 'Cliente preferencial, sempre paga em dia'
            ],
            [
                'nome' => 'Empresa XYZ Ltda',
                'cpf_cnpj' => '12.345.678/0001-99',
                'whatsapp' => '(11) 88888-8888',
                'endereco' => 'Av. Paulista, 1000 - Bela Vista, São Paulo/SP',
                'observacoes' => 'Empresa corporativa, pedir nota fiscal'
            ],
            [
                'nome' => 'Maria Santos',
                'cpf_cnpj' => '987.654.321-00',
                'whatsapp' => '(11) 77777-7777',
                'endereco' => 'Rua Augusta, 500 - Consolação, São Paulo/SP',
                'observacoes' => 'Prefere contato por WhatsApp'
            ],
            [
                'nome' => 'Tech Solutions ME',
                'cpf_cnpj' => '23.456.789/0001-01',
                'whatsapp' => '(11) 66666-6666',
                'endereco' => 'Rua Liberdade, 200 - Liberdade, São Paulo/SP',
                'observacoes' => 'Solicitar orçamento antes do serviço'
            ],
            [
                'nome' => 'Carlos Oliveira',
                'cpf_cnpj' => '456.789.123-00',
                'whatsapp' => '(11) 55555-5555',
                'endereco' => 'Alameda Santos, 800 - Jardins, São Paulo/SP',
                'observacoes' => 'Cliente novo, explicar formas de pagamento'
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}