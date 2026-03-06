<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();            
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('cliente_nome_avulso')->nullable(); 
            $table->string('cliente_contato_avulso')->nullable(); 
            // Datas e Status
            $table->date('data_emissao')->useCurrent();
            $table->date('data_validade')->nullable();
            $table->enum('status', ['Rascunho', 'Enviado', 'Aprovado', 'Rejeitado', 'Vencido'])->default('Rascunho');
            // Valores Financeiros
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('frete_acrescimos', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            // Informações Adicionais
            $table->text('condicoes_pagamento')->nullable(); 
            $table->string('prazo_entrega')->nullable(); 
            $table->text('observacoes')->nullable();
            $table->text('notas_internas')->nullable(); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};