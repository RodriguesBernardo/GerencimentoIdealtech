<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // created, updated, deleted, restored
            $table->string('model_type'); // Classe do modelo
            $table->unsignedBigInteger('model_id')->nullable(); // ID do registro
            $table->json('old_data')->nullable(); // Dados antes da alteração
            $table->json('new_data')->nullable(); // Dados após alteração
            $table->text('description')->nullable(); // Descrição da ação
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id'); // Usuário que executou a ação
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('created_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};