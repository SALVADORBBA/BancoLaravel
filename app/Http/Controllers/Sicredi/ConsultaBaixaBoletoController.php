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
      
       
        $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
        $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
        $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
        $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
        $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

  
            $data = $this->data ?? date('d/m/Y');

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url_param.'/liquidados/dia?codigoBeneficiario='.$codigoBeneficiario.'&dia=' . $data, // Substitua pela URL de seu endpoint
                CURLOPT_RETURNTRANSFER => true, // Retorna a resposta como string
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10, // Redirecionamentos máximos
                CURLOPT_TIMEOUT => 10, // Timeout de 10 segundos
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // Usando HTTP 1.1
                CURLOPT_CUSTOMREQUEST => 'GET', // Ou o método HTTP que você preferir
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' .  $this->token, // Substitua com seu token de autenticação
                    'Content-Type: application/json', // Tipo de conteúdo JSON
                    'Cooperativa: ' . $cooperativa, // Exemplo de cabeçalho específico
                    'Posto: ' . $posto, // Exemplo de cabeçalho específico
                    'x-api-key: ' . $xapikey, // Exemplo de chave API
                ],
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, // Garante que o TLS 1.2 será utilizado
            ]);

            $response = curl_exec($curl);

            // Decodificando a resposta JSON para objeto PHP
            $responseObject = json_decode($response);
             return $responseObject;
  
    }
}
