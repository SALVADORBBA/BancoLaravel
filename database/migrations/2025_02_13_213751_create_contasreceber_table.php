<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contasreceber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pessoa_id')->nullable()->constrained('pessoa')->nullOnDelete();
            $table->string('nossonumero', 50)->nullable();
            $table->string('seunumero', 255)->nullable();
            $table->integer('parametros_bancos_id')->nullable();
            $table->double('valor', 10, 2)->nullable();
            $table->date('data_vencimento')->nullable();
            $table->integer('status')->default(0);
            $table->text('qrcode')->nullable();
            $table->string('linhadigitavel', 255)->nullable();
            $table->string('codigobarras', 255)->nullable();
            $table->string('etapa_processo_boleto', 50)->default('validacao');
            $table->text('txid')->nullable();
            $table->text('pdfboletobase64')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contasreceber');
    }
};
