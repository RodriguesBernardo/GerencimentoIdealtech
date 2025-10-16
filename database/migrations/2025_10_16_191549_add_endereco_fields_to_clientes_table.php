<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('celular');
            $table->string('cep')->nullable()->after('email');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero')->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'cep', 
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'uf'
            ]);
        });
    }
};