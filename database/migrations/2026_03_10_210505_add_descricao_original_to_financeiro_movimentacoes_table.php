<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('financeiro_movimentacoes', function (Blueprint $table) {
            $table->string('descricao_original')->nullable()->after('descricao');
        });
    }

    public function down()
    {
        Schema::table('financeiro_movimentacoes', function (Blueprint $table) {
            $table->dropColumn('descricao_original');
        });
    }
};
