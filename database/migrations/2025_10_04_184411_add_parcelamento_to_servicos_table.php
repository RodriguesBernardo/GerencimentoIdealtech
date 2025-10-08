<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->integer('parcelas')->default(1)->after('valor');
            $table->enum('tipo_pagamento', ['avista', 'parcelado'])->default('avista')->after('parcelas');
        });
    }

    public function down()
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropColumn(['parcelas', 'tipo_pagamento']);
        });
    }
};