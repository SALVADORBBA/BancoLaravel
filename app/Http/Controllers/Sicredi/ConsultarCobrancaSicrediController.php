<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Sicredi\CreateTokensSC;
use Illuminate\Http\Request;
use App\Models\Contasrec;
use App\Models\ParametrosBancos;
use App\Models\BoletosMovimentacao;
use App\Models\BoletoLiquidacao;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Services\GetTokenSicredi;
use Exception;

class ConsultarCobrancaSicrediController extends Controller
{
    private $key;
    private $tipo;
    private $titulos;
    private $parametros;
    private $token;

    public function __construct($key, $tipo = null)
    {
        $this->key = $key;
        $this->tipo = $tipo;
        $this->titulos = ContasReceber::findOrFail($this->key);
        $this->parametros = ParametroBanco::findOrFail($this->titulos->parametros_bancos_id);
        $this->token = CreateTokensSC::create($this->parametros);
    }

    public function search()
    {
        try {
            $url = $this->parametros->ambiente == 1 ? $this->parametros->url_busca_producao : $this->parametros->url2;
            $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
            $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
            $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';

            $queryParams = http_build_query([
                'cooperativa' => $cooperativa,
                'posto' => $posto,
                'codigoBeneficiario' => $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345,
                'nossoNumero' => $this->titulos->seunumero,
            ]);

            $fullUrl = $url . '?' . $queryParams;
            $headers = [
                'x-api-key: ' . $xapikey,
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
                'cooperativa: ' . $cooperativa,
                'posto: ' . $posto
            ];

            $response = $this->makeCurlRequest($fullUrl, $headers);
            $responseData = json_decode($response);

            if (isset($responseData->code)) {
                return response()->json(['error' => 'Erro na consulta do boleto.'], 400);
            }

            $this->processBoletoData($responseData);
            return response()->json(['message' => 'Consulta realizada com sucesso.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function makeCurlRequest($url, $headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function processBoletoData($response)
    {
        $situacaoMap = [
            'VENCIDO' => 7,
            'LIQUIDADO' => 8,
            'EM CARTEIRA' => 5,
            'EM CARTEIRA PIX' => 10,
            'LIQUIDADO COMPE' => 11,
            'LIQUIDADO PIX' => 13,
            'BAIXADO POR SOLICITACAO' => 14,
            'DEFAULT' => 12
        ];

        $situacao_boleto = $situacaoMap[$response->situacao] ?? $situacaoMap['DEFAULT'];
        $movimentacao = BoletosMovimentacao::firstOrNew(['contasreceber_id' => $this->key]);

        $movimentacao->fill([
            'contasreceber_id' => $this->titulos->id,
            'multa' => $response->multa ?? 0,
            'abatimento' => $response->abatimento ?? 0,
            'tipojuros' => $response->tipoJuros ?? null,
            'diasprotesto' => $response->diasProtesto ?? null,
            'datamovimento' => $response->dataMovimento ?? null,
        ])->save();

        if (isset($response->dadosLiquidacao)) {
            BoletoLiquidacao::updateOrCreate(
                ['boletos_movimentacao_id' => $movimentacao->id],
                [
                    'data_liquidacao' => $response->dadosLiquidacao->data,
                    'valor' => $response->dadosLiquidacao->valor,
                    'multa' => $response->dadosLiquidacao->multa,
                ]
            );
        }
    }
}
