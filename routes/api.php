<?php

use App\Http\Controllers\BancoBrasil\BancoBrasilTokenService;
use App\Http\Controllers\BancoInter\CreateBoletoInter;
use App\Http\Controllers\BancoInter\GetTokenIter;
use App\Http\Controllers\BoletosRest;
use App\Http\Controllers\Bradesco\CreateBoletoBradesco;
use App\Http\Controllers\Bradesco\GetTokenBradesco;
use App\Http\Controllers\Itau\CreateBoleto;
use App\Http\Controllers\Itau\CreateToken;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Itau\TokenItauController;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Http\Controllers\Itau\PrintBoletoNormal;
 
use App\Http\Controllers\Santander\WorkspaceBusca;
use App\Http\Controllers\Santander\CreateBoletoSTD;
use App\Http\Controllers\Santander\SolicitacaoBaixaBoleto;
use App\Http\Controllers\Santander\WorkspaceSantanderController;
use App\Http\Controllers\Sicredi\BaixaBoletoSicredi;
use App\Http\Controllers\Sicredi\BuscaCobraca;
use App\Http\Controllers\Sicredi\ConsultaBaixaBoletoController;
use App\Http\Controllers\Sicredi\CreateBoletoSC;
use App\Http\Controllers\Sicredi\CreateTokensSC;
use App\Http\Controllers\Sicredi\PrintBoleto;
 
use App\Http\Controllers\WebhookController;

use App\Http\Controllers\BancoInterBoletoController;
 
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 
Route::post('/GetToken', [CreateToken::class, 'create']);
Route::post('/itau/create', [CreateBoleto::class, 'create']);


Route::post('/controle-meu-numero', [ControleMeuNumeroController::class, 'create']);
Route::post('/Print', [PrintBoletoNormal::class, 'create']);
// Route::get('/Create', [BoletoGetBan::class, 'create']);

 Route::match(['get', 'post', 'options'], '/webhook', [WebhookController::class, 'capture']);
//Route::post('/webhook', [WebhookController::class, 'capture']);
//Route::any('/webhook', [WebhookController::class, 'capture']);


/////////////santander/////////////////
Route::post('/Workspace', [WorkspaceSantanderController::class, 'create']);
Route::post('/BuscarAll', [WorkspaceBusca::class, 'index']);
Route::post('/BuscarOne', [WorkspaceBusca::class, 'search']);
Route::post('/Delete', [WorkspaceBusca::class, 'destroy']);
Route::post('/Edit', [WorkspaceBusca::class, 'edit']);


Route::post('/santander/create', [CreateBoletoSTD::class, 'create']);

Route::post('/santander/Baixa', [SolicitacaoBaixaBoleto::class, 'store']);

Route::post('/Localizar', [BoletosRest::class, 'store']);
 
//////////sicredi
Route::post('/Sicredi/CreateBoleto', [CreateBoletoSC::class, 'create']);
Route::post('/Sicredi/Consultar', [ConsultaBaixaBoletoController::class, 'search']);
Route::post('/Sicredi/Buscar', [BuscaCobraca::class, 'search']);
Route::get('/Sicredi/Print', [PrintBoleto::class, 'Print']);
Route::post('/Sicredi/SolicitacaoBaixa', [BaixaBoletoSicredi::class, 'store']);

//////////Bradesco
 
Route::post('/Bradesco/Tokens', [GetTokenBradesco::class, 'create']);


 
Route::post('/Bradesco/CreateBoleto', [CreateBoletoBradesco::class, 'create']);
//////////Banco do Brasil

 
Route::post('/BancoBrasil/Tokens', [BancoBrasilTokenService::class, 'create']);

//////////////// inter


 
Route::post('/emitir', [CreateBoletoInter::class, 'generate']);
Route::get('/consultar/{codigo}', [CreateBoletoInter::class, 'consultar']);
Route::post('/cancelar/{codigo}', [CreateBoletoInter::class, 'cancelar']);
Route::get('/baixar-pdf/{codigo}', [CreateBoletoInter::class, 'baixarPdf']);
