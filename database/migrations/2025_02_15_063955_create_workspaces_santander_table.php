<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('workspaces_santander', function (Blueprint $table) {
            $table->id();
            $table->string('status', 10);
            $table->string('parametros_bancos_id', 110);
            $table->string('type', 10);
            $table->string('description', 255);
            $table->string('covenant_code', 36);
            $table->boolean('bank_slip_billing_webhook_active')->nullable();
            $table->boolean('pix_billing_webhook_active')->nullable();
 
            $table->string('id_remoto', 255)->nullable();
            $table->text('webhookurl')->nullable();
            $table->timestamps();

            // Opcional: Adiciona uma chave estrangeira (descomente se necessário)
        $table->foreign('parametros_bancos_id')->references('id')->on('parametros_bancos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('workspaces_santander');
    }
};
