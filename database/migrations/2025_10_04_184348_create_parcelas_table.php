<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained()->onDelete('cascade');
            $table->integer('numero_parcela');
            $table->integer('total_parcelas');
            $table->decimal('valor_parcela', 10, 2);
            $table->date('data_vencimento');
            $table->enum('status', ['pendente', 'paga', 'atrasada'])->default('pendente');
            $table->date('data_pagamento')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['servico_id', 'numero_parcela']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcelas');
    }
};