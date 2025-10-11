<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'celular', // Alterado de 'whatsapp' para 'celular'
        'email',
        'endereco',
        'observacoes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function servicosPagos()
    {
        return $this->servicos()->where('status', 'Pago');
    }

    public function servicosPendentes()
    {
        return $this->servicos()->where('status', '!=', 'Pago');
    }
}