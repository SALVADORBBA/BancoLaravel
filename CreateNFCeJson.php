<?php
date_default_timezone_set('America/Sao_Paulo');

use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;

class CreateNFCeJson
{
    private $dados;
    private $usuario;
    private $emitente;
    private $controleSerie;
    private $tools;
    private $limit = 1000.00;

    public function __construct($dados, $usuario)
    {
        TTransaction::open('conectabanco');
        $this->dados = (object) $dados;
        $this->usuario = (object) $usuario;
        $this->emitente = Emitentes::find($this->usuario->emitentes_id);
        $this->controleSerie = ControleSerie::find($this->emitente->controle_serie_id);
        $config = new NFeConfig($this->emitente);
        $this->tools = $config->json();
        TTransaction::close();
    }

    public function create()
    {
        TTransaction::open('conectabanco');

        // Verificação do cliente/destinatário
        $cliente = $this->dados->cliente ?? null;
        $valorNota = $this->dados->total->ICMSTot->vNF ?? 0;

        // Verifica se cliente é um array ou objeto e ajusta conforme necessário
        if (is_array($cliente)) {
            $destinatarioPreenchido = !empty($cliente['cpf']) || !empty($cliente['cnpj']);
        } else if (is_object($cliente)) {
            $cliente = (array) $cliente;
            $destinatarioPreenchido = !empty($cliente['cpf']) || !empty($cliente['cnpj']);
        } else {
            $destinatarioPreenchido = false;
        }

        // Verifica limite de valor para obrigatoriedade do destinatário
        if ($valorNota > $this->limit && !$destinatarioPreenchido) {
            throw new Exception('Para notas acima de R$ ' . number_format($this->limit, 2, ',', '.') . ' é obrigatório informar o destinatário.');
        }

        // Criação do objeto Make para construir o XML
        $nfe = new Make();

        // Tag infNFe
        $std = new \stdClass();
        $std->versao = '4.00';
        $std->Id = null;
        $std->pk_nItem = '';
        $nfe->taginfNFe($std);

        // Tag ide (Identificação da NF-e)
        $std = new \stdClass();
        $std->cUF = $this->dados->ide->cUF ?? '29'; // Código da UF
        $std->cNF = $this->dados->ide->cNF ?? '12345678'; // Código numérico
        $std->natOp = $this->dados->ide->natOp ?? 'VENDA'; // Natureza da operação
        $std->mod = $this->dados->ide->mod ?? '65'; // Modelo (65 = NFCe)
        $std->serie = $this->dados->ide->serie ?? '1'; // Série
        $std->nNF = $this->dados->ide->nNF ?? '1'; // Número
        $std->dhEmi = $this->dados->ide->dhEmi ?? date('Y-m-d\TH:i:sP'); // Data e hora de emissão
        $std->tpNF = $this->dados->ide->tpNF ?? '1'; // Tipo de operação (1 = saída)
        $std->idDest = $this->dados->ide->idDest ?? '1'; // Destino da operação
        $std->cMunFG = $this->dados->ide->cMunFG ?? '2927408'; // Código do município
        $std->tpImp = $this->dados->ide->tpImp ?? '4'; // Formato de impressão (4 = DANFE NFCe)
        $std->tpEmis = $this->dados->ide->tpEmis ?? '1'; // Forma de emissão
        $std->cDV = null; // Dígito verificador (calculado automaticamente)
        $std->tpAmb = $this->dados->ide->tpAmb ?? '2'; // Ambiente (1 = produção, 2 = homologação)
        $std->finNFe = $this->dados->ide->finNFe ?? '1'; // Finalidade
        $std->indFinal = $this->dados->ide->indFinal ?? '1'; // Consumidor final (1 = sim)
        $std->indPres = $this->dados->ide->indPres ?? '1'; // Presença do comprador
        $std->procEmi = $this->dados->ide->procEmi ?? '0'; // Processo de emissão
        $std->verProc = $this->dados->ide->verProc ?? 'Developer-API'; // Versão do processo
        $nfe->tagide($std);

        // Tag emit (Emitente)
        $std = new \stdClass();
        $std->xNome = $this->emitente->razao_social;
        $std->xFant = $this->emitente->nome_fantasia;
        $std->IE = $this->emitente->ie;
        $std->CRT = $this->emitente->crt;

        if (!empty($this->emitente->cnpj)) {
            $std->CNPJ = preg_replace('/\D/', '', $this->emitente->cnpj);
        } elseif (!empty($this->emitente->cpf)) {
            $std->CPF = preg_replace('/\D/', '', $this->emitente->cpf);
        }

        $nfe->tagemit($std);

        // Tag enderEmit (Endereço do Emitente)
        $std = new \stdClass();
        $std->xLgr = $this->emitente->endereco;
        $std->nro = $this->emitente->numero;
        $std->xCpl = $this->emitente->complemento;
        $std->xBairro = $this->emitente->bairro;
        $std->cMun = $this->emitente->codigo_municipio;
        $std->xMun = $this->emitente->municipio;
        $std->UF = $this->emitente->uf;
        $std->CEP = preg_replace('/\D/', '', $this->emitente->cep);
        $std->cPais = '1058';
        $std->xPais = 'BRASIL';
        $std->fone = preg_replace('/\D/', '', $this->emitente->telefone);
        $nfe->tagenderEmit($std);

        // Tag dest (Destinatário)
        if ($destinatarioPreenchido) {
            $std = new \stdClass();
            $std->xNome = $cliente['razao_social'] ?? 'Consumidor Final';
            $std->indIEDest = $cliente['indIEDest'] ?? '9';

            if (!empty($cliente['ie'])) {
                $std->IE = $cliente['ie'];
            }

            if (!empty($cliente['cnpj'])) {
                $std->CNPJ = preg_replace('/\D/', '', $cliente['cnpj']);
            } elseif (!empty($cliente['cpf'])) {
                $std->CPF = preg_replace('/\D/', '', $cliente['cpf']);
            }

            if (!empty($cliente['email'])) {
                $std->email = $cliente['email'];
            }

            $nfe->tagdest($std);

            // Tag enderDest (Endereço do Destinatário)
            $std = new \stdClass();
            $std->xLgr = $cliente['endereco'] ?? 'Não informado';
            $std->nro = $cliente['numero'] ?? 'SN';
            $std->xBairro = $cliente['bairro'] ?? 'Centro';
            $std->cMun = $cliente['codigo_municipio'] ?? '0000000';
            $std->xMun = $cliente['municipio'] ?? 'Cidade';
            $std->UF = $cliente['uf'] ?? 'UF';

            if (!empty($cliente['cep'])) {
                $std->CEP = preg_replace('/\D/', '', $cliente['cep']);
            }

            $std->cPais = '1058';
            $std->xPais = 'BRASIL';

            if (!empty($cliente['telefone'])) {
                $std->fone = preg_replace('/\D/', '', $cliente['telefone']);
            }

            $nfe->tagenderDest($std);
        } else {
            // Consumidor final (quando não há destinatário específico)
            $std = new \stdClass();
            $std->xNome = 'Consumidor Final';
            $std->indIEDest = '9';
            $nfe->tagdest($std);
        }

        // Adicionar itens (produtos)
        if (isset($this->dados->det) && is_array($this->dados->det)) {
            foreach ($this->dados->det as $key => $item) {
                $nItem = $key + 1;

                // Tag prod (Produto)
                $std = new \stdClass();
                $std->item = $nItem;
                $std->cProd = $item->prod->cProd ?? '';
                $std->cEAN = $item->prod->cEAN ?? 'SEM GTIN';
                $std->xProd = $item->prod->xProd ?? '';
                $std->NCM = $item->prod->NCM ?? '';
                $std->CFOP = $item->prod->CFOP ?? '';
                $std->uCom = $item->prod->uCom ?? '';
                $std->qCom = $item->prod->qCom ?? '0';
                $std->vUnCom = $item->prod->vUnCom ?? '0';
                $std->vProd = $item->prod->vProd ?? '0';
                $std->cEANTrib = $item->prod->cEANTrib ?? 'SEM GTIN';
                $std->uTrib = $item->prod->uTrib ?? '';
                $std->qTrib = $item->prod->qTrib ?? '0';
                $std->vUnTrib = $item->prod->vUnTrib ?? '0';
                $std->indTot = $item->prod->indTot ?? '1';
                $nfe->tagprod($std);

                // Tag imposto (Imposto)
                $std = new \stdClass();
                $std->item = $nItem;
                $std->vTotTrib = $item->imposto->vTotTrib ?? '0.00';
                $nfe->tagimposto($std);

                // Tag ICMS (Imposto sobre Circulação de Mercadorias e Serviços)
                $std = new \stdClass();
                $std->item = $nItem;
                $std->orig = '0';
                $std->CST = '00';
                $std->modBC = '0';
                $std->vBC = '0.00';
                $std->pICMS = '0.00';
                $std->vICMS = '0.00';
                $nfe->tagICMS($std);

                // Tag PIS (Programa de Integração Social)
                $std = new \stdClass();
                $std->item = $nItem;
                $std->CST = '07';
                $std->vBC = '0.00';
                $std->pPIS = '0.00';
                $std->vPIS = '0.00';
                $nfe->tagPIS($std);

                // Tag COFINS (Contribuição para o Financiamento da Seguridade Social)
                $std = new \stdClass();
                $std->item = $nItem;
                $std->CST = '07';
                $std->vBC = '0.00';
                $std->pCOFINS = '0.00';
                $std->vCOFINS = '0.00';
                $nfe->tagCOFINS($std);
            }
        }

        // Tag total (Total da NF-e)
        $std = new \stdClass();
        $std->vBC = $this->dados->total->ICMSTot->vBC ?? '0.00';
        $std->vICMS = $this->dados->total->ICMSTot->vICMS ?? '0.00';
        $std->vICMSDeson = $this->dados->total->ICMSTot->vICMSDeson ?? '0.00';
        $std->vFCP = $this->dados->total->ICMSTot->vFCP ?? '0.00';
        $std->vBCST = '0.00';
        $std->vST = '0.00';
        $std->vFCPST = '0.00';
        $std->vFCPSTRet = '0.00';
        $std->vProd = $this->dados->total->ICMSTot->vProd ?? '0.00';
        $std->vFrete = $this->dados->total->ICMSTot->vFrete ?? '0.00';
        $std->vSeg = $this->dados->total->ICMSTot->vSeg ?? '0.00';
        $std->vDesc = $this->dados->total->ICMSTot->vDesc ?? '0.00';
        $std->vII = $this->dados->total->ICMSTot->vII ?? '0.00';
        $std->vIPI = $this->dados->total->ICMSTot->vIPI ?? '0.00';
        $std->vIPIDevol = '0.00';
        $std->vPIS = $this->dados->total->ICMSTot->vPIS ?? '0.00';
        $std->vCOFINS = $this->dados->total->ICMSTot->vCOFINS ?? '0.00';
        $std->vOutro = $this->dados->total->ICMSTot->vOutro ?? '0.00';
        $std->vNF = $this->dados->total->ICMSTot->vNF ?? '0.00';
        $std->vTotTrib = '0.00';
        $nfe->tagICMSTot($std);

        // Tag transp (Transporte)
        $std = new \stdClass();
        $std->modFrete = $this->dados->transp->modFrete ?? '9';
        $nfe->tagtransp($std);

        // Tag pag (Pagamento)
        $std = new \stdClass();
        $std->vTroco = $this->dados->pag->vTroco ?? '0.00';
        $nfe->tagpag($std);

        // Tag detPag (Detalhamento do Pagamento)
        if (isset($this->dados->pag->detPag)) {
            if (is_array($this->dados->pag->detPag)) {

                foreach ($this->dados->pag->detPag as $pagamento) {
                    $nfe->tagdetPag($pagamento);
                }
            }
        }

    
    }
}
