<?php

namespace App\Http\Controllers\Santander;

use App\Http\Controllers\Controller;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;

class WorkspaceBusca extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {




        $parametros_bancos_id = $request->input('parametros_bancos_id');


        $parametros = ParametroBanco::find($parametros_bancos_id);
        $Token = CreateTokenSTD::create($parametros_bancos_id);
        if ($request->tipo == true) {
            return response()->json(['codigo' => 200, 'mensagem' => 'success', 'validade' => $parametros->data_token, 'token' => $Token, 'client_id' => $parametros->client_id], 200);
        }
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
            CURLOPT_CUSTOMREQUEST => 'GET',
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

        return    $response = json_decode($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
 

        $parametros_bancos_id = $request->input('parametros_bancos_id');

        $parametros = ParametroBanco::find($parametros_bancos_id);
        $Token = CreateTokenSTD::create($parametros_bancos_id);
        if ($request->tipo == true) {
            return response()->json(['codigo' => 200, 'mensagem' => 'success', 'validade' => $parametros->data_token, 'token' => $Token, 'client_id' => $parametros->client_id], 200);
        }
        if (!$parametros) {
            return response()->json(['error' => 'Parâmetro não encontrado'], 404);
        }

        $certificadoPath = storage_path($parametros->certificado);

 
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://trust-sandbox.api.santander.com.br/collection_bill_management/v2/workspaces/'.$request->id_workspace,
          CURLOPT_SSLCERTTYPE => 'P12',
          CURLOPT_SSLCERT => $certificadoPath,
          CURLOPT_SSLCERTPASSWD => $parametros->senha,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'X-Application-Key: ' . $parametros->client_id,
            'Content-Type: application/json',
            'Authorization: Bearer ' . $Token
        
        ),
        ));
        
       
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            return response()->json(['error' => 'Falha na comunicação com o Santander'], 500);
        }

        return    $response = json_decode($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
    
        $parametros_bancos_id = $request->input('parametros_bancos_id');

        $parametros = ParametroBanco::find($parametros_bancos_id);
        $Token = CreateTokenSTD::create($parametros_bancos_id);
        if ($request->tipo == true) {
            return response()->json(['codigo' => 200, 'mensagem' => 'success', 'validade' => $parametros->data_token, 'token' => $Token, 'client_id' => $parametros->client_id], 200);
        }
        if (!$parametros) {
            return response()->json(['error' => 'Parâmetro não encontrado'], 404);
        }
        $certificadoPath = storage_path($parametros->certificado);
 
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://trust-sandbox.api.santander.com.br/collection_bill_management/v2/workspaces/'.$request->id_workspace,
          CURLOPT_SSLCERTTYPE => 'P12',
          CURLOPT_SSLCERT => $certificadoPath,
          CURLOPT_SSLCERTPASSWD => $parametros->senha,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'DELETE',
          CURLOPT_HTTPHEADER => array(
            'X-Application-Key: ' . $parametros->client_id,
            'Content-Type: application/json',
            'Authorization: Bearer ' . $Token
        
        ),
        ));
        
       
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            return response()->json(['error' => 'Falha na comunicação com o Santander'], 500);
        }

        return    $response = json_decode($response);
    }
}
