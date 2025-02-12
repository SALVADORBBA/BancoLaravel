<?php

namespace App\Http\Controllers\Itau;

use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParametroBanco;

class CreateToken extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function create(Request $request)
    {



        $parametros = ParametroBanco::find($request->id);

        $x_itau_flowID = ClassGenerica::CreateUuid(2);
        $x_itau_correlationID = ClassGenerica::CreateUuid(1);


        $certificadoPath = storage_path($parametros->certificado);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $parametros->url1,
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERT => $certificadoPath,
            CURLOPT_SSLCERTPASSWD => $parametros->senha,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $parametros->client_id,
                'client_secret' => $parametros->client_secret,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'x-itau-flowID: ' . $x_itau_flowID,
                'x-itau-correlationID: ' . $x_itau_correlationID,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);
        return $response;
    }
}
