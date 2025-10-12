<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function temPermissao($permissao)
    {
        if ($this->is_admin) {
            return true;
        }

        return in_array($permissao, $this->permissoes ?? []);
    }


}