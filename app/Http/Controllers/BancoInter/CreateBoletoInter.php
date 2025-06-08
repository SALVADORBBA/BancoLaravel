<?php

namespace App\Http\Controllers\BancoInter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Support\Facades\Http;
 
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use MasterClass;

class CreateBoletoInter extends Controller
{
    protected ContasReceber $cobranca;
    protected ParametroBanco $parametros;
    protected string $ambiente;
    protected string $baseUrl;

    protected function loadData(int $id): void
    {
        try {
            $this->cobranca = ContasReceber::with('pessoa')->findOrFail($id);
            $this->parametros = ParametroBanco::findOrFail(2);

            $this->ambiente = $this->parametros->ambiente;
            $this->baseUrl = $this->ambiente === 'sandbox'
                ? $this->parametros->url2
                : $this->parametros->url_producao_cobranca;
        } catch (Exception $e) {
            Log::error('Erro ao carregar dados do boleto: ' . $e->getMessage());
            throw new Exception('Erro ao carregar dados necessários para gerar o boleto');
        }
    }

    protected function resolveCertPath(string $path): string
    {
        if (empty($path)) {
            throw new Exception('Caminho do certificado não informado');
        }

        // Caminho absoluto fornecido
        if (file_exists($path)) {
            return $path;
        }

        // Tenta encontrar no diretório de certificados
        $possiblePaths = [
            storage_path("app/public/certificado/{$path}"),
            storage_path("app/public/certificado/2/{$path}"),
            storage_path("app/{$path}"),
            public_path("certificados/{$path}"),
            base_path("certificados/{$path}")
        ];

        foreach ($possiblePaths as $possiblePath) {
            if (file_exists($possiblePath)) {
                return $possiblePath;
            }
        }

        // Log para debug
        Log::error('Certificado não encontrado', [
            'caminho_original' => $path,
            'caminhos_tentados' => $possiblePaths
        ]);

        throw new Exception('Certificado não encontrado. Verifique se o arquivo existe em: ' . storage_path('app/public/certificado/2'));
    }

    protected function getCertificateAndKey(): array
    {
        $cert = $this->resolveCertPath(
            $this->ambiente === 'sandbox'
                ? $this->parametros->certificado
                : $this->parametros->certificado_crt_producao
        );

        $key = $this->resolveCertPath(
            $this->ambiente === 'sandbox'
                ? $this->parametros->certificadoPublico
                : $this->parametros->certificado_key_producao
        );

        return [$cert, $key];
    }

    public function getToken(Request $request): object
    {
        try {
            $this->loadData($request->id);

            // Verifica se já existe um token válido
            $margemSegundos = 3600;
            $dataTokenTimestamp = isset($this->parametros->data_token) 
                ? strtotime($this->parametros->data_token) 
                : 0;

            if (!empty($this->parametros->token) && 
                $dataTokenTimestamp > (time() - $margemSegundos)) {
                Log::info('Utilizando token existente do banco de dados');
                return (object)[
                    'codigo' => 200,
                    'data_token' => $this->parametros->data_token,
                    'token' => $this->parametros->token,
                    'origem' => 'Banco de Dados',
                    'expires_in' => $this->parametros->expires_in,
                ];
            }

            // Prepara a URL baseada no ambiente
            $url = $this->ambiente === 'sandbox'
                ? $this->parametros->url1
                : $this->parametros->url_producao_token;

            Log::info('Iniciando requisição de novo token', [
                'ambiente' => $this->ambiente,
                'url' => $url
            ]);

            // Verifica e carrega os certificados
            $cert = $this->resolveCertPath(
                $this->ambiente === 'sandbox'
                    ? $this->parametros->certificado
                    : $this->parametros->certificado_crt_producao
            );

            $key = $this->resolveCertPath(
                $this->ambiente === 'sandbox'
                    ? $this->parametros->certificadoPublico
                    : $this->parametros->certificado_key_producao
            );

            // Prepara os dados da requisição
            $postData = [
                'client_id' => $this->ambiente === 'sandbox' 
                    ? $this->parametros->client_id 
                    : $this->parametros->client_id_producao,
                'client_secret' => $this->ambiente === 'sandbox' 
                    ? $this->parametros->client_secret 
                    : $this->parametros->client_secret_producao,
                'grant_type' => 'client_credentials',
                'scope' => 'boleto-cobranca.read boleto-cobranca.write',
            ];

            // Verifica se as credenciais estão presentes
            if (empty($postData['client_id']) || empty($postData['client_secret'])) {
                throw new Exception('Credenciais do Banco Inter não configuradas');
            }

            Log::info('Configurando requisição CURL para obtenção do token');

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLCERT => $cert,
                CURLOPT_SSLKEY => $key,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_VERBOSE => true
            ]);

