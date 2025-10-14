<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'old_data',
        'new_data',
        'description',
        'ip_address',
        'user_agent',
        'user_id'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopos para filtrar por ação
     */
    public function scopeCreated($query)
    {
        return $query->where('action', 'created');
    }

    public function scopeUpdated($query)
    {
        return $query->where('action', 'updated');
    }

    public function scopeDeleted($query)
    {
        return $query->where('action', 'deleted');
    }

    public function scopeRestored($query)
    {
        return $query->where('action', 'restored');
    }

    /**
     * Escopo para filtrar por modelo
     */
    public function scopeForModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Escopo para filtrar por período
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Acessor para nome do modelo formatado
     */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }

    /**
     * Acessor para ação formatada
     */
    public function getActionFormattedAttribute(): string
    {
        return match($this->action) {
            'created' => 'Criado',
            'updated' => 'Atualizado',
            'deleted' => 'Excluído',
            'restored' => 'Restaurado',
            default => $this->action
        };
    }

    /**
     * Acessor para descrição resumida
     */
    public function getShortDescriptionAttribute(): string
    {
        return $this->description ? substr($this->description, 0, 100) . (strlen($this->description) > 100 ? '...' : '') : '';
    }
}