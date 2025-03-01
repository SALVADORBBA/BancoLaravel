<?php
 

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametrosBancos;
use App\Models\Contasrec;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Services\GetTokenSicredi;
use App\Services\ConsultarCobrancaSicredi;
use Illuminate\Support\Facades\Http;

class ConsultaBaixaBoletoController extends Controller
{
    private $key;
    private $tipo;
    private $parametros;
    private $token;

    public function __construct($key, $tipo = null)
    {
        $this->key = $key;
        $this->tipo = $tipo;
        
        // Busca os parâmetros do banco
        $this->parametros = ParametroBanco::findOrFail($this->key);
                // Gera o token Sicredi
         $this->token =CreateTokensSC::create($this->parametros);
    }

    public function onShow(Request $request)
    {
        $url_param = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
        $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
        $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
        $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';
        $codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;

        if ($this->tipo == 1) {
            $data = now()->format('d/m/Y');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Cooperativa' => $cooperativa,
                'Posto' => $posto,
                'x-api-key' => $xapikey,
            ])->get("{$url_param}/liquidados/dia", [
                'codigoBeneficiario' => $codigoBeneficiario,
                'dia' => $data,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Erro na requisição Sicredi'], 500);
            }

            $responseObject = $response->json();

            // Ordena do menor para o maior
            usort($responseObject['items'], fn ($b, $a) => $a['nossoNumero'] <=> $b['nossoNumero']);

            $resposta = [];

            foreach ($responseObject['items'] as $item) {
                $objeto = ContasReceber::where('nossonumero', $item['nossoNumero'])
                    ->whereIn('status_id', [2, 5, 7, 10])
                    ->first();

                if ($objeto) {
                    $responses = new ConsultarCobrancaSicredi($objeto->id);
                    $message = $responses->search();

                    if ($message) {
                        $resposta[] = $message;
                    }
                }
            }

            return response()->json($resposta);
        } else {
            $objetos = Contasrec::whereIn('status_id', [2, 5, 7, 10])
                ->inRandomOrder()
                ->limit(1)
                ->get();

            $ids = $objetos->pluck('id');

            return response()->json($ids);
        }
    }
}
