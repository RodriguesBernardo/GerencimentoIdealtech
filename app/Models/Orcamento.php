<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id', 'cliente_nome_avulso', 'cliente_contato_avulso',
        'data_emissao', 'data_validade', 'status',
        'subtotal', 'desconto', 'frete_acrescimos', 'valor_total',
        'condicoes_pagamento', 'prazo_entrega', 'observacoes', 'notas_internas', 'mostrar_valores_individuais'
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relacionamento com os Itens
    public function itens()
    {
        return $this->hasMany(OrcamentoItem::class);
    }

    // Helper para pegar o nome do cliente (cadastrado ou avulso)
    public function getNomeClienteAttribute()
    {
        return $this->cliente_id ? $this->cliente->nome : $this->cliente_nome_avulso;
    }
}