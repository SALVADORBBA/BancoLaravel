<?php
 

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\ConsultarCobrancaSicrediController;
use App\Http\Controllers\Controller;
use App\Models\ContasReceber;
use Illuminate\Http\Request;
use App\Models\ParametrosBancos;
  
use App\Models\ParametroBanco;
use App\Services\GetTokenSicredi;
use App\Services\ConsultarCobrancaSicredi;
use Illuminate\Support\Facades\Http;

class ConsultaBaixaBoletoController extends Controller
{
    private $key;
    private $data;
    private $parametros;
    private $token;

    public function __construct(request $request)
    {
        $this->key =$request->key;
        $this->data = $request->data;
        
        // Busca os parâmetros do banco
        $this->parametros = ParametroBanco::findOrFail($this->key);
                // Gera o token Sicredi
         $this->token =CreateTokensSC::create($this->parametros);
    }

    public function search()
    {
        try {
            $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
            $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
            $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
            $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
            $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

            $data = $this->data ?? date('d/m/Y');

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url_param.'/liquidados/dia?codigoBeneficiario='.$codigoBeneficiario.'&dia=' . $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' .  $this->token,
                    'Content-Type: application/json',
                    'Cooperativa: ' . $cooperativa,
                    'Posto: ' . $posto,
                    'x-api-key: ' . $xapikey,
                ],
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode == 200) {
                $responseObject = json_decode($response);
                return [
                    'success' => true,
                    'data' => $responseObject,
                    'message' => 'Consulta realizada com sucesso'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao realizar consulta. Status code: ' . $httpCode
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao processar a requisição: ' . $e->getMessage()
            ];
        }
    }
}
