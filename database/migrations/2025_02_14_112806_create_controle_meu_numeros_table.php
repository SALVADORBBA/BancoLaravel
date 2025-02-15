<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('controle_meu_numeros', function (Blueprint $table) {
            $table->id(); // Equivalente a INT AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('parametros_bancos_id')->nullable();
            $table->string('ultimo_numero', 20)->nullable();
            $table->string('numero_anterior', 20)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->string('status', 20)->default('livre');
            $table->unsignedBigInteger('banco_id')->nullable();

            // Índices e chaves estrangeiras (se necessário)
            // $table->foreign('parametros_bancos_id')->references('id')->on('parametros_bancos');
            // $table->foreign('banco_id')->references('id')->on('bancos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('controle_meu_numeros');
    }
};