            // Captura erros do CURL
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Log do resultado da requisição
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            Log::info('Detalhes da requisição CURL', [
                'verbose_log' => $verboseLog,
                'http_code' => $httpCode
            ]);

            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                Log::error('Erro na requisição CURL', ['error' => $error]);
                throw new Exception("Erro na requisição CURL: {$error}");
            }

            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('Erro na resposta da API', [
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                throw new Exception("Erro ao obter token (HTTP {$httpCode}): {$response}");
            }

            $data = json_decode($response);
            if (!isset($data->access_token)) {
                Log::error('Token não encontrado na resposta', ['response' => $response]);
                throw new Exception('Token não recebido da API');
            }

            Log::info('Token obtido com sucesso');

            // Atualiza o token no banco de dados
            $this->parametros->update([
                'token' => $data->access_token,
                'data_token' => now()->addSeconds($data->expires_in),
                'expires_in' => $data->expires_in,
            ]);

            return (object)[
                'codigo' => 201,
                'data_token' => $this->parametros->data_token,
                'token' => $data->access_token,
                'origem' => 'API Banco do Inter',
                'expires_in' => $data->expires_in,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao obter token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Falha ao obter token de autenticação: ' . $e->getMessage());
        }
    }

    protected function makeTokenRequest(string $url, string $cert, string $key): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSLCERT => $cert,
            CURLOPT_SSLKEY => $key,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->ambiente === 'sandbox' 
                    ? $this->parametros->client_id 
                    : $this->parametros->client_id_producao,
                'client_secret' => $this->ambiente === 'sandbox' 
                    ? $this->parametros->client_secret 
                    : $this->parametros->client_secret_producao,
                'grant_type' => 'client_credentials',
                'scope' => 'boleto-cobranca.read boleto-cobranca.write',
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new Exception("Erro ao obter token (HTTP $httpCode): $response");
        }

        return $response;
    }

    protected function processTokenResponse(string $response): object
    {
        $data = json_decode($response);
        if (!isset($data->access_token)) {
            throw new Exception('Token não recebido da API');
        }

        $this->parametros->update([
            'token' => $data->access_token,
            'data_token' => now()->addSeconds($data->expires_in),
            'expires_in' => $data->expires_in,
        ]);

        return (object)[
            'codigo' => 201,
            'data_token' => $this->parametros->data_token,
            'token' => $data->access_token,
            'origem' => 'API Banco do Inter',
            'expires_in' => $data->expires_in,
        ];
    }

    public function generate(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $this->loadData($id);
            $token = $this->getToken($request);

            $boletoData = $this->prepareBoletoData();
            $response = $this->sendBoletoRequest($boletoData, $token->token);

            // Atualiza a cobrança com os dados do boleto
            $this->cobranca->nossoNumero = $response->nossoNumero ?? null;
            $this->cobranca->linhaDigitavel = $response->linhaDigitavel ?? null;
            $this->cobranca->codigoBarras = $response->codigoBarras ?? null;
            $this->cobranca->save();

            // Retorna a resposta da API junto com a cobrança atualizada
            return response()->json([
                'boleto' => $response,
                'cobranca' => $this->cobranca
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao gerar boleto: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function prepareBoletoData(): array
    {
        $tipoPessoa = strlen($this->cobranca->pessoa->documento) <= 11 ? 'FISICA' : 'JURIDICA';
        $numeroWhatsapp = preg_replace('/\D/', '', $this->cobranca->pessoa->whatsapp ?? '');
        $codigoSolicitacao = substr(uniqid() . str_pad($this->cobranca->id, 6, '0', STR_PAD_LEFT), 0, 20);

        return [
            'seuNumero' => substr(now()->format('dmY') . $this->parametros->id . $this->cobranca->id, 0, 14),
            'codigoSolicitacao' => $codigoSolicitacao,
            'valorNominal' => (float)$this->cobranca->valor,
            'valorAbatimento' => 0,
            'dataVencimento' => $this->cobranca->data_vencimento,
            'numDiasAgenda' => 30,
            'atualizarPagador' => false,
            'pagador' => [
                'email' => $this->cobranca->pessoa->email ?? '',
                'ddd' => substr($numeroWhatsapp, 0, 2),
                'telefone' => substr($numeroWhatsapp, 2),
                'numero' => $this->cobranca->pessoa->numero ?? '',
                'complemento' => substr($this->cobranca->pessoa->complemento ?? '', 0, 29),
                'cpfCnpj' =>  ($this->cobranca->pessoa->documento),
                'tipoPessoa' => $tipoPessoa,
                'nome' => $this->cobranca->pessoa->nome,
                'endereco' => $this->cobranca->pessoa->rua,
                'bairro' => $this->cobranca->pessoa->bairro ?? '',
                'cidade' => $this->cobranca->pessoa->cidade,
                'uf' => $this->cobranca->pessoa->uf,
                'cep' => ($this->cobranca->pessoa->cep),
            ],
            'mensagem' => [
                'linha1' => 'Receber até o vencimento',
                'linha2' => 'Vencimento'
            ]
        ];
    }

    protected function sendBoletoRequest(array $data, string $token): object
    {
        $url = $this->baseUrl . '/cobranca/v3/cobrancas';
        [$cert, $key] = $this->getCertificateAndKey();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSLCERT => $cert,
            CURLOPT_SSLKEY => $key,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new Exception("Erro ao gerar boleto (HTTP $httpCode): $response");
        }

        // Atualiza o codigoSolicitacao no modelo
        $this->cobranca->codigoSolicitacao = $data['codigoSolicitacao'];
        $this->cobranca->save();

        return $this->cobranca;
    }

    public function getPdf(Request $request, string $nossoNumero): JsonResponse
    {
        try {
            $this->loadData($request->id);
            $token = $this->getToken($request);
            
            $url = $this->baseUrl . "/cobranca/v3/cobrancas/{$nossoNumero}/pdf";
            [$cert, $key] = $this->getCertificateAndKey();

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLCERT => $cert,
                CURLOPT_SSLKEY => $key,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token->token
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                throw new Exception("Erro ao obter PDF do boleto (HTTP $httpCode)");
            }

            return response()->json(['pdf' => base64_encode($response)]);
        } catch (Exception $e) {
            Log::error('Erro ao obter PDF do boleto: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
