<?php

namespace App\Http\Controllers\Santander;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Models\Beneficiario;
use App\Models\ControleMeuNumeros;
use App\Models\WorkspaceSantander;
use Exception;
use stdClass;

class CreateBoletoSTD extends Controller
{
    // Usando Dependency Injection para instanciar o CreateToken
    private $titulos;
    private $parametros;
    private $pessoas;
    private $Token;
    private $tipo;
    private $Workspace;
    private $beneficiario;

    public function __construct(Request $request)
    {
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->pessoas = $this->titulos->pessoa;
        $this->Token = CreateTokenSTD::create($this->titulos->parametros_bancos_id);
        $this->tipo = $request->tipo;
        $this->beneficiario = Beneficiario::find($request->beneficiario_id);
        $this->Workspace = WorkspaceSantander::find($request->workspace_id);
    }




    public function create()
    {


        $meunumero =  ControleMeuNumeroController::create($this->parametros->id);


        try {
            $curl = curl_init();

            // Create stdClass object
            $data = new stdClass();
            $data->workspace_id =  $this->Workspace->id_remoto;
            $data->environment = $this->parametros->ambiente;;
            //No momento do registro de boletos em nosso ambiente Produtivo (utilizando os Endpoints de Produção), você deve
            // indicar se este registro deve ser realizado em TESTE ou PRODUCAO.
            $data->nsuCode = $meunumero->numero;
            $data->nsuDate =  $this->titulos->data_vencimento;
            $data->covenantCode = $this->beneficiario->covenantCode;
            $data->bankNumber = $this->beneficiario->bankNumber;
            $data->clientNumber = $this->beneficiario->clientNumber;
            $data->dueDate =  $this->titulos->data_vencimento;
            $data->issueDate =  $this->titulos->data_vencimento;
            $data->participantCode = "Gerado via API";
            $data->nominalValue =  $this->titulos->valor;

            // Payer information
            $data->payer = new stdClass();
            $data->payer->name = $this->pessoas->nome;
            $data->payer->documentType = strlen($this->pessoas->documento) === 14 ? "CNPJ" : "CPF";
            $data->payer->documentNumber = $this->pessoas->documento;
            $data->payer->address =  $this->pessoas->rua;
            $data->payer->neighborhood =  $this->pessoas->bairro;
            $data->payer->city = $this->pessoas->cidade;
            $data->payer->state =  $this->pessoas->uf;
            $data->payer->zipCode = $this->pessoas->cep;


            // Beneficiary information
            $data->beneficiary = new stdClass();
            $data->beneficiary->name = $this->beneficiario->nome;

            $data->beneficiary->documentType = strlen($this->beneficiario->documento) === 14 ? "CNPJ" : "CPF";


            $data->beneficiary->documentNumber =  $this->beneficiario->documento;

            $data->documentKind = "DUPLICATA_MERCANTIL";
            $data->deductionValue = $this->titulos->valor;
            $data->paymentType = "REGISTRO";
            $data->writeOffQuantityDays = "3000";
            $data->messages = [$this->parametros->mensagem_1 ?? null,  $this->parametros->mensagem_2 ?? null,   $this->parametros->mensagem_3 ?? null];
            $certificadoPath = storage_path($this->parametros->certificado);
            // Convert to JSON
            $jsonData = json_encode($data);




            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://trust-sandbox.api.santander.com.br/collection_bill_management/v2/workspaces/579d0608-188d-4f32-b301-da4c388a673b/bank_slips',
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
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HTTPHEADER => array(
                    'X-Application-Key: GxmZtTPj5EzRWHgG0gehNpTRt5dq7Ijv',
                    'Authorization: Bearer ' . $this->Token,
                    'Content-Type: application/json',
                      ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response);
            ContasReceber::where('id', $this->titulos->id)->update([
                'nossonumero'   => $data->nsuCode,
                'codigobarras'  => $response->barcode ?? null,
                'linhadigitavel' => $response->digitableLine ?? null,
                'workspaces_id' => $this->Workspace->id ?? null,
              'covenantCode' =>  $this->beneficiario->covenantCode,
              'beneficiario_id' =>    $this->beneficiario->id,
              
                'qrCodePix' => $response->qrCodePix ?? null,
                'qrCodeUrl' => $response->qrCodeUrl ?? null,

                'status'   =>3,
            ]);




            ControleMeuNumeros::where('id', $meunumero->id)->update([

                'status'  => 'uso',
            ]);


            return response()->json([
                'status'  => 'success',
                'message' => 'Boleto cadastrado com sucesso!',
                'data'    => [

                    'codigo_barras'  => $response,

                ]
            ]);

            // Feche a transação (a conexão será fechada automaticamente)
        } catch (Exception $e) {
        }
    }
}
