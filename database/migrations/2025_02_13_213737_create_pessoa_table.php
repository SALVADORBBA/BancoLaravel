<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pessoa', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 20)->unique();
            $table->string('insc_estadual', 20)->nullable();
            $table->string('nome', 500);
            $table->string('nome_fantasia', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->integer('pessoa_atacado_id')->default(0);
            $table->integer('forca_venda_id')->default(1);
            $table->string('email_fx', 200)->nullable();
            $table->string('fone_fx', 20)->nullable();
            $table->string('cep', 20)->nullable();
            $table->string('rua', 200)->nullable();
            $table->string('bairro', 50)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 50)->nullable();
 
            $table->string('ibge', 20)->nullable();
            $table->string('cuf', 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoa');
    }
};
