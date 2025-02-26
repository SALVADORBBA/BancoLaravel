<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Models\Beneficiario;
use App\Models\ControleMeuNumeros;
use Exception;
use stdClass;

class CreateBoletoSC extends Controller
{
    private $titulos;
    private $parametros;
    private $Token;
    private $tipo;
    private $meunumero;
    private $beneficiario;
    public function __construct(Request $request)
    {
        // Inicializando as variáveis necessárias
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->Token = CreateTokensSC::create($this->parametros);
        $this->tipo = $request->tipo;
        $this->meunumero = ControleMeuNumeroController::create($this->parametros->id);

        $this->beneficiario = Beneficiario::find($this->titulos->beneficiario_id);

       // $this->beneficiario = Beneficiario::where('id', $this->titulos->beneficiario_id)->pluck('id','nome',)->first();
    }

    public function create()
    {



        // Dependendo do tipo, retorna o Token ou gera o boleto
        if ($this->tipo == 1) {
            return $this->Token;
        } else {

            return   $this->enviarBoletoParaBanco();
        }
    }

    private function gerarBoleto($ultimoNumero)
    {
        $boletoObj = new stdClass();

        // Definindo os dados do título
        $boletoObj->codigoBeneficiario = $this->parametros->ambiente == 1 ? $this->parametros->numerocontrato : 12345;
        $boletoObj->dataVencimento = $this->titulos->data_vencimento;
        $boletoObj->especieDocumento = 'DUPLICATA_MERCANTIL_INDICACAO';
        $boletoObj->valor = number_format($this->titulos->valor, 2, '.', '');

        // Dados do pagador (caso esteja presente)
        if ($this->titulos->pessoa) {
            $boletoObj->pagador = $this->preencherDadosPagador($this->titulos->pessoa);
        }

        // Definindo o tipo de cobrança e nosso número
        $boletoObj->tipoCobranca = "HIBRIDO";
        $boletoObj->nossoNumero = null;
        $boletoObj->seuNumero = $ultimoNumero->numero ?? null;

        // Definindo juros, multa e informativos
        $boletoObj = $this->adicionarJurosMultaInformativos($boletoObj);

        return $boletoObj;
    }

    private function preencherDadosPagador($pessoa)
    {
        $pagador = new stdClass();
        $pagador->cep = $pessoa->cep;
        $pagador->cidade = $pessoa->cidade;
        $pagador->documento = ClassGenerica::cleandoc($pessoa->documento);
        $pagador->nome = ClassGenerica::limitarTexto($pessoa->nome, 40);
        $pagador->tipoPessoa = ClassGenerica::tipodoc($pessoa->cpf_cnpj);
        $pagador->endereco = ClassGenerica::limitarTexto($pessoa->rua, 34) . ' ' . ClassGenerica::limitarTexto($pessoa->numero, 5);
        $pagador->uf = $pessoa->uf;

        return $pagador;
    }

    private function adicionarJurosMultaInformativos($boletoObj)
    {
        if ($this->parametros->tipojurosmora == 0) {
            unset($boletoObj->tipoJuros, $boletoObj->juros);
        } else {
            $boletoObj->tipoJuros = $this->parametros->tipojurosmora == 1 ? 'PERCENTUAL' : 'VALOR';
            $boletoObj->juros = number_format($this->parametros->valorjurosmora, 2, '.', '');
        }

        if ($this->parametros->tipomulta == 1) {
            $boletoObj->multa = $this->parametros->valormulta;
        } else {
            unset($boletoObj->multa);
        }

        $boletoObj->informativos = [
            ClassGenerica::limitarTexto($this->parametros->mensagem_1, 80) ?? "",
            ClassGenerica::limitarTexto($this->parametros->mensagem_2, 80) ?? "",
            ClassGenerica::limitarTexto($this->parametros->mensagem_3, 80) ?? "---------------------",
            ClassGenerica::limitarTexto($this->parametros->mensagem_4, 69) . ' ' . ClassGenerica::limitarTexto($this->titulos->numero_documento, 10) ?? "",
        ];

        return $boletoObj;
    }

    private function enviarBoletoParaBanco()
    {
        try {
            $url = $this->parametros->ambiente == 1 ? $this->parametros->url_boleto_producao : $this->parametros->url2;
            $xapikey = $this->parametros->ambiente == 1 ? $this->parametros->client_id_producao : $this->parametros->client_id;
            $posto = $this->parametros->ambiente == 1 ? $this->parametros->posto : "03";
            $cooperativa = $this->parametros->ambiente == 1 ? $this->parametros->cooperativa : '6789';

            // Monta o objeto do boleto
            $boletoObj = $this->gerarBoleto($this->meunumero);

            // Configuração do cURL
            $method = 'POST';
            $headers = [
                'x-api-key: ' . $xapikey,
                'Authorization: Bearer ' . $this->Token,
                'Content-Type: application/json',
                'cooperativa: ' . $cooperativa,
                'posto: ' . $posto,
            ];

            $payload = json_encode($boletoObj);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($curl);
            if ($response === false) {
                $error = curl_error($curl);
                curl_close($curl);
                return "Erro na solicitação cURL: $error";
            }

            curl_close($curl);

            // Decodifica a resposta do banco
            $response = json_decode($response);

            // Atualiza os dados no banco

            ContasReceber::where('id', $this->titulos->id)->update([
           'nossonumero'   => $response->nossoNumero,
           'seunumero'   =>   $this->meunumero->numero,
                'codigobarras'  => $response->codigoBarras,
                'linhadigitavel' => $response->linhaDigitavel,
                'beneficiario_id' =>  $this->beneficiario->id,
                'qrCodePix' => $response->qrCode,
                'qrCodeUrl' => $response->qrCode,
                'status'   => 3,
            ]);



            ControleMeuNumeros::where('id', $this->meunumero->id)->update([
                'status'  => 'uso',
            ]);

            return response()->json([
                'Banco' =>$this->parametros->apelido,
                'message' => 'Boleto gerado com sucesso!',
                'Boleto' =>$this->meunumero->numero,
                'Valor' =>$this->titulos->valor,
                'Vencimento' =>$this->titulos->data_vencimento,
              'Beneficiario' =>  $this->beneficiario->nome,
             'Cliete' => $this->titulos->pessoa->nome,
             'Docuemnto' => $this->titulos->pessoa->documento,
                'data' =>  $response
            ], 201);
            
        } catch (Exception $e) {
            return 'Erro ao enviar o boleto: ' . $e->getMessage();
        }
    }
}
