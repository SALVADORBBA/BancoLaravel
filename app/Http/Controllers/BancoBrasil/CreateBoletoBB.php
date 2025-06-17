<?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\Controller;
use App\Models\Beneficiario;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Http\Request;
use stdClass;

class CreateBoletoBB
{

    private $titulo;
    private $parametros;
    private $Token;
    private $key;
    private $meunumero;
    private $beneficiario;
        private $cliente;
    public function __construct(Request $request)
    {
         $this->key=$request->id;
         $this->titulo = ContasReceber::find($this->key);
         $this->cliente = $this->titulo->pessoa;
         $this->parametros = ParametroBanco::find($this->titulo->parametros_bancos_id);
         $this->beneficiario = Beneficiario::find($this->titulo->beneficiario_id);
         $Token = new BancoBrasilTokenService($this->parametros->id);
         $resposta = (object)  $Token->create();
        $this->Token  =   $resposta->token;
    }
    public function create()
    {



        $numeroAgregado = str_pad($this->beneficiario->id . $this->key . $this->parametros->system_unit_id . $this->parametros->id, 9, '0', STR_PAD_LEFT);

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
            'tipoInscricao' => strlen($this->beneficiario->documento) == 14 ? 2 : 1,
            'numeroInscricao' => $this->beneficiario->documento,
            'nome' => $this->beneficiario->nome
        ];

 
        $json = json_encode($dados);

        $url = $this->parametros->url2 . '?gw-dev-app-key=' . $this->parametros->gw_dev_app_key;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' .  $this->Token
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);


        
        $response = curl_exec($curl);
        curl_close($curl);

     $response = json_decode($response);

     return  $response ;
    }
}
