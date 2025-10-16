<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AnexoServico extends Model
{
    use HasFactory;

    protected $table = 'anexos_servicos';

    protected $fillable = [
        'servico_id',
        'nome_arquivo',
        'caminho_arquivo',
        'mime_type',
        'tamanho',
        'descricao'
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    // Verifica se o arquivo é uma imagem
    public function isImage()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    // Formata o tamanho do arquivo
    public function getTamanhoFormatadoAttribute()
    {
        $bytes = $this->tamanho;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}