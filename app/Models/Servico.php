<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'nome',
        'descricao',
        'data_servico',
        'status_pagamento',
        'observacao_pagamento',
        'valor',
        'observacoes'
    ];

    protected $casts = [
        'data_servico' => 'date',
        'valor' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}