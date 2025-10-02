<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Criar usuário admin
        User::create([
            'name' => 'Administrador',
            'password' => Hash::make('senha123'),
            'is_admin' => true,
            'telefone' => '(11) 99999-9999',
            'permissoes' => json_encode(['ver_valores_completos', 'gerenciar_usuarios', 'gerar_relatorios'])
        ]);

        // Criar usuário comum
        User::create([
            'name' => 'Funcionário Comum',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 88888-8888',
            'permissoes' => json_encode(['ver_clientes', 'cadastrar_clientes', 'ver_servicos'])
        ]);

        // Criar mais alguns usuários de exemplo
        User::create([
            'name' => 'João Técnico',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 77777-7777',
            'permissoes' => json_encode(['ver_clientes', 'cadastrar_clientes', 'ver_servicos', 'cadastrar_servicos'])
        ]);

        // Usuário sem permissão para ver valores
        User::create([
            'name' => 'Estagiário',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 66666-6666',
            'permissoes' => json_encode(['ver_clientes', 'ver_servicos'])
        ]);
    }
}