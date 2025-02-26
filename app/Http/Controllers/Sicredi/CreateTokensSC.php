<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class CreateTokensSC extends Controller
{
    /**
     * Função principal para criar ou renovar o token de acesso.
     *  Desenvolvedor: Rubens dos Santos
     *  Telefone: (71) 99675-8056
     *  Email: salvadorbba@gmail.com
     * @param mixed $parametros
     * @return string|Response
     */
    public static function create($parametros)
    {
        try {
            if (!$parametros) {
                // Retorna erro se não houver parâmetros válidos
                return response()->json(['error' => 'Chave inválida'], 404);
            }

            $now = Carbon::now();

            // Verifica se o access_token ainda é válido
            if ($parametros->token && strtotime($parametros->data_token) > time()) {
                return $parametros->token;
            }

            // Se o refresh_token ainda for válido, renova o access_token
            if ($parametros->refresh_token && strtotime($parametros->data_refresh_token) > time()) {
                return self::refreshToken($parametros);
            }

            // Se o refresh_token estiver expirado ou não houver, gera um novo token
            return self::generateNewToken($parametros);
        } catch (Exception $e) {
            // Loga o erro e relança a exceção
            Log::error('Erro na transação: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Função para renovar o token de acesso utilizando o refresh_token.
     *
     * @param mixed $parametros
     * @return string|Response
     */
    private static function refreshToken($parametros)
    {
        try {
            // Define a URL de acordo com o ambiente
            $url = ($parametros->ambiente == 1) ? $parametros->url_token_producao : $parametros->url1;
            $client_id = ($parametros->ambiente == 2) ? $parametros->client_id : $parametros->client_id_producao;

            // Prepara os dados para a requisição
            $postData = "refresh_token={$parametros->refresh_token}&grant_type=refresh_token";

            // Realiza a requisição para renovar o token
            $data = self::makeRequest($url, $postData, $client_id);

            // Verifica se o novo token foi obtido
            if (!isset($data->access_token)) {
                return response()->json(['error' => 'Erro ao renovar token'], 500);
            }

            // Atualiza os dados no banco
            $parametros->refresh_token = $data->refresh_token ?? $parametros->refresh_token;
            $parametros->data_refresh_token = now()->addSeconds($data->refresh_expires_in ?? 900);
            $parametros->token = $data->access_token;
            $parametros->data_token = now()->addSeconds($data->expires_in);

            // Salva no banco de dados
            $parametros->save();

            return $parametros->token;
        } catch (Exception $e) {
            // Loga o erro e relança a exceção
            Log::error('Erro na transação: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Função para gerar um novo token de acesso.
     *
     * @param mixed $parametros
     * @return string|Response
     */
    private static function generateNewToken($parametros)
    {
        try {
            // Define as credenciais de acesso de acordo com o ambiente
            $password = ($parametros->ambiente == 1) ? $parametros->password : $parametros->password_homologacao;
            $usuario = ($parametros->ambiente == 1) ? $parametros->username : $parametros->usuario_homologacao;
            $url = ($parametros->ambiente == 1) ? $parametros->url_token_producao : $parametros->url1;
            $client_id = ($parametros->ambiente == 2) ? $parametros->client_id : $parametros->client_id_producao;

            // Prepara os dados para a requisição
            $postData = "username={$usuario}&password={$password}&scope={$parametros->scope}&grant_type=password";

            // Realiza a requisição para obter o novo token
            $data = self::makeRequest($url, $postData, $client_id);

            // Verifica se o token foi obtido com sucesso
            if (!isset($data->access_token) || !isset($data->refresh_token)) {
                return response()->json(['error' => 'Erro ao obter novo token'], 500);
            }

            // Atualiza os dados no banco de dados
            $parametros->token = $data->access_token;
            $parametros->data_token = now()->addSeconds($data->expires_in);
            $parametros->refresh_token = $data->refresh_token;
            $parametros->data_refresh_token = now()->addSeconds($data->refresh_expires_in);

            // Salva no banco de dados
            $parametros->save();

            return $parametros->token;
        } catch (Exception $e) {
            // Loga o erro e relança a exceção
            Log::error('Erro na transação: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Função para fazer a requisição HTTP para a API.
     *
     * @param string $url
     * @param string $postData
     * @param string $client_id
     * @return object
     */
    private static function makeRequest($url, $postData, $client_id)
    {
        try {
            // Configura a requisição cURL
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

            // Executa a requisição cURL
            $response = curl_exec($curl);
            curl_close($curl);

            // Retorna o resultado decodificado como JSON
            return json_decode($response);
        } catch (Exception $e) {
            // Loga o erro e relança a exceção
            Log::error('Erro na transação: ' . $e->getMessage());
            throw $e;
        }
    }
}
