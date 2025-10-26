<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Loggable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'permissoes',
        'telefone'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissoes' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Relacionamentos
     */
    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }

    /**
     * Métodos de Permissão
     */

    /**
     * Verifica se o usuário tem uma permissão específica
     */
    public function temPermissao($permissao)
    {
        if ($this->is_admin) {
            return true;
        }

        return in_array($permissao, $this->permissoes ?? []);
    }

    /**
     * Verifica se o usuário tem qualquer uma das permissões fornecidas
     */
    public function temQualquerPermissao(array $permissoes)
    {
        if ($this->is_admin) {
            return true;
        }

        return !empty(array_intersect($permissoes, $this->permissoes ?? []));
    }

    /**
     * Verifica se o usuário tem todas as permissões fornecidas
     */
    public function temTodasPermissoes(array $permissoes)
    {
        if ($this->is_admin) {
            return true;
        }

        return empty(array_diff($permissoes, $this->permissoes ?? []));
    }

    /**
     * Adiciona uma permissão ao usuário
     */
    public function adicionarPermissao($permissao)
    {
        $permissoes = $this->permissoes ?? [];
        
        if (!in_array($permissao, $permissoes)) {
            $permissoes[] = $permissao;
            $this->permissoes = $permissoes;
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove uma permissão do usuário
     */
    public function removerPermissao($permissao)
    {
        $permissoes = $this->permissoes ?? [];
        
        if (($key = array_search($permissao, $permissoes)) !== false) {
            unset($permissoes[$key]);
            $this->permissoes = array_values($permissoes); // Reindexa o array
            return $this->save();
        }
        
        return true;
    }

    /**
     * Limpa todas as permissões do usuário
     */
    public function limparPermissoes()
    {
        $this->permissoes = [];
        return $this->save();
    }

    /**
     * Retorna a lista de permissões disponíveis no sistema
     */
    public static function permissoesDisponiveis()
    {
        return [
            // Calendário e Atendimentos
            'calendar.view' => 'Visualizar Calendário',
            'calendar.events.create' => 'Criar Atendimentos',
            'calendar.events.edit' => 'Editar Atendimentos',
            'calendar.events.delete' => 'Excluir Atendimentos',
            'calendar.events.view_all' => 'Visualizar Todos os Atendimentos',
            
            // Usuários
            'users.view' => 'Visualizar Usuários',
            'users.create' => 'Criar Usuários',
            'users.edit' => 'Editar Usuários',
            'users.delete' => 'Excluir Usuários',
            
            // Clientes
            'clients.view' => 'Visualizar Clientes',
            'clients.create' => 'Criar Clientes',
            'clients.edit' => 'Editar Clientes',
            'clients.delete' => 'Excluir Clientes',
            
            // Serviços
            'services.view' => 'Visualizar Serviços',
            'services.create' => 'Criar Serviços',
            'services.edit' => 'Editar Serviços',
            'services.delete' => 'Excluir Serviços',
            
            // Relatórios
            'reports.view' => 'Visualizar Relatórios',
            'reports.export' => 'Exportar Relatórios',
            
            // Configurações
            'settings.manage' => 'Gerenciar Configurações',
        ];
    }

    /**
     * Métodos de Verificação Específicos para Calendário
     */

    /**
     * Verifica se o usuário pode visualizar o calendário
     */
    public function podeVisualizarCalendario()
    {
        return $this->is_admin || $this->temPermissao('calendar.view');
    }

    /**
     * Verifica se o usuário pode criar atendimentos
     */
    public function podeCriarAtendimentos()
    {
        return $this->is_admin || $this->temPermissao('calendar.events.create');
    }

    /**
     * Verifica se o usuário pode editar atendimentos
     */
    public function podeEditarAtendimentos()
    {
        return $this->is_admin || $this->temPermissao('calendar.events.edit');
    }

    /**
     * Verifica se o usuário pode excluir atendimentos
     */
    public function podeExcluirAtendimentos()
    {
        return $this->is_admin || $this->temPermissao('calendar.events.delete');
    }

    /**
     * Verifica se o usuário pode visualizar todos os atendimentos
     */
    public function podeVerTodosAtendimentos()
    {
        return $this->is_admin || $this->temPermissao('calendar.events.view_all');
    }

    /**
     * Verifica se o usuário pode editar um atendimento específico
     */
    public function podeEditarAtendimento($atendimento)
    {
        if ($this->is_admin || $this->temPermissao('calendar.events.edit')) {
            return true;
        }

        // Se não tem permissão geral, só pode editar seus próprios atendimentos
        return $atendimento->user_id === $this->id;
    }

    /**
     * Verifica se o usuário pode excluir um atendimento específico
     */
    public function podeExcluirAtendimento($atendimento)
    {
        if ($this->is_admin || $this->temPermissao('calendar.events.delete')) {
            return true;
        }

        // Se não tem permissão geral, só pode excluir seus próprios atendimentos
        return $atendimento->user_id === $this->id;
    }

    /**
     * Métodos para Usuários
     */

    public function podeVisualizarUsuarios()
    {
        return $this->is_admin || $this->temPermissao('users.view');
    }

    public function podeCriarUsuarios()
    {
        return $this->is_admin || $this->temPermissao('users.create');
    }

    public function podeEditarUsuarios()
    {
        return $this->is_admin || $this->temPermissao('users.edit');
    }

    public function podeExcluirUsuarios()
    {
        return $this->is_admin || $this->temPermissao('users.delete');
    }

    /**
     * Métodos para Clientes
     */

    public function podeVisualizarClientes()
    {
        return $this->is_admin || $this->temPermissao('clients.view');
    }

    public function podeCriarClientes()
    {
        return $this->is_admin || $this->temPermissao('clients.create');
    }

    public function podeEditarClientes()
    {
        return $this->is_admin || $this->temPermissao('clients.edit');
    }

    public function podeExcluirClientes()
    {
        return $this->is_admin || $this->temPermissao('clients.delete');
    }

    /**
     * Escopos de Consulta
     */

    /**
     * Escopo para usuários ativos (não deletados)
     */
    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Escopo para usuários administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Escopo para usuários com uma permissão específica
     */
    public function scopeComPermissao($query, $permissao)
    {
        return $query->where('is_admin', true)
                    ->orWhereJsonContains('permissoes', $permissao);
    }

    /**
     * Acessores
     */

    /**
     * Retorna as permissões formatadas para exibição
     */
    public function getPermissoesFormatadasAttribute()
    {
        $permissoesDisponiveis = self::permissoesDisponiveis();
        $permissoesUsuario = $this->permissoes ?? [];
        
        $formatadas = [];
        foreach ($permissoesUsuario as $permissao) {
            if (isset($permissoesDisponiveis[$permissao])) {
                $formatadas[] = $permissoesDisponiveis[$permissao];
            }
        }
        
        return $formatadas;
    }

    /**
     * Retorna o tipo de usuário formatado
     */
    public function getTipoFormatadoAttribute()
    {
        return $this->is_admin ? 'Administrador' : 'Usuário';
    }

    /**
     * Retorna o status do usuário
     */
    public function getStatusAttribute()
    {
        return $this->deleted_at ? 'Inativo' : 'Ativo';
    }

    /**
     * Mutators
     */

    /**
     * Garante que o email seja sempre em minúsculas
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Garante que as permissões sejam sempre um array válido
     */
    public function setPermissoesAttribute($value)
    {
        $this->attributes['permissoes'] = json_encode($value ?? []);
    }

    /**
     * Outros Métodos Úteis
     */

    /**
     * Verifica se o usuário é o próprio usuário logado
     */
    public function isCurrentUser()
    {
        return $this->id === auth()->id();
    }

    /**
     * Verifica se o usuário pode ser editado/excluído
     */
    public function podeSerModificado()
    {
        // Impede que usuários modifiquem a si mesmos (em alguns casos)
        return !$this->isCurrentUser();
    }

    /**
     * Retorna os dados básicos do usuário para selects
     */
    public function toSelectArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'telefone' => $this->telefone,
        ];
    }
}