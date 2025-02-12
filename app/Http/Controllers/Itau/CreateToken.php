<?php

namespace App\Http\Controllers\Itau;

use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametroBanco;

/**
 * Classe para CreateToken  do banco itau
 *
 * Autor: Rubens dos Santos
 * Email: salvadorbba@gmail.com
 * Celular: (71) 99675-8056
 */
class CreateToken extends Controller
{
    // Função para criar um token de acesso
    public static function create(Request $request)
    {
        // Validação de entrada: 'id' deve ser um inteiro
        $request->validate(['id' => 'required|integer']);

        // Busca os parâmetros do banco com o id fornecido
        $parametros = ParametroBanco::find($request->id);
        // Se os parâmetros não forem encontrados, retorna erro 404
        if (!$parametros) {
            return response()->json(['error' => 'Chave inválida'], 404);
        }
        $expires_in_s = 300; // segundos
        $expires_in_minutes = $expires_in_s / 60;
        $expires_in = $expires_in_minutes * 60; // Define o tempo de expiração do token em 5 minutos (em segundos)

        // Verifica se o token ainda é válido, comparando a data do token com o tempo atual
        if ($parametros->token && strtotime($parametros->data_token) > time()) {
          
           return  $parametros->token;
          
            // return response()->json([
            //     'codigo' => 200,
            //     'data_token' => $parametros->data_token,
            //     'token' => $parametros->token,
            //     'origem' => 'Banco de Dados' // Indica que o token vem do banco de dados
            // ]);
        }

        // Gera novos UUIDs necessários para a requisição
        $x_itau_flowID = ClassGenerica::CreateUuid(2);
        $x_itau_correlationID = ClassGenerica::CreateUuid(1);

        // Define o caminho do certificado
        $certificadoPath = storage_path($parametros->certificado);
        // Inicializa a sessão cURL para fazer a requisição HTTP para o API do Itau
        $curl = curl_init();

        // Configurações da requisição cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $parametros->url1, // URL da API de autenticação do Itau
         CURLOPT_SSLCERTTYPE => 'P12', // Tipo do certificado
            CURLOPT_SSLCERT => $certificadoPath, // Caminho do certificado
            CURLOPT_SSLCERTPASSWD => $parametros->senha, // Senha do certificado
            CURLOPT_RETURNTRANSFER => true, // Retorna a resposta como string
            CURLOPT_CUSTOMREQUEST => 'POST', // Método HTTP POST
            CURLOPT_POSTFIELDS => http_build_query([ // Dados enviados na requisição
                'grant_type' => 'client_credentials',
                'client_id' => $parametros->client_id,
                'client_secret' => $parametros->client_secret,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded', // Cabeçalho de tipo de conteúdo
                'x-itau-flowID: ' . $x_itau_flowID, // Cabeçalho personalizado para o fluxo
                'x-itau-correlationID: ' . $x_itau_correlationID, // Cabeçalho personalizado para correlação
            ],
        ]);

        // Executa a requisição cURL
        $response = curl_exec($curl);
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
        // Retorna a resposta com o novo token e a origem como "API Itau"
        return response()->json([
            'codigo' => 200,
            'data_token' => $parametros->data_token,
            'token' => $data->access_token,
            'origem' => 'API Itau' // Indica que o token foi gerado pela API do Itau
        ]);
    }
}
