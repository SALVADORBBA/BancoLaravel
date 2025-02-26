<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CreateTokensSC extends Controller
{
    public static function create($parametros)
    {
        if (!$parametros) {
            return response()->json(['error' => 'Chave inválida'], 404);
        }

        $now = Carbon::now();

        // Se o access_token ainda for válido, retorna ele
        if ($parametros->token && strtotime($parametros->data_token) > time()) {
            return $parametros->token;
        }

        // Se o refresh_token ainda for válido, usa ele para renovar o access_token
        if ($parametros->refresh_token && strtotime($parametros->data_refresh_token) > time()) {
            return self::refreshToken($parametros);
        }

        // Se não houver refresh_token ou estiver expirado, gera um novo token do zero
        return self::generateNewToken($parametros);
    }

    private static function refreshToken($parametros)
    {
        $url = ($parametros->ambiente == 1) ? $parametros->url_token_producao : $parametros->url1;
        $client_id = ($parametros->ambiente == 2) ? $parametros->client_id : $parametros->client_id_producao;

        $postData = "refresh_token={$parametros->refresh_token}&grant_type=refresh_token";

        $data = self::makeRequest($url, $postData, $client_id);

        if (!isset($data->access_token)) {
            return response()->json(['error' => 'Erro ao renovar token'], 500);
        }

        // Atualiza o banco com os novos valores
        $parametros->refresh_token = $data->refresh_token ?? $parametros->refresh_token;
        $parametros->data_refresh_token = now()->addSeconds($data->refresh_expires_in ?? 900);
        $parametros->token = $data->access_token;
        $parametros->data_token = now()->addSeconds($data->expires_in);

        $parametros->save(); // Salva no banco de dados

        return $parametros->token;
    }

    private static function generateNewToken($parametros)
    {
        $password = ($parametros->ambiente == 1) ? $parametros->password : $parametros->password_homologacao;
        $usuario = ($parametros->ambiente == 1) ? $parametros->username : $parametros->usuario_homologacao;
        $url = ($parametros->ambiente == 1) ? $parametros->url_token_producao : $parametros->url1;
        $client_id = ($parametros->ambiente == 2) ? $parametros->client_id : $parametros->client_id_producao;

        $postData = "username={$usuario}&password={$password}&scope={$parametros->scope}&grant_type=password";

        $data = self::makeRequest($url, $postData, $client_id);

        if (!isset($data->access_token) || !isset($data->refresh_token)) {
            return response()->json(['error' => 'Erro ao obter novo token'], 500);
        }

        // Atualiza os valores e salva no banco
        $parametros->token = $data->access_token;
        $parametros->data_token = now()->addSeconds($data->expires_in);
        $parametros->refresh_token = $data->refresh_token;
        $parametros->data_refresh_token = now()->addSeconds($data->refresh_expires_in);

        $parametros->save(); // Salva no banco de dados

        return $parametros->token;
    }


    private static function makeRequest($url, $postData, $client_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'x-api-key: ' . $client_id,
                'context: COBRANCA'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }
}