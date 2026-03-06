<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoItem extends Model
{
    protected $table = 'orcamento_itens';

    protected $fillable = [
        'orcamento_id', 'descricao', 'detalhes', 
        'quantidade', 'valor_unitario', 'valor_total'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}