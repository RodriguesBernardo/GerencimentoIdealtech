<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Loggable;

class Parcela extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'servico_id',
        'numero_parcela',
        'total_parcelas',
        'valor_parcela',
        'data_vencimento',
        'status',
        'data_pagamento',
        'observacao'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor_parcela' => 'decimal:2',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    // Scope para parcelas pendentes
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Scope para parcelas pagas
    public function scopePagas($query)
    {
        return $query->where('status', 'paga');
    }

    // Scope para parcelas atrasadas
    public function scopeAtrasadas($query)
    {
        return $query->where('status', 'atrasada');
    }

    // Método para marcar como paga
    public function marcarComoPaga($dataPagamento = null)
    {
        $this->update([
            'status' => 'paga',
            'data_pagamento' => $dataPagamento ?? now(),
        ]);
    }

    // Método para verificar se está atrasada
    public function estaAtrasada()
    {
        return $this->data_vencimento < now() && $this->status === 'pendente';
    }

    // Atualizar status baseado na data de vencimento
    public function atualizarStatus()
    {
        if ($this->estaAtrasada() && $this->status !== 'paga') {
            $this->update(['status' => 'atrasada']);
        }
    }
}