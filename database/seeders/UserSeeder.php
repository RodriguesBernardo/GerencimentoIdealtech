<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Desenvolvimento',
            'email' => 'dev@dev',
            'password' => Hash::make('01032004'),
            'is_admin' => true,
            'telefone' => '(54) 99194-5373',
            'permissoes' => json_encode([''])
        ]);
    }
}