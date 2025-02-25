<?php

namespace App\Http\Controllers\Itau;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Itau\CreateToken; // Adicione o namespace da classe CreateToken
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
        $this->Token = CreateToken::create($this->titulos->parametros_bancos_id);
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

        // Montagem do payload
        $data = new stdClass();
        $data->data = new stdClass();
        $data->data->etapa_processo_boleto = 'validacao';
        $data->data->codigo_canal_operacao = "API";
        $data->data->beneficiario = new stdClass();
        $data->data->beneficiario->id_beneficiario = $this->parametros->numero_beneficiario;

        // Dados do boleto-
        $data->data->dado_boleto = new stdClass();
        $data->data->dado_boleto->descricao_instrumento_cobranca = "boleto";
        $data->data->dado_boleto->tipo_boleto = "a vista";
        $data->data->dado_boleto->codigo_carteira = $this->parametros->carteira;
        $data->data->dado_boleto->valor_total_titulo = ClassGenerica::formatarValorItau($this->titulos->valor);
        $data->data->dado_boleto->codigo_especie = "01";
        $data->data->dado_boleto->valor_abatimento = ClassGenerica::formatarValorItau("000");
        $data->data->dado_boleto->data_emissao = date('Y-m-d');
        $data->data->dado_boleto->indicador_pagamento_parcial = true;
        $data->data->dado_boleto->quantidade_maximo_parcial = 0;

        // Dados do pagador
        $data->data->dado_boleto->pagador = new stdClass();
        $data->data->dado_boleto->pagador->pessoa = new stdClass();
        $data->data->dado_boleto->pagador->pessoa->tipo_pessoa = new stdClass();

        $data->data->dado_boleto->pagador->pessoa->nome_pessoa = ClassGenerica::limitarTexto($this->pessoas->nome, 45);
        $documentoTratado = ClassGenerica::TrataDoc($this->pessoas->documento);
        $tipoPessoa = strlen($documentoTratado) === 14 ? "J" : "F";

        $data->data->dado_boleto->pagador->pessoa->tipo_pessoa->codigo_tipo_pessoa = $tipoPessoa;

        if ($tipoPessoa === "J") {
            $data->data->dado_boleto->pagador->pessoa->tipo_pessoa->numero_cadastro_nacional_pessoa_juridica = $documentoTratado;
        } else {
            $data->data->dado_boleto->pagador->pessoa->tipo_pessoa->numero_cadastro_pessoa_fisica = $documentoTratado;
        }

        // Endereço do pagador
        $endereco = ClassGenerica::limitarTexto($this->pessoas->rua, 38) . ', ' . ClassGenerica::limitarTexto($this->pessoas->numero, 5);
        $data->data->dado_boleto->pagador->endereco = new stdClass();
        $data->data->dado_boleto->pagador->endereco->nome_logradouro = $endereco;
        $data->data->dado_boleto->pagador->endereco->nome_bairro = $this->pessoas->bairro;
        $data->data->dado_boleto->pagador->endereco->nome_cidade = $this->pessoas->cidade;
        $data->data->dado_boleto->pagador->endereco->sigla_UF = $this->pessoas->uf;
        $data->data->dado_boleto->pagador->endereco->numero_CEP = $this->pessoas->cep;

        // Dados individuais do boleto
        $data->data->dado_boleto->dados_individuais_boleto = [];
        $dados_individuais_boleto = new stdClass();
        $dados_individuais_boleto->numero_nosso_numero = $meunumero->numero;
        $dados_individuais_boleto->data_vencimento = $this->titulos->data_vencimento;
        $dados_individuais_boleto->valor_titulo = ClassGenerica::formatarValorItau($this->titulos->valor);
        $dados_individuais_boleto->texto_uso_beneficiario = "2";
        $dados_individuais_boleto->texto_seu_numero = "2";
        $data->data->dado_boleto->dados_individuais_boleto[] = $dados_individuais_boleto;

        // Instruções de cobrança
        $data->data->dado_boleto->instrucao_cobranca = [];
        $instrucao_cobranca = new stdClass();
        $instrucao_cobranca->codigo_instrucao_cobranca = "4";
        $data->data->dado_boleto->instrucao_cobranca[] = $instrucao_cobranca;
        $data->data->dado_boleto->desconto_expresso = false;

        // Definindo cabeçalhos para a requisição
        $x_itau_correlationID = ClassGenerica::CreateUuid(1);
        $x_itau_flowID = ClassGenerica::CreateUuid(2);
        $certificadoPath = storage_path($this->parametros->certificado);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->parametros->url2,
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERT => $certificadoPath,
            CURLOPT_SSLCERTPASSWD => $this->parametros->senha,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'x-itau-apikey: ' . $this->parametros->client_id,
                'x-itau-correlationID: ' . $x_itau_correlationID,
                'x-itau-flowID: ' . $x_itau_flowID,
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->Token,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        // Atualizar os dados no banco de dados com base na resposta
    $responseData = json_decode($response);

        if ($responseData && isset($responseData->data->dado_boleto->dados_individuais_boleto[0])) {
            $boleto = $responseData->data->dado_boleto->dados_individuais_boleto[0];

            // Atualizar os dados no banco de dados
            ContasReceber::where('id', $this->titulos->id)->update([
                'nossonumero'   => $boleto->numero_nosso_numero,
                'codigobarras'  => $boleto->codigo_barras ?? null,
                'linhadigitavel' => $boleto->numero_linha_digitavel ?? null,
            ]);

            ControleMeuNumeros::where('id',$meunumero->id)->update([
              //  'ultimo_numero'   => $meunumero->numero,
                'status'  => 'uso',
                           ]);


            Log::info("Boleto cadastrado com sucesso!", [
                'id_titulo' => $this->titulos->id,
                'nosso_numero' => $boleto->numero_nosso_numero
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Boleto cadastrado com sucesso!',
                'data'    => [
                    'nosso_numero'   => $boleto->numero_nosso_numero,
                    'codigo_barras'  => $boleto->codigo_barras ?? null,
                    'linha_digitavel' => $boleto->numero_linha_digitavel ?? null,
                ]
            ]);
        } else {
            // Se houver erro, registrar logs para depuração
            Log::error("Erro ao cadastrar boleto", [
                'response' => $response
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Erro ao cadastrar o boleto. Verifique os logs para mais detalhes.',
                'response' => $responseData
            ], 400);
        }
    }
}
