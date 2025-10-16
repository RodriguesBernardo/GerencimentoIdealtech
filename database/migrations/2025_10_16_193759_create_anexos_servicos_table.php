<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anexos_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained()->onDelete('cascade');
            $table->string('nome_arquivo');
            $table->string('caminho_arquivo');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('tamanho')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('anexos_servicos');
    }
};