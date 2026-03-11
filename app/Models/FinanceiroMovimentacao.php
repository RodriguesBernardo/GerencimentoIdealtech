<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceiroMovimentacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'financeiro_movimentacoes';

    protected $fillable = [
        'descricao', 'descricao_original', 'valor', 'data_vencimento', 'data_pagamento', 
        'tipo', 'categoria', 'status_pagamento', 'lote_importacao', 
        'observacoes', 'user_id'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'decimal:2'
    ];

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}