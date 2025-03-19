<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;

class PrintBoleto extends Controller
{
    private $key;
    private $linhaDigitavel;
    private $parametros;
    private $token;
    private $nossoNumero;
    public function __construct(request $request)
    {
        $this->key = $request->key;
        $this->linhaDigitavel = $request->linhadigitavel;

        $this->nossoNumero = $request->nossoNumero;
        // Busca os parâmetros do banco
        $this->parametros = ParametroBanco::findOrFail($this->key);
        // Gera o token Sicredi
        $this->token = CreateTokensSC::create($this->parametros);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function Print()
    {

        $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
        $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
        $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
        $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
        $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

        $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url_param.'/pdf?linhaDigitavel=74891121150039736789903123451001187340000000050', // Linha digitável fixa
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'x-api-key:  '.$xapikey, // Coloque a chave API correta aqui
                    'Authorization: Bearer ' .  $this->token
                ],
            ]);

            // Executa a requisição cURL
            $response = curl_exec($curl);
            curl_close($curl);

 
            $ano = date('Y');
            $mes = date('m');
            $pastaDestino = "app/pdf/sicredi/boleto/{$this->key}/{$ano}/{$mes}/";

            // Cria a pasta se não existir
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }

            // Nome do arquivo PDF
            $nomeArquivo = $pastaDestino .  $this->linhaDigitavel . '.pdf';

            // Salva o conteúdo do PDF no arquivo
            file_put_contents($nomeArquivo, $response);
 
 return response()->json(['message' => 'PDF salvo com sucesso', 'file' => $nomeArquivo], 200);

      
    }

}