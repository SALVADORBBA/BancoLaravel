<?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RetornoWebhook; // Certifique-se de importar o model correto

class WebhookBrasil extends Controller
{
    /**
     * Armazena os dados do webhook do Banco do Brasil.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Tenta obter o conteúdo como JSON
        $data = $request->json()->all();

        if (!is_array($data)) {
            return response()->json(['error' => 'Formato de dados inválido'], 400);
        }

        foreach ($data as $itens) {
            $objeto = new RetornoWebhook();
            $objeto->webhook_id = $itens['id'] ?? null;
            $objeto->dataregistro = $itens['dataRegistro'] ?? null;
            $objeto->datavencimento = $itens['dataVencimento'] ?? null;
            $objeto->valororiginal = $itens['valorOriginal'] ?? null;
            $objeto->valorpagosacado = $itens['valorPagoSacado'] ?? null;
            $objeto->numeroconvenio = $itens['numeroConvenio'] ?? null;
            $objeto->numerooperacao = $itens['numeroOperacao'] ?? null;
            $objeto->carteiraconvenio = $itens['carteiraConvenio'] ?? null;
            $objeto->variacaocarteiraconvenio = $itens['variacaoCarteiraConvenio'] ?? null;
            $objeto->codigoestadobaixaoperacional = $itens['codigoEstadoBaixaOperacional'] ?? null;
            $objeto->dataliquidacao = $itens['dataLiquidacao'] ?? null;
            $objeto->instituicaoliquidacao = $itens['instituicaoLiquidacao'] ?? null;
            $objeto->canalliquidacao = $itens['canalLiquidacao'] ?? null;
            $objeto->codigomodalidadeboleto = $itens['codigoModalidadeBoleto'] ?? null;
            $objeto->tipopessoaportador = $itens['tipoPessoaPortador'] ?? null;
            $objeto->identidadeportador = $itens['identidadePortador'] ?? null;
            $objeto->nomeportador = $itens['nomePortador'] ?? null;
            $objeto->formapagamento = $itens['formaPagamento'] ?? null;
            $objeto->json_original = json_encode($itens); // opcional: guardar o JSON original
            $objeto->save();
        }

        return response()->json(['message' => 'Dados recebidos com sucesso'], 201);
    }
}
