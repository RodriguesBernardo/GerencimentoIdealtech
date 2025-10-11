<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atendimento extends Model
{
    use HasFactory;

    protected $table = 'atendimentos';

    protected $fillable = [
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'cor',
        'cliente_id',
        'user_id',
        'observacoes',
        'local',
        'tipo'
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    /**
     * Relacionamento com o cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com o usuário (funcionário responsável)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopos para filtrar por status
     */
    public function scopeAgendado($query)
    {
        return $query->where('status', 'agendado');
    }

    public function scopeConfirmado($query)
    {
        return $query->where('status', 'confirmado');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluido($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeCancelado($query)
    {
        return $query->where('status', 'cancelado');
    }

    /**
     * Acessores para status
     */
    public function getStatusFormatadoAttribute(): string
    {
        return match($this->status) {
            'agendado' => 'Agendado',
            'confirmado' => 'Confirmado',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            default => $this->status
        };
    }

    public function getTipoFormatadoAttribute(): string
    {
        return match($this->tipo) {
            'presencial' => 'Presencial',
            'online' => 'Online',
            'telefone' => 'Telefone',
            default => $this->tipo
        };
    }
}