<?php

namespace App\Http\Controllers\Bradesco;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametroBanco;
use Illuminate\Support\Carbon;

/**
 * Classe para obter o token de acesso da API do Bradesco via certificado mTLS.
 *
 * Autor: Rubens dos Santos
 * Email: salvadorbba@gmail.com
 */
class GetTokenBradesco extends Controller
{
    /**
     * Gera e retorna o token de acesso da API Bradesco usando certificados .pem/.key
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function create($key)
    {
        // Busca os parâmetros de integração do banco
        $parametros = ParametroBanco::find($key);

        if (!$parametros) {
            return response()->json(['erro' => 'Parâmetros do banco não encontrados'], 404);
        }

        // Verifica se o token ainda está válido
        if ($parametros->token && $parametros->data_token && strtotime($parametros->data_token) > time()) {
            return    $parametros->token;
              
        }

        $certificadoPublico = storage_path('app/public/certificado/' . $parametros->id . '/compdados.homologacao.pem');
        $chavePrivada        = storage_path('app/public/certificado/' . $parametros->id . '/compdados.homologacao.key.pem');

        if (!file_exists($certificadoPublico) || !file_exists($chavePrivada)) {
            return response()->json(['erro' => 'Certificados não encontrados'], 500);
        }

        $client_id     = $parametros->client_id;
        $client_secret = $parametros->client_secret;

        $postFields = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $client_id,
            'client_secret' => $client_secret
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $parametros->url1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSLCERT => $certificadoPublico,
            CURLOPT_SSLKEY  => $chavePrivada,
            CURLOPT_KEYPASSWD => $parametros->senha,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $erro = curl_error($curl);
            curl_close($curl);
            return response()->json(['erro' => 'Erro cURL: ' . $erro], 500);
        }

        curl_close($curl);

        $jsonResponse = json_decode($response, true);

        if ($httpCode !== 200 || !isset($jsonResponse['access_token'])) {
            return response()->json([
                'erro' => 'Falha ao obter token',
                'http_code' => $httpCode,
                'response' => $jsonResponse,
            ], $httpCode);
        }

        // Atualiza o token e a data de expiração no banco de dados
        $parametros->token = $jsonResponse['access_token'];
        $parametros->data_token = now()->addSeconds($jsonResponse['expires_in'] ?? 300);
        $parametros->save();

        return  $parametros->token;
 
      
    }
}
