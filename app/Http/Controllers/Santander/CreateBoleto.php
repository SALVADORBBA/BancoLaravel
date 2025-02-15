<?php

namespace App\Http\Controllers\Santander;

use App\Http\Controllers\Controller;
 
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Models\ControleMeuNumeros;
use stdClass;

class CreateBoleto extends Controller
{
    // Usando Dependency Injection para instanciar o CreateToken
    private $titulos;
    private $parametros;
    private $pessoas;
    private $Token;
    private $tipo;
    public function __construct(Request $request)
    {
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->pessoas = $this->titulos->pessoa;
        $this->Token = CreateTokenSTD::create($this->titulos->parametros_bancos_id);
        $this->tipo = $request->tipo;
    }




    public function create()
    {
 


        $meunumero=  ControleMeuNumeroController::create($this->parametros->id);
 
 
        if ($this->tipo == 1) {
            return response()->json([
                'status'  => 'success',
                'Token'    => $this->Token
            ]);
        }

  
        // Atualizar os dados no banco de dados com base na resposta
    //  return   $responseData = json_decode($response);

    //     if ($responseData && isset($responseData->data->dado_boleto->dados_individuais_boleto[0])) {
    //         $boleto = $responseData->data->dado_boleto->dados_individuais_boleto[0];

    //         // Atualizar os dados no banco de dados
    //         ContasReceber::where('id', $this->titulos->id)->update([
    //             'nossonumero'   => $boleto->numero_nosso_numero,
    //             'codigobarras'  => $boleto->codigo_barras ?? null,
    //             'linhadigitavel' => $boleto->numero_linha_digitavel ?? null,
    //         ]);

    //         ControleMeuNumeros::where('id',$meunumero->id)->update([
    //             'ultimo_numero'   => $meunumero->numero,
    //             'status'  => 'uso',
    //                        ]);


    //         Log::info("Boleto cadastrado com sucesso!", [
    //             'id_titulo' => $this->titulos->id,
    //             'nosso_numero' => $boleto->numero_nosso_numero
    //         ]);

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Boleto cadastrado com sucesso!',
    //             'data'    => [
    //                 'nosso_numero'   => $boleto->numero_nosso_numero,
    //                 'codigo_barras'  => $boleto->codigo_barras ?? null,
    //                 'linha_digitavel' => $boleto->numero_linha_digitavel ?? null,
    //             ]
    //         ]);
    //     } else {
    //         // Se houver erro, registrar logs para depuração
    //         Log::error("Erro ao cadastrar boleto", [
    //             'response' => $response
    //         ]);

    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Erro ao cadastrar o boleto. Verifique os logs para mais detalhes.',
    //             'response' => $responseData
    //         ], 400);
    //     }
    }
}
