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
            'email' => 'admin@idealtech.com',
            'password' => Hash::make('senha123'),
            'is_admin' => true,
            'telefone' => '(11) 99999-9999',
            'permissoes' => json_encode(['ver_valores_completos', 'gerenciar_usuarios', 'gerar_relatorios'])
        ]);

        User::create([
            'name' => 'Desenvolvimento',
            'email' => 'dev@dev',
            'password' => Hash::make('01032004'),
            'is_admin' => true,
            'telefone' => '(54) 99194-5373',
            'permissoes' => json_encode(['ver_valores_completos', 'gerenciar_usuarios', 'gerar_relatorios'])
        ]);



        // Criar usuário comum
        User::create([
            'name' => 'Funcionário Comum',
            'email' => 'funcionario@idealtech.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 88888-8888',
            'permissoes' => json_encode(['ver_clientes', 'cadastrar_clientes', 'ver_servicos'])
        ]);

        // Criar mais alguns usuários de exemplo
        User::create([
            'name' => 'João Técnico',
            'email' => 'joao@idealtech.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 77777-7777',
            'permissoes' => json_encode(['ver_clientes', 'cadastrar_clientes', 'ver_servicos', 'cadastrar_servicos'])
        ]);

        // Usuário sem permissão para ver valores
        User::create([
            'name' => 'Estagiário',
            'email' => 'estagiario@idealtech.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false,
            'telefone' => '(11) 66666-6666',
            'permissoes' => json_encode(['ver_clientes', 'ver_servicos'])
        ]);
    }
}