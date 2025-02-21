<?php

use App\Http\Controllers\Itau\CreateBoleto;
use App\Http\Controllers\Itau\CreateToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Itau\TokenItauController;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Http\Controllers\Itau\PrintBoletoNormal;
use App\Http\Controllers\Santander\WorkspaceBusca;
use App\Http\Controllers\Santander\WorkspaceSantanderController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Rotas da API
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar as rotas da API para sua aplicação.
| As rotas estão organizadas por banco (Itau, Santander, etc.).
| As rotas são carregadas pelo RouteServiceProvider dentro de um grupo 
| que é atribuído ao middleware "api". Aproveite para construir sua API!
|
*/

// Rotas para o Banco Itaú
Route::prefix('itau')->group(function () {
    Route::post('/GetToken', [CreateToken::class, 'create']); // Obter token para o Itaú
    Route::post('/boleto/create', [CreateBoleto::class, 'create']); // Criar boleto para o Itaú
    Route::post('/controle-meu-numero', [ControleMeuNumeroController::class, 'create']); // Controle Meu Número (Itaú)
    Route::post('/Print', [PrintBoletoNormal::class, 'create']); // Imprimir boleto para o Itaú
});

// Rota para o Webhook
Route::match(['get', 'post', 'options'], '/webhook', [WebhookController::class, 'capture']); // Webhook para notificações

// Rotas para o Banco Santander
Route::prefix('santander')->group(function () {
    Route::post('/Workspace', [WorkspaceSantanderController::class, 'create']); // Criar workspace para o Santander
    Route::post('/BuscarAll', [WorkspaceBusca::class, 'index']); // Buscar todos os registros do Santander
    Route::post('/BuscarOne', [WorkspaceBusca::class, 'search']); // Buscar um registro específico do Santander
    Route::post('/Delete', [WorkspaceBusca::class, 'destroy']); // Deletar registro do Santander
});
