<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'servicos';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'descricao_servico',
        'valor',
        'status',
        'observacoes',
        'data_servico',
        'data_vencimento',
        'data_pagamento',
        'forma_pagamento'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_servico' => 'date',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePagos($query)
    {
        return $query->where('status', 'Pago');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', '!=', 'Pago');
    }

    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        
        return $query->whereYear('data_servico', $ano)
                    ->whereMonth('data_servico', $mes);
    }
}