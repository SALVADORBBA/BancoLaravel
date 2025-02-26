<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiariosTable extends Migration
{
    public function up()
    {
        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('tipo_pessoa');
            $table->string('cpf', 11)->nullable();
            $table->string('cnpj', 14)->nullable();
            $table->string('insc_estadual', 20)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('telefone', 15)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('numero', 20)->nullable();
            $table->text('complemento')->nullable();
            $table->string('bairro', 20)->nullable();
            $table->integer('cmun')->nullable();
            $table->integer('cuf')->nullable();
            $table->timestamps();

            $table->primary('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('beneficiarios');
    }
}
