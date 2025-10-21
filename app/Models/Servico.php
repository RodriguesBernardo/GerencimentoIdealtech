<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Loggable;

class Servico extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'cliente_id',
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

    // Relacionamento com anexos
    public function anexos()
    {
        return $this->hasMany(AnexoServico::class);
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

    // Contador de anexos
    public function getTotalAnexosAttribute()
    {
        return $this->anexos()->count();
    }

    public function marcarComoPago($dataPagamento = null)
    {
        $this->update([
            'status_pagamento' => 'pago',
            'pago_at' => $dataPagamento ?? now(),
        ]);
    }

    // Criar parcelas
    public function criarParcelas($datasVencimento, $valoresParcelas = [])
    {
        $valorTotal = $this->valor;
        $numParcelas = $this->parcelas;
        
        // Calcula o valor padrão da parcela
        $valorParcelaPadrao = $valorTotal / $numParcelas;
        
        \Log::info("Criando parcelas para serviço {$this->id}");
        \Log::info("Valor total: {$valorTotal}, Parcelas: {$numParcelas}");
        \Log::info("Valores personalizados recebidos:", $valoresParcelas);
        
        for ($i = 1; $i <= $numParcelas; $i++) {
            // Determina a data de vencimento
            if (isset($datasVencimento[$i]) && !empty($datasVencimento[$i])) {
                $dataVencimento = $datasVencimento[$i];
            } else {
                // Calcula data automaticamente baseada na primeira parcela
                $dataBase = new \DateTime($datasVencimento[1]);
                $dataBase->modify('+' . ($i - 1) . ' months');
                $dataVencimento = $dataBase->format('Y-m-d');
            }
            
            // Determina o valor da parcela (personalizado ou padrão)
            if (isset($valoresParcelas[$i]) && is_numeric($valoresParcelas[$i]) && $valoresParcelas[$i] > 0) {
                $valorParcela = $valoresParcelas[$i];
                \Log::info("Parcela {$i}: usando valor personalizado R$ " . $valorParcela);
            } else {
                $valorParcela = $valorParcelaPadrao;
                \Log::info("Parcela {$i}: usando valor padrão R$ " . $valorParcela);
            }
            
            $this->parcelasServico()->create([
                'numero_parcela' => $i,
                'total_parcelas' => $numParcelas,
                'valor_parcela' => $valorParcela,
                'data_vencimento' => $dataVencimento,
                'status' => 'pendente',
                'data_pagamento' => null,
            ]);
        }
        
        \Log::info("Parcelas criadas com sucesso para serviço {$this->id}");
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