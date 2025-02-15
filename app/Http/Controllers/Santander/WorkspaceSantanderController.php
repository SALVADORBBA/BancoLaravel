<?php

namespace App\Http\Controllers\Santander;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;
use App\Models\ParametrosBancos;
use App\Models\WorkspaceSantander;

use Illuminate\Support\Facades\Log;

class WorkspaceSantanderController extends Controller
{
    public function create(Request $request)
    {


        $key = $request->input('key');
        $work_id = $request->input('work_id');
        $webhookURL = $request->input('webhookURL');
        $code = $request->input('code');

        $parametros_bancos_id = $request->input('parametros_bancos_id');
        if (!$key) {
            return response()->json(['error' => 'Parâmetro key é obrigatório'], 400);
        }

        $parametros = ParametroBanco::find($parametros_bancos_id);
        $Token = CreateTokenSTD::create($parametros_bancos_id);

        if (!$parametros) {
            return response()->json(['error' => 'Parâmetro não encontrado'], 404);
        }

        $certificadoPath = storage_path($parametros->certificado);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://trust-sandbox.api.santander.com.br/collection_bill_management/v2/workspaces',
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERT => $certificadoPath,
            CURLOPT_SSLCERTPASSWD => $parametros->senha,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "type" => "BILLING",
                "covenants" => [["code" =>    $code]],
                "description" => "DeveloperAPI",
                "bankSlipBillingWebhookActive" => true,
                "pixBillingWebhookActive" => true,
                "webhookURL" =>   $webhookURL
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Application-Key: ' . $parametros->client_id,
                'Content-Type: application/json',
                'Authorization: Bearer ' . $Token
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            return response()->json(['error' => 'Falha na comunicação com o Santander'], 500);
        }

  $response = json_decode($response);

        if (!isset($response->id)) {
            return response()->json(['error' => 'Resposta inválida do Santander', 'data' => $response], 500);
        }
        $workspace = new WorkspaceSantander();
        $workspace->status = 'active';
        $workspace->parametros_bancos_id = $parametros_bancos_id;
        $workspace->type = $response->type;
        $workspace->description = $response->description;
      $workspace->covenant_code = $response->covenants[0]->code;
   $workspace->bank_slip_billing_webhook_active = $response->bankSlipBillingWebhookActive  ;
      $workspace->pix_billing_webhook_active = $response->pixBillingWebhookActive;
        $workspace->id_remoto = $response->id;
     $workspace->webhookurl = $response->webhookURL;
        $workspace->save();
        
        return response()->json(['message' => 'Workspace criado com sucesso', 'data' => $workspace], 201);
    }
}
