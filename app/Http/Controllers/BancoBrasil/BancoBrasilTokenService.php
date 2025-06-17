<?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametroBanco;
use Illuminate\Support\Carbon;

class BancoBrasilTokenService
{
    private $key;
    private $parametros;

    /**
     * Construtor da classe GetTokenBancoBrasil.
     *
     * @param string|int $key - Chave primária da tabela parametros_bancos
     */
    public function __construct($key)
    {
        $this->key =  $key;
        $this->parametros = ParametroBanco::find($this->key);
    }

    /**
     * Obtém ou renova o token de acesso.
     *
     * @return object|false
     */
    public function create()
    {

 
        $margem_segundos = 600;
        $tempo_atual_ajustado = time() - $margem_segundos;
        $data_token_timestamp = isset($this->parametros->data_token) ? strtotime($this->parametros->data_token) : 0;

        if (!empty($this->parametros->token) && $data_token_timestamp > $tempo_atual_ajustado) {
            return  [
                'codigo' => 200,
                'data_token' => $this->parametros->data_token,
                'token' => $this->parametros->token,
                'origem' => 'Banco de Dados',
            ];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->parametros->url1 . '?gw-dev-app-key=' . $this->parametros->gw_dev_app_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=cobrancas.boletos-info%20cobrancas.boletos-requisicao',
            CURLOPT_HTTPHEADER => array(
                'Authorization:' . $this->parametros->authorization,
                'Content-Type: application/x-www-form-urlencoded',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

     $response = json_decode($response);

        if (isset($response->access_token)) {
            $this->parametros->token = $response->access_token;
            $this->parametros->data_token = date('Y-m-d H:i:s', time() + ($response->expires_in ?? $margem_segundos));
            $this->parametros->save();

            return  [
                'codigo' => 201,
                'data_token' => $this->parametros->data_token,
                'token' => $response->access_token,
                'origem' => 'API Banco do Brasil',
            ];
        }

        return false;
    }
}
