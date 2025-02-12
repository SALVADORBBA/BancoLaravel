<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parametros_bancos', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('url1');
            $table->string('certificado');
            $table->string('senha');
            $table->string('client_id');
            $table->string('client_secret');
            $table->integer('expires_in')->default(300);
            $table->text('token')->nullable();
            $table->timestamp('data_token')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_bancos');
    }
};
