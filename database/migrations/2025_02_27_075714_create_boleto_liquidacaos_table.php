<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('boleto_liquidacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boletos_movimentacao_id');
            $table->string('data_liquidacao', 250);
            $table->double('valor', 10, 2);
            $table->double('multa', 10, 2)->nullable();
            $table->double('abatimento', 10, 2)->nullable();
            $table->double('juros', 10, 2)->nullable();
            $table->double('desconto', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('boletos_movimentacao_id')->references('id')->on('boletos_movimentacao')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('boleto_liquidacao');
    }
};
