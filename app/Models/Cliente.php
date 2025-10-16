<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Loggable;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'celular',
        'email',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
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

    /**
     * Acessor para endereço completo
     */
    public function getEnderecoCompletoAttribute()
    {
        $endereco = [];
        
        if ($this->logradouro) {
            $endereco[] = $this->logradouro;
            if ($this->numero) {
                $endereco[] = $this->numero;
            }
        }
        
        if ($this->bairro) {
            $endereco[] = $this->bairro;
        }
        
        if ($this->cidade) {
            $endereco[] = $this->cidade;
        }
        
        if ($this->uf) {
            $endereco[] = $this->uf;
        }
        
        if ($this->cep) {
            $endereco[] = $this->cep;
        }
        
        return implode(', ', $endereco);
    }

    /**
     * Acessor para endereço resumido (sem CEP)
     */
    public function getEnderecoResumidoAttribute()
    {
        $endereco = [];
        
        if ($this->logradouro) {
            $endereco[] = $this->logradouro;
            if ($this->numero) {
                $endereco[] = $this->numero;
            }
        }
        
        if ($this->bairro) {
            $endereco[] = $this->bairro;
        }
        
        if ($this->cidade && $this->uf) {
            $endereco[] = $this->cidade . '/' . $this->uf;
        }
        
        return implode(', ', $endereco);
    }
}