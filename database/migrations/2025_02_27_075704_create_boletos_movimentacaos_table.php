<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('boletos_movimentacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contasreceber_id');
            $table->double('multa', 10, 2)->nullable();
            $table->double('abatimento', 10, 2)->nullable();
            $table->string('tipojuros', 10)->nullable();
            $table->integer('diasprotesto')->nullable();
            $table->integer('validadeaposvencimento')->nullable();
            $table->integer('diasnegativacao')->nullable();
            $table->string('tipodesconto', 10)->nullable();
            $table->double('descontoantecipacao', 10, 2)->nullable();
            $table->double('juros', 10, 2)->nullable();
            $table->datetime('dadosliquidacao_data')->nullable();
            $table->double('dadosliquidacao_valor', 10, 2)->nullable();
            $table->double('dadosliquidacao_multa', 10, 2)->nullable();
            $table->double('dadosliquidacao_abatimento', 10, 2)->nullable();
            $table->double('dadosliquidacao_juros', 10, 2)->nullable();
            $table->double('dadosliquidacao_desconto', 10, 2)->nullable();
            $table->date('datamovimento')->nullable();
            $table->date('dataprevisaopagamento')->nullable();
            $table->timestamps();

            $table->foreign('contasreceber_id')->references('id')->on('contasrec')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('boletos_movimentacao');
    }
};
