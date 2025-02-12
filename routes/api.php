<?php

use App\Http\Controllers\Itau\CreateBoleto;
use App\Http\Controllers\Itau\CreateToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Itau\TokenItauController;
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
Route::post('/boleto/create', [CreateBoleto::class, 'create']);