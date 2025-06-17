<?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\Controller;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Models\Pessoa;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CreateBoletoBB 
{
    protected $key;
    protected $titulo;
    protected $cliente;
    protected $parametros;
    protected $beneficiario;
    protected $token;
    protected $chave;
    protected $maxTentativas = 3;
    protected $tentativasAtuais = 0;
 

     public function __construct(Request $request)
    {
        $this->key =  $request->id;
        $this->parametros = ParametroBanco::find($this->key);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  
     protected function Create()
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
 return   $dados;
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
  
         $payload = $this->montarPayload();
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
                        'Authorization: Bearer ' . $this->token
                    ],
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_TIMEOUT => 30
                ]);





     }
 }
 