<?php

namespace App\Http\Controllers\Bradesco;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Models\Beneficiario;
use App\Models\ControleMeuNumeros;
use Exception;
use stdClass;

class CreateBoletoBradesco extends Controller
{
    private $titulos;
    private $parametros;
    private $Token;
    private $tipo;
    private $meunumero;
    private $beneficiario;
    public function __construct(Request $request)
    {
        // Inicializando as variáveis necessárias
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->Token = GetTokenBradesco::create($this->parametros->id);



        $this->tipo = $request->tipo;
        $this->meunumero = ControleMeuNumeroController::create($this->parametros->id);

        $this->beneficiario = Beneficiario::find($this->titulos->beneficiario_id);

        // $this->beneficiario = Beneficiario::where('id', $this->titulos->beneficiario_id)->pluck('id','nome',)->first();
    }

    public function create()
    {
        $dados = new stdClass();

        // SEÇÃO: DADOS DE IDENTIFICAÇÃO DO BENEFICIÁRIO
        // -----------------------------------------
        $dados->nroCpfCnpjBenef = 68542653;
        $dados->filCpfCnpjBenef = "1018";
        $dados->digCpfCnpjBenef = 38;

        // SEÇÃO: DADOS DE CONTROLE E ACESSO
        // -----------------------------------------
        $dados->ctitloCobrCdent = 0;
        $dados->registrarTitulo = 1;
        $dados->codUsuario = "APISERVIC";
        $dados->tipoAcesso = 2;
        $dados->cpssoaJuridContr = "";
        $dados->ctpoContrNegoc = "";
        $dados->cidtfdProdCobr = 9;
        $dados->nseqContrNegoc = "";
        $dados->cnegocCobr = 111111111111111112;
        $dados->filler = "";
        $dados->eNseqContrNegoc = "";

        // SEÇÃO: DADOS DO TÍTULO/BOLETO
        // -----------------------------------------
        $dados->tipoRegistro = 1;
        $dados->codigoBanco = 237;
        $dados->cprodtServcOper = "";
        $dados->demisTitloCobr = "17.12.2024";
        $dados->ctitloCliCdent = "TESTEBIA";
        $dados->dvctoTitloCobr = "20.02.2025";
        $dados->cidtfdTpoVcto = "";
        $dados->vnmnalTitloCobr = 6000;
        $dados->cindcdEconmMoeda = 9;
        $dados->cespceTitloCobr = 2;
        $dados->qmoedaNegocTitlo = 0;

        // SEÇÃO: CONFIGURAÇÕES DE PROTESTO E ACEITE
        // -----------------------------------------
        $dados->ctpoProteTitlo = 0;
        $dados->cindcdAceitSacdo = "N";
        $dados->ctpoPrzProte = 0;
        $dados->ctpoPrzDecurs = 0;
        $dados->ctpoProteDecurs = 0;
        $dados->cctrlPartcTitlo = 0;

        // SEÇÃO: CONFIGURAÇÕES DE PAGAMENTO
        // -----------------------------------------
        $dados->cindcdPgtoParcial = "N";
        $dados->cformaEmisPplta = "02";
        $dados->qtdePgtoParcial = 0;

        // SEÇÃO: JUROS, MULTAS E DESCONTOS
        // -----------------------------------------
        $dados->ptxJuroVcto = 0;
        $dados->filler1 = "";
        $dados->vdiaJuroMora = 0;
        $dados->pmultaAplicVcto = 0;
        $dados->qdiaInicJuro = 0;
        $dados->vmultaAtrsoPgto = 0;
        $dados->pdescBonifPgto01 = 0;
        $dados->qdiaInicMulta = 0;
        $dados->vdescBonifPgto01 = 0;
        $dados->pdescBonifPgto02 = 0;
        $dados->dlimDescBonif1 = "";
        $dados->vdescBonifPgto02 = 0;
        $dados->pdescBonifPgto03 = 0;
        $dados->dlimDescBonif2 = "";
        $dados->vdescBonifPgto03 = 0;
        $dados->ctpoPrzCobr = 0;
        $dados->dlimDescBonif3 = "";
        $dados->pdescBonifPgto = 0;
        $dados->dlimBonifPgto = "";
        $dados->vdescBonifPgto = 0;
        $dados->vabtmtTitloCobr = 0;
        $dados->filler2 = "";
        $dados->viofPgtoTitlo = 0;

        // SEÇÃO: DADOS DO SACADO (PAGADOR)
        // -----------------------------------------
        $dados->isacdoTitloCobr = "TESTE EMPRESA PGIT";
        $dados->enroLogdrSacdo = "TESTE";
        $dados->elogdrSacdoTitlo = "TESTE";
        $dados->ecomplLogdrSacdo = "TESTE";
        $dados->ccepSacdoTitlo = 6332;
        $dados->ebairoLogdrSacdo = "TESTE";
        $dados->ccomplCepSacdo = 130;
        $dados->imunSacdoTitlo = "TESTE";
        $dados->indCpfCnpjSacdo = 1;
        $dados->csglUfSacdo = "SP";
        $dados->renderEletrSacdo = "";
        $dados->cdddFoneSacdo = 0;
        $dados->nroCpfCnpjSacdo = 38453450803;
        $dados->cfoneSacdoTitlo = 0;

        // SEÇÃO: DADOS BANCÁRIOS PARA DÉBITO
        // -----------------------------------------
        $dados->bancoDeb = 0;
        $dados->agenciaDebDv = 0;
        $dados->agenciaDeb = 0;
        $dados->bancoCentProt = 0;
        $dados->contaDeb = 0;

        // SEÇÃO: DADOS DO SACADOR/AVALISTA
        // -----------------------------------------
        $dados->isacdrAvalsTitlo = "";
        $dados->agenciaDvCentPr = 0;
        $dados->enroLogdrSacdr = "0";
        $dados->elogdrSacdrAvals = "";
        $dados->ecomplLogdrSacdr = "";
        $dados->ccomplCepSacdr = 0;
        $dados->ebairoLogdrSacdr = "";
        $dados->csglUfSacdr = "";
        $dados->ccepSacdrTitlo = 0;
        $dados->imunSacdrAvals = "";
        $dados->indCpfCnpjSacdr = 0;
        $dados->renderEletrSacdr = "";
        $dados->nroCpfCnpjSacdr = 0;
        $dados->cdddFoneSacdr = 0;
        $dados->filler3 = "0";
        $dados->cfoneSacdrTitlo = 0;

        // SEÇÃO: CONFIGURAÇÕES ADICIONAIS E PIX
        // -----------------------------------------
        $dados->iconcPgtoSpi = "";
        $dados->fase = "1";
        $dados->cindcdCobrMisto = "S";
        $dados->ialiasAdsaoCta = "";
        $dados->ilinkGeracQrcd = "";
        $dados->caliasAdsaoCta = "";
        $dados->wqrcdPdraoMercd = "";
        $dados->validadeAposVencimento = "";
        $dados->filler4 = "";
        $dados->idLoc = "";

        // SEÇÃO: CONFIGURAÇÃO DE CERTIFICADOS E AUTENTICAÇÃO
        // -----------------------------------------
        $certificadoPublico = storage_path('app/public/certificado/' . $this->parametros->id . '/compdados.homologacao.pem');
        $chavePrivada        = storage_path('app/public/certificado/' . $this->parametros->id . '/compdados.homologacao.key.pem');

        if (!file_exists($certificadoPublico) || !file_exists($chavePrivada)) {
            return response()->json(['erro' => 'Certificados não encontrados'], 500);
        }

        $client_id     = $this->parametros->client_id;
        $client_secret = $this->parametros->client_secret;

        // SEÇÃO: EXECUÇÃO DA REQUISIÇÃO
        // -----------------------------------------
        // Converte o objeto para JSON
        $json = json_encode($dados);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapisandbox.prebanco.com.br/boleto-hibrido/cobranca-registro/v1/gerarBoleto',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSLCERT => $certificadoPublico,
            CURLOPT_SSLKEY  => $chavePrivada,
            CURLOPT_KEYPASSWD => $this->parametros->senha,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->Token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);

      
    // SEÇÃO: ATUALIZAÇÃO DOS DADOS DO BOLETO NO BANCO DE DADOS
    // -----------------------------------------
 
        // Extrair o nosso número da resposta do Bradesco
        $nsuCode = $response->nsuCode ?? $this->meunumero ?? null;
        
      
        ContasReceber::where('id', $this->titulos->id)->update([
            'nossonumero'    => $response->cnegocCobr ?? $this->meunumero ?? null,
            'codigobarras'   => $response->codBarras10 ?? null,
            'linhadigitavel' => $response->linhaDig10 ?? null,
 
            'covenantCode'   => $this->beneficiario->covenantCode ?? null,
            'beneficiario_id'=> $this->beneficiario->id,
            'qrCodePix'      => $response->wqrcdPdraoMercd ?? null,
            'qrCodeUrl'      => $response->ilinkGeracQrcd ?? null,
            'status'         => 3,
        ]);
  

    return $response;
    }
}
