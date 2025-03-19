<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;
use Exception;

class BaixaBoletoSicredi extends Controller
{
    private $key;
    private $titulos;
    private $parametros;
    private $token;

    public function __construct(Request $request)
    {
        $this->key = $request->id;
        $this->titulos = ContasReceber::findOrFail($this->key);
        $this->parametros = ParametroBanco::findOrFail($this->titulos->parametros_bancos_id);
        $this->token = CreateTokensSC::create($this->parametros);
    }

    /**
     * Process the boleto cancellation request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        try {
            $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
            $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
            $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
            $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
            $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url_param . '/' . $this->titulos->nossonumero . '/baixa',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS => '{}',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->token,
                    'x-api-key: ' . $xapikey,
                    'Content-Type: application/json',
                    'Cooperativa: ' . $cooperativa,
                    'codigoBeneficiario: ' . $codigoBeneficiario,
                    'posto: ' . $posto,
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            $httpCode >= 400 ? throw new Exception('Error in API request. HTTP Code: ' . $httpCode) : null;
            
            curl_close($curl);

            $responseData = json_decode($response);
            
            // Update the status in the database
            ContasReceber::where('id', $this->titulos->id)->update([
                'status' => 5, // Assuming 5 is the status code for cancelled boletos
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Baixa realizada com sucesso!',
                'data' => $responseData
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao realizar baixa do boleto: ' . $e->getMessage()
            ], 500);
        }
    }
}
