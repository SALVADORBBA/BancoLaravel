<?php

namespace App\Http\Controllers\Santander;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametroBanco;
use App\Http\Controllers\ClassGlobais\ClassGenerica;

/**
 * Classe para CreateToken  do banco Santander
 *
 * Autor: Rubens dos Santos
 * Email: salvadorbba@gmail.com
 * Celular: (71) 99675-8056
 */

class CreateTokenSTD extends Controller
{
    // Função para criar um token de acesso
    public static function create($key)
    {
 
 
        // Busca os parâmetros do banco com o id fornecido
        $parametros = ParametroBanco::find($key);
        // Se os parâmetros não forem encontrados, retorna erro 404
        if (!$parametros) {
            return response()->json(['error' => 'Chave inválida'], 404);
        }
        $expires_in_s =  $parametros->expires_in; // segundos
        $expires_in_minutes = $expires_in_s / 60;
        $expires_in = $expires_in_minutes * 60; // Define o tempo de expiração do token em 5 minutos (em segundos)
        // Verifica se o token ainda é válido, comparando a data do token com o tempo atual

        if ($parametros->token && strtotime($parametros->data_token) > time()) {
            return $parametros->token;
        }

        // Define o caminho do certificado
        $certificadoPath = storage_path($parametros->certificado);
        // Inicializa a sessão cURL para fazer a requisição HTTP para o API do Itau
 
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $parametros->url1,
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERT =>  $certificadoPath,
            CURLOPT_SSLCERTPASSWD => $parametros->senha,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'client_id=' . $parametros->client_id . '&client_secret=' . $parametros->client_secret . '&grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
 
        // Verifica se houve erro durante a requisição cURL
        if ($response === false) {
            return response()->json(['error' => 'CURL Error: ' . curl_error($curl)], 500);
        }
        // Decodifica a resposta JSON
        $data = json_decode($response);

        return  $data ;
        // Verifica se o campo access_token está presente na resposta
        if (!isset($data->access_token)) {
            return response()->json(['error' => 'Erro ao obter token'], 500);
        }
        // Atualiza o token e a data de expiração no banco de dados
        $parametros->update([
            'token' => $data->access_token,
            'data_token' => now()->addSeconds($expires_in), // Data de expiração
        ]);
        return $parametros->token;
    }
}
