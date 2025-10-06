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
        'parcelas',
        'tipo_pagamento',
        'observacoes',
        'pago_at'
    ];

    protected $casts = [
        'data_servico' => 'date',
        'valor' => 'decimal:2',
        'pago_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relacionamento com parcelas
    public function parcelasServico()
    {
        return $this->hasMany(Parcela::class);
    }

    // Accessors CORRIGIDOS - usando parcelasServico
    public function getTotalParcelasAttribute()
    {
        return $this->parcelasServico()->count();
    }

    public function getParcelasPagasAttribute()
    {
        return $this->parcelasServico->where('status', 'paga')->count();
    }

    // Calcular valor da parcela
    public function getValorParcelaAttribute()
    {
        if ($this->parcelas > 0) {
            return $this->valor / $this->parcelas;
        }
        return $this->valor;
    }

    // Verificar se todas as parcelas estão pagas
    public function getTodasParcelasPagasAttribute()
    {
        return $this->parcelasServico()->where('status', '!=', 'paga')->count() === 0;
    }

    // Total pago
    public function getTotalPagoAttribute()
    {
        return $this->parcelasServico()->where('status', 'paga')->sum('valor_parcela');
    }

    // Total pendente
    public function getTotalPendenteAttribute()
    {
        return $this->parcelasServico()->where('status', '!=', 'paga')->sum('valor_parcela');
    }

    public function marcarComoPago($dataPagamento = null)
    {
        $this->update([
            'status_pagamento' => 'pago',
            'pago_at' => $dataPagamento ?? now(),
        ]);
    }

    // Criar parcelas
    public function criarParcelas($dataPrimeiroVencimento = null)
    {
        if ($this->tipo_pagamento !== 'parcelado' || $this->parcelas <= 1) {
            return;
        }

        // Deleta parcelas existentes
        $this->parcelasServico()->delete();

        $valorParcela = $this->valor / $this->parcelas;
        $dataVencimento = $dataPrimeiroVencimento ? \Carbon\Carbon::parse($dataPrimeiroVencimento) : now();

        for ($i = 1; $i <= $this->parcelas; $i++) {
            $this->parcelasServico()->create([
                'numero_parcela' => $i,
                'total_parcelas' => $this->parcelas,
                'valor_parcela' => $valorParcela,
                'data_vencimento' => $dataVencimento->copy()->addMonths($i - 1),
                'status' => 'pendente',
            ]);
        }
    }

    // Método para marcar como não pago
    public function marcarComoNaoPago()
    {
        $this->update([
            'status_pagamento' => 'nao_pago',
            'pago_at' => null,
        ]);
    }

    // Método para marcar como pendente
    public function marcarComoPendente()
    {
        $this->update([
            'status_pagamento' => 'pendente',
            'pago_at' => null,
        ]);
    }

    // Scope para serviços pagos
    public function scopePagos($query)
    {
        return $query->where('status_pagamento', 'pago');
    }

    // Scope para serviços pendentes
    public function scopePendentes($query)
    {
        return $query->where('status_pagamento', 'pendente');
    }

    // Scope para serviços não pagos
    public function scopeNaoPagos($query)
    {
        return $query->where('status_pagamento', 'nao_pago');
    }

    public function estaAtrasada()
    {
        return $this->data_vencimento < now() && $this->status === 'pendente';
    }
}
