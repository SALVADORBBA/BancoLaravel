 
 <?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\Controller;

use App\Models\CobrancaTitulo;
use App\Models\Pessoa;
use App\Models\ParametrosBancos;
use App\Services\GetTokenBancoBrasil;
use App\Services\BancoBrasilPrintAPI;
use Illuminate\Support\Facades\DB;
use Exception;
use stdClass;

class BancoBrasilBoletoService
{
    protected $titulo;
    protected $cliente;
    protected $parametros;
    protected $beneficiario;
    protected $token;
    protected $chave;

    public function __construct($chave)
    {
        $this->chave = $chave;
    }

    public function gerarBoleto()
    {
        return DB::transaction(function () {
            $this->carregarDados();

            $payload = $this->montarPayload();
            $json = json_encode($payload);

            $url = $this->parametros->url2 . '?gw-dev-app-key=' . $this->parametros->gw_dev_app_key;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ]
            ]);

            $response = curl_exec($curl);
            if ($response === false) {
                throw new Exception('Erro cURL: ' . curl_error($curl));
            }
            curl_close($curl);

            $res = json_decode($response);
            $this->salvarResposta($res, $payload);

            return $this->titulo->fresh();
        });
    }

    protected function carregarDados()
    {
        $this->titulo = CobrancaTitulo::findOrFail($this->chave);

        if (empty($this->titulo->data_vencimento)) {
            throw new Exception("Boleto sem data de vencimento.");
        }

        if ($this->titulo->valor <= 0) {
            throw new Exception("Valor inválido para o título.");
        }

        $this->cliente = Pessoa::findOrFail($this->titulo->cliente_id);
        $this->parametros = ParametrosBancos::with('beneficiario')->findOrFail($this->titulo->parametros_bancos_id);
        $this->beneficiario = $this->parametros->beneficiario;

        if (!$this->beneficiario) {
            throw new Exception("Beneficiário não encontrado nos parâmetros.");
        }

        $this->token = (new GetTokenBancoBrasil($this->parametros->id))->create()->token;
    }

    protected function salvarResposta($res, $payload)
    {
        $this->titulo->resposta = $res->erros[0]->mensagem ?? json_encode($res);
        $this->titulo->save();

        if (isset($res->statusCode) && $res->statusCode == 401) {
            $this->parametros->data_token = null;
            $this->parametros->save();

            // Retry com novo token
            $reprocessar = new self($this->chave);
            return $reprocessar->gerarBoleto();
        }

        if (!empty($res->numero)) {
            $this->titulo->update([
                'status' => 3,
                'seunumero' => $res->numero,
                'nossonumero' => $payload->numeroTituloBeneficiario,
                'url_bb' => $res->qrCode->url ?? null,
                'txid' => $res->qrCode->txId ?? null,
                'emv' => $res->qrCode->emv ?? null,
                'qrcode' => $res->qrCode->emv ?? null,
                'linhadigitavel' => $res->linhaDigitavel ?? null,
                'codigobarras' => $res->codigoBarraNumerico ?? null,
                'modelo' => 2
            ]);

            (new BancoBrasilPrintAPI($this->titulo->id))->create();
        } else {
            throw new Exception('Erro ao registrar boleto: ' . json_encode($res));
        }
    }

    protected function montarPayload()
    {
        $numeroAgregado = str_pad($this->beneficiario->id . $this->chave . $this->parametros->system_unit_id . $this->parametros->id, 9, '0', STR_PAD_LEFT);

        $dados = new stdClass();
        $dados->numeroConvenio = $this->parametros->numeroconvenio;
        $dados->dataVencimento = ClassGenerica::CVDataBB($this->titulo->data_vencimento);
        $dados->valorOriginal = $this->titulo->valor;
        $dados->numeroCarteira = $this->parametros->carteira;
        $dados->numeroVariacaoCarteira = $this->parametros->numerovariacaocarteira;
        $dados->dataEmissao = ClassGenerica::CVDataBB(now()->toDateString());
        $dados->numeroTituloBeneficiario = $numeroAgregado;
        $dados->codigoTipoTitulo = 2;
        $dados->descricaoTipoTitulo = $this->parametros->tipos_documentos;
        $dados->indicadorPermissaoRecebimentoParcial = "N";
        $dados->codigoAceite = $this->parametros->codigoaceite;
        $dados->indicadorAceiteTituloVencido = $this->parametros->indicadoraceitetitulovencido;
        $dados->numeroDiasLimiteRecebimento = $this->parametros->numerodiaslimiterecebimento;
        $dados->campoUtilizacaoBeneficiario = 0;
        $dados->numeroTituloCliente = "000{$this->parametros->numeroconvenio}1{$numeroAgregado}";
        $dados->indicadorPix = "S";

        if ($this->titulo->abatimento == 1) {
            $dados->valorAbatimento = $this->titulo->valorabatimento;
        }

        if ($this->titulo->numerodiasprotesto > 0) {
            $dados->quantidadeDiasProtesto = $this->titulo->numerodiasprotesto;
        }

        if ($this->titulo->numerodiasnegativacao > 0) {
            $dados->quantidadeDiasNegativacao = $this->titulo->numerodiasnegativacao;
            $dados->orgaoNegativador = $this->titulo->orgaonegativador;
        }

        $dados->mensagemBloquetoOcorrencia = ClassGenerica::limitarTexto($this->parametros->mens1, 50)
            . ClassGenerica::limitarTexto($this->parametros->mens2, 55)
            . 'CC' . $this->cliente->indetificado . ' Matricula Nº' . $this->titulo->matricula;

        $dados->pagador = (object)[
            'tipoInscricao' => strlen($this->cliente->documento) == 11 ? 1 : 2,
            'numeroInscricao' => $this->cliente->documento,
            'nome' => $this->cliente->nome,
            'endereco' => $this->cliente->endereco,
            'cep' => $this->cliente->cep,
            'cidade' => $this->cliente->cidade,
            'bairro' => $this->cliente->bairro,
            'uf' => $this->cliente->uf,
            'telefone' => $this->cliente->telefone
        ];

        $dados->beneficiarioFinal = (object)[
            'tipoInscricao' => strlen($this->beneficiario->cnpj) == 14 ? 2 : 1,
            'numeroInscricao' => $this->beneficiario->cnpj,
            'nome' => $this->beneficiario->nome
        ];

        return $dados;
    }
}
