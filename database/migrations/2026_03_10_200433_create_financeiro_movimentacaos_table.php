<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financeiro_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('categoria');
            $table->string('status_pagamento')->default('pago'); 
            $table->string('lote_importacao')->nullable(); 
            $table->text('observacoes')->nullable();

            $table->foreignId('user_id')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financeiro_movimentacoes');
    }
};