<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function capture(Request $request)
    {
        // Captura o método HTTP da requisição
        $method = $request->method();
        
        // Captura o Content-Type da requisição
        $contentType = $request->header('Content-Type');

        // Captura os dados do corpo da requisição para POST, PUT e PATCH
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $data = $contentType === 'application/json' ? $request->json()->all() : $request->all();
        } 
        // Para GET, captura os parâmetros da URL e, se houver, dados no corpo
        else {
            $data = array_merge($request->query(), $request->json()->all());
        }

        // Converte os dados para JSON para melhor legibilidade no log
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        
        // Grava os dados no log incluindo o método HTTP recebido e Content-Type
        Log::info("[Webhook Recebido]", [
            "method" => $method,
            "content_type" => $contentType,
            "data" => $jsonData
        ]);
        
        // Retorna uma resposta de sucesso
        return response()->json([
            "message" => "Webhook recebido com sucesso.",
            "method" => $method,
            "content_type" => $contentType,
            "data" => $data
        ], 200);
    }
}
