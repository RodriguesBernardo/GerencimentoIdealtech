<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao');
            $table->date('data_servico');
            $table->enum('status_pagamento', ['pago', 'nao_pago', 'pendente'])->default('pendente');
            $table->decimal('valor', 10, 2)->nullable();
            $table->text('observacao_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servicos');
    }
};