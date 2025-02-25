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

class SolicitacaoBaixaBoleto extends Controller
{
    // Usando Dependency Injection para instanciar o CreateToken
    private $titulos;
    private $parametros;
    private $pessoas;
    private $Token;
    private $tipo;
    private $Workspace;
    private $beneficiario;
    private $operation;
    
    public function __construct(Request $request)
    {
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->Token = CreateTokenSTD::create($this->titulos->parametros_bancos_id);
        $this->Workspace = WorkspaceSantander::find($this->titulos->workspaces_id);
        $this->operation=$request->operation;
        $this->beneficiario = Beneficiario::find($this->titulos->beneficiario_id);
    }





    public function store()
    {
        $certificadoPath = storage_path($this->parametros->certificado);
 

 

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://trust-sandbox.api.santander.com.br/collection_bill_management/v2/workspaces/'. $this->Workspace->id_remoto.'/bank_slips',
          CURLOPT_SSLCERTTYPE => 'P12',
          CURLOPT_SSLCERT => $certificadoPath,
          CURLOPT_SSLCERTPASSWD => $this->parametros->senha,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
            //PROTESTAR
            // CANCELAR_PROTESTO  
            // BAIXAR BAIXAR
          CURLOPT_POSTFIELDS =>'{
            "covenantCode": "'. $this->Workspace->covenant_code.'",
            "bankNumber": "'. $this->beneficiario->bankNumber.'",
            "operation": "'.$this->operation.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'X-Application-Key:  '.$this->parametros->client_id,
            'Authorization: Bearer '.$this->Token,
            'Content-Type: application/json',
           ),
        ));
  
        
        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        ContasReceber::where('id', $this->titulos->id)->update([
            'status'   => 10,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Solicitação de baixa cadastrado com sucesso!',
            'data'    =>  $response,


        ]);
    }
}
