<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->boolean('mostrar_valores_individuais')->default(true)->after('status');
        });
    }
    public function down()
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('mostrar_valores_individuais');
        });
    }
};
