<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;

class CreateTokensSC extends Controller
{
 
/**
 * Classe para CreateToken  do banco Santander
 *
 * Autor: Rubens dos Santos
 * Email: salvadorbba@gmail.com
 * Celular: (71) 99675-8056
 */

 
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
 
        $password= ($parametros->ambiente == 1) ? $parametros->password : $parametros->password_homologacao;
        $usuario= ($parametros->ambiente == 1) ? $parametros->username : $parametros->usuario_homologacao;
       // Caso o token tenha expirado, gera um novo access_token

      $Url= ($parametros->ambiente == 1) ? $parametros->url_token_producao : $parametros->url1;
      $clint_id = ($parametros->ambiente == 2) ? $parametros->client_id : $parametros->client_id_producao;
      
      $url_Composicao = "username={$usuario}&password={$password}&scope={$parametros->scope}&grant_type=password";
      

       // Inicializar uma requisição CURL.
       $curl = curl_init();

       // Configurar as opções da requisição CURL.
       curl_setopt_array($curl, array(
           CURLOPT_URL =>$Url,

           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'POST',
           CURLOPT_POSTFIELDS => $url_Composicao,
           CURLOPT_HTTPHEADER => array(
               'ContentType: application/x-www-form-urlencoded',
               'x-api-key: ' . $clint_id,
               'context: COBRANCA'
           ),
       ));

       // Executar a requisição CURL e obter a resposta.
       $response = curl_exec($curl);

       // Encerrar a requisição CURL.
       curl_close($curl);

       // Decodificar a resposta JSON.
       $response = json_decode($response);
 
        // Verifica se houve erro durante a requisição cURL
        if ($response === false) {
            return response()->json(['error' => 'CURL Error: ' . curl_error($curl)], 500);
        }
        // Decodifica a resposta JSON
        $data = json_decode($response);
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








