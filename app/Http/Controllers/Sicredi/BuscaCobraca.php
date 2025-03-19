<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;

class BuscaCobraca extends Controller
{
    private $key;
    private $tipo;
    private $parametros;
    private $token;
    private $nossoNumero;
    public function __construct(request $request)
    {
        $this->key = $request->key;
        $this->tipo = $request->tipo;

        $this->nossoNumero = $request->nossoNumero;
        // Busca os parâmetros do banco
        $this->parametros = ParametroBanco::findOrFail($this->key);
        // Gera o token Sicredi
        $this->token = CreateTokensSC::create($this->parametros);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function search()
    {
        try {
            $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
            $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
            $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
            $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
            $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url_param . '?codigoBeneficiario=' . $codigoBeneficiario . '&nossoNumero=' . $this->nossoNumero,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token,
                    'x-api-key: ' . $xapikey,
                    'Content-Type: application/json',
                    'cooperativa: ' . $cooperativa,
                    'posto: ' . $posto,
                    'data-movimento: true'
                ),
            ));

            $response = curl_exec($curl);

            if ($response === false) {
                throw new \Exception('Erro na requisição cURL: ' . curl_error($curl));
            }

            curl_close($curl);

            $decodedResponse = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erro ao decodificar resposta JSON: ' . json_last_error_msg());
            }

            return [
                'success' => true,
                'data' => $decodedResponse,
                'message' => 'Consulta realizada com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao realizar consulta: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}