<?php

namespace App\Services\CobrancaInter;

use App\Models\CobrancaInter\CobrancaInter;
use Exception;

class CobrancaInterService
{
    private $parametros;
    private $ambiente;
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->ambiente = config('cobranca_inter.ambiente');
        $this->baseUrl = $this->ambiente === 'producao'
            ? config('cobranca_inter.url_producao')
            : config('cobranca_inter.url_sandbox');

        $this->validarCredenciais();
        $this->validarCertificados();
    }

    private function validarCredenciais()
    {
        if ($this->ambiente === 'producao') {
            if (!config('cobranca_inter.client_id_producao') || !config('cobranca_inter.client_secret_producao')) {
                throw new Exception('Credenciais de produção não configuradas');
            }
        } else {
            if (!config('cobranca_inter.client_id') || !config('cobranca_inter.client_secret')) {
                throw new Exception('Credenciais de sandbox não configuradas');
            }
        }
    }

    private function validarCertificados()
    {
        $certificadoPath = $this->ambiente === 'producao'
            ? config('cobranca_inter.certificado_crt_producao')
            : config('cobranca_inter.certificado');

        $chavePrivadaPath = $this->ambiente === 'producao'
            ? config('cobranca_inter.certificado_key_producao')
            : config('cobranca_inter.certificado_publico');

        if (!file_exists($certificadoPath) || !file_exists($chavePrivadaPath)) {
            throw new Exception('Certificados não encontrados');
        }
    }

    private function getToken()
    {
        if ($this->token && isset($this->token['expires_at']) && $this->token['expires_at'] > time()) {
            return $this->token['access_token'];
        }

        $certificadoPath = $this->ambiente === 'producao'
            ? config('cobranca_inter.certificado_crt_producao')
            : config('cobranca_inter.certificado');

        $chavePrivadaPath = $this->ambiente === 'producao'
            ? config('cobranca_inter.certificado_key_producao')
            : config('cobranca_inter.certificado_publico');

        $clientId = $this->ambiente === 'producao'
            ? config('cobranca_inter.client_id_producao')
            : config('cobranca_inter.client_id');

        $clientSecret = $this->ambiente === 'producao'
            ? config('cobranca_inter.client_secret_producao')
            : config('cobranca_inter.client_secret');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/oauth/v2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
                'scope' => 'boleto-cobranca.read boleto-cobranca.write'
            ]),
            CURLOPT_SSLCERT => $certificadoPath,
            CURLOPT_SSLKEY => $chavePrivadaPath,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception('Erro ao obter token: ' . $err);
        }

        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            throw new Exception('Token não recebido: ' . $response);
        }

        $this->token = [
            'access_token' => $tokenData['access_token'],
            'expires_at' => time() + ($tokenData['expires_in'] ?? 3600)
        ];

        return $this->token['access_token'];
    }

    public function consultarCobranca($nossoNumero)
    {
        try {
            $token = $this->getToken();
            $certificadoPath = $this->ambiente === 'producao'
                ? config('cobranca_inter.certificado_crt_producao')
                : config('cobranca_inter.certificado');

            $chavePrivadaPath = $this->ambiente === 'producao'
                ? config('cobranca_inter.certificado_key_producao')
                : config('cobranca_inter.certificado_publico');

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/cobranca/v3/cobrancas/' . $nossoNumero,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSLCERT => $certificadoPath,
                CURLOPT_SSLKEY => $chavePrivadaPath,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Erro ao consultar cobrança: ' . $err);
            }

            return json_decode($response, true);
        } catch (Exception $e) {
            throw new Exception('Erro ao consultar cobrança: ' . $e->getMessage());
        }
    }

    public function consultarAtualizacaoCobranca($dataInicial, $dataFinal)
    {
        try {
            $token = $this->getToken();
            $certificadoPath = $this->ambiente === 'producao'
                ? config('cobranca_inter.certificado_crt_producao')
                : config('cobranca_inter.certificado');

            $chavePrivadaPath = $this->ambiente === 'producao'
                ? config('cobranca_inter.certificado_key_producao')
                : config('cobranca_inter.certificado_publico');

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/cobranca/v3/cobrancas/atualizacoes?dataInicial=' . $dataInicial . '&dataFinal=' . $dataFinal,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSLCERT => $certificadoPath,
                CURLOPT_SSLKEY => $chavePrivadaPath,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Erro ao consultar atualizações de cobrança: ' . $err);
            }

            return json_decode($response, true);
        } catch (Exception $e) {
            throw new Exception('Erro ao consultar atualizações de cobrança: ' . $e->getMessage());
        }
    }
}