<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            
            // Produto de digitação livre
            $table->string('descricao');
            $table->text('detalhes')->nullable();
            
            // Valores
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('valor_unitario', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_itens');
    }
};