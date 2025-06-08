<?php

/**
 * Classe Gera nfe para obtenção do XML | Assinatura
 *
 * Autor: Rubens dos Santos
 * Email: salvadorbba@gmail.com
 * Celular: (71) 99675-8056
 *
 * Funções:
 *
 * - static function converterData($data)
 * - static function removerAcentoNFSE($texto)
 * - static function modeda_string_LIMPA($value)
 * - static function Moeda($total, $desconto)
 * - static function calcularICMS($valorMercadoria, $aliquotaICMS)
 * - static function CalculosMagico($frete, $produto, $valorTotalNota)
 * - static function cestado($valor)
 * - static function TrataDoc($valor)
 * - function removeSpecialCharacters($str)
 * - function isCpfOrCnpj($str)
 * - static function getverifica($caminhoPFX, $senhaPFX, $id)
 * - static function converterData_H($data)
 * - static function converterDataBr($data)
 * - static function converterDataBrCompetencia($data)
 * - static function ZerosAEsquerda($numero, $quandidade)
 * - static function limitarTexto($texto, $limite)
 * - static function validarValor($valor)
 * - static function ErroXML($mensagem)
 * - static function data_BR($data)
 * - static function calcularDataFutura($dias)
 * - static function SalvaXML($id, $file, $chave, $variavel_banco, $protocolo_envio = null, $controleSerie_id, $finalizar = null, $ValorNF = null)
 * - static function CartaOFF($pedido)
 * - static function calcularCfop($uf_emitente, $uf_destino)
 * - static function SeachCFOP($id, $idDest)
 * - static function DesabilitarBotao($grupo)
 * - static function cleanPasta($value)
 * - static function LimpaTexto($string)
 * - static function removerAcentos($string)
 * - static function valorPorExtenso($valor) 
 */

use Carbon\Carbon;

class MasterClass extends TPage // /// MasterClass::ValidaPagamento($key, $quantidade) formatarCnpj
{
    private static $database = 'minierp';

 public static function formatarCnpj($valor)
    {
        $antes = ['+', '.', '-', '/', '(', ')', ' '];
        $depos = ['', '', '', '', '', '', ''];
        return str_replace($antes, $depos, $valor);
    }
  public static function obterPercentualPartilhaICMS(): float
    {
        $anoAtual = (int) date('Y');

        switch ($anoAtual) {
            case 2016:
                return 40.00;
            case 2017:
                return 60.00;
            case 2018:
                return 80.00;
            case 2019:
            default:
                return 100.00;
        }
    }



////////////////////////////////banco inter///////Action_Abstract

    public static function onlyNumbers($var)
    {
        if($var)
        {
            return preg_replace("/[^0-9]/", "", $var);
        }
        
        return '';
    }
    
    public static function toDouble($number)
	{
		return (double) str_replace(',','.',str_replace('.', '', $number));
	}

	public static function toBRL($number)
	{
		return number_format($number, 2 , ',', '.');
	}
	
	public static function formatMask($value, $mask)
    {
        if ($value)
        {
            $value_index  = 0;
            $clear_result = '';
        
            $value = preg_replace('/[^a-z\d]+/i', '', $value);
            
            for ($mask_index=0; $mask_index < strlen($mask); $mask_index ++)
            {
                $mask_char = substr($mask, $mask_index,  1);
                $text_char = substr($value, $value_index, 1);
        
                if (in_array($mask_char, array('-', '_', '.', '/', '\\', ':', '|', '(', ')', '[', ']', '{', '}', ' ')))
                {
                    $clear_result .= $mask_char;
                }
                else
                {
                    $clear_result .= $text_char;
                    $value_index ++;
                }
            }
            return $clear_result;
        }
    }





















 public static function DataNFCE($valor)
    {

$date = new DateTime($valor);

// Definir o fuso horário para o Brasil (fuso horário de São Paulo, por exemplo)
$date->setTimezone(new DateTimeZone('America/Sao_Paulo'));

// Formatar a data para exibição em português
return $dhEmiFormatada = $date->format('d/m/Y H:i:s');
}



   
    public static function imageToBlackAndWhiteBase64($imagePath) {
    $image = imagecreatefromstring(file_get_contents($imagePath));
    
    if (!$image) {
        return null;
    }

    // Converte a imagem para preto e branco (tons de cinza)
    imagefilter($image, IMG_FILTER_GRAYSCALE);
    imagefilter($image, IMG_FILTER_CONTRAST, -100);

    // Salva a imagem no buffer de saída
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();

    imagedestroy($image);

    // Retorna a imagem convertida em Base64
    return 'data:image/png;base64,' . base64_encode($imageData);
}

    public static function ValidaPagamento($response)
    {
   $cfopsSemPagamento = [
        '1102', '1103', '1556', '1403', '1949', '1201', '1202',
        '5102', '5103', '5556', '5403', '5949', '5201', '5202',
        '5905', '5904', '5155'
    ];

    $cfop = str_pad((string)    $response, 4, '0', STR_PAD_LEFT);

   return !in_array($cfop, $cfopsSemPagamento);
    }
    public static function Debug($response)
    {
        $return = null;

        if (!is_object($response) && !is_array($response)) {
            $return = $response;
        }

        if ($response instanceof TRecord || is_object($response)) {
            $recordCount = 1;
        } elseif ($response instanceof TCollection) {
            $recordCount = $response->count();
        } elseif (is_array($response)) {
            $recordCount = count($response);
        } else {
            $recordCount = 0;
        }

        function objectToArray($obj)
        {
            if (is_object($obj)) {
                $array = (array) $obj;
            } elseif (is_array($obj)) {
                $array = $obj;
            } else {
                return $obj;
            }

            $cleanArray = [];
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    $key = preg_replace('/^\x00.*\x00/', '', $key);
                }
                $cleanArray[$key] = objectToArray($value);
            }
            return $cleanArray;
        }

        $arrayData = $return ?? objectToArray($response);

        $output = print_r($response, true);
        preg_match('/(\w+)\s+Object/', $output, $matches);
        $objectName = $matches[1] ?? "Variavel";

        $Usuario = TSession::getValue('username');
        $Login = TSession::getValue('login');

        $debugInfo = "Objeto: {$objectName}\n";
        $debugInfo .= "Registros Localizados: {$recordCount}\n";
        $debugInfo .= "Usuario: {$Usuario}\n";
        $debugInfo .= "Login: {$Login}\n";
        $debugInfo .= "----------------------------------------\n\n";

        if (!is_array($arrayData)) {
            $debugInfo .= "Returno: " . json_encode($arrayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $debugInfo .= json_encode($arrayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $token = AdiantiApplicationConfig::get('general', 'token');
        $versao = 'MAD v' . $token['builder']['version'] . ' | APP: ' . $token['general']['application_version'];

        TScript::create(
            "
        (function(){
            let debugPanel = document.getElementById('debug-panel');
            if (!debugPanel) {
                debugPanel = document.createElement('div');
                debugPanel.id = 'debug-panel';
                debugPanel.style.position = 'fixed';
                debugPanel.style.top = '0';
                debugPanel.style.right = '-420px';
                debugPanel.style.width = '820px';
                debugPanel.style.height = '100vh';
                debugPanel.style.background = 'rgba(0, 0, 0, 0.85)';
                debugPanel.style.color = '#0f0';
                debugPanel.style.overflowY = 'auto';
                debugPanel.style.padding = '15px';
                debugPanel.style.zIndex = '9999';
                debugPanel.style.transition = 'right 0.3s ease-in-out';
                debugPanel.style.fontFamily = 'verdana';
                debugPanel.style.fontSize = '12px';
                debugPanel.style.borderLeft = '3px solid red';
                debugPanel.style.borderRadius = '10px 0 0 10px';
                
                let header = document.createElement('div');
                header.style.background = '#222';
                header.style.color = '#fff';
                header.style.padding = '10px';
                header.style.textAlign = 'center';
                header.style.fontWeight = 'bold';
                header.style.fontSize = '14px';
                header.style.borderBottom = '2px solid red';
                header.innerText = 'DUMP DIE v1.2 | {$versao}';

                let closeButton = document.createElement('button');
                closeButton.innerText = 'Fechar Janela';
                closeButton.style.background = 'red';
                closeButton.style.color = 'white';
                closeButton.style.padding = '5px 10px';
                closeButton.style.border = 'none';
                closeButton.style.cursor = 'pointer';
                closeButton.style.marginBottom = '10px';
                closeButton.style.display = 'block';
                closeButton.style.width = '100%';
                closeButton.onclick = function() {
                    debugPanel.style.right = '-820px';
                };

                let content = document.createElement('pre');
                content.innerText = atob('" .
                base64_encode($debugInfo) .
                "');

                debugPanel.appendChild(header);
                debugPanel.appendChild(closeButton);
                debugPanel.appendChild(content);
                document.body.appendChild(debugPanel);
            } else {
                let content = debugPanel.querySelector('pre');
                content.innerText = atob('" .
                base64_encode($debugInfo) .
                "');
            }

            setTimeout(() => {
                debugPanel.style.right = '0';
            }, 100);
        })();
    "
        );
    }

    public static function cloneProduto($key)
    {
        TTransaction::open(self::$database);

        // Buscar os objetos TemplateProdutos com o produto_id igual à chave fornecida
        $objetos = TemplateProdutos::where('produto_id', '=', $key)->get();

        foreach ($objetos as $itesClone) {
            // Cria o novo produto clonado
            $objeto_insert = new Produto();
            $objeto_insert->nome = $itesClone->nome;
            $objeto_insert->estoque_atual = 0;
            $objeto_insert->estoque_reserva = 0;
            $objeto_insert->estoque_disponivel = 0;
            $objeto_insert->origem_produto_id = 2;
            $objeto_insert->produto_id = $key;
            $objeto_insert->tecido_id = $itesClone->produto->tecido_id;
            $objeto_insert->familia_produto_id = $itesClone->produto->familia_produto_id;
            $objeto_insert->tipo_produto_id = $itesClone->produto->tipo_produto_id;
            $objeto_insert->grupo_reducao_base_calculo_id = $itesClone->produto->grupo_reducao_base_calculo_id;
            $objeto_insert->system_unit_id = $itesClone->produto->system_unit_id;

            $objeto_insert->system_users_id = $itesClone->produto->system_users_id;
            $objeto_insert->emitente_id = $itesClone->produto->emitente_id;
            $objeto_insert->ativo = 'T';
            $objeto_insert->cfop_saida = $itesClone->produto->cfop_saida;
            $objeto_insert->venda_orig_id = 0;
            $objeto_insert->preco_venda = $itesClone->produto->preco_venda;
            $objeto_insert->preco_venda10 = $itesClone->produto->preco_venda10;
            $objeto_insert->preco_venda50 = $itesClone->produto->preco_venda50;
            $objeto_insert->preco_venda = $itesClone->produto->preco_venda;
            $objeto_insert->unidade_medida_id = $itesClone->produto->unidade_medida_id;

            // Salva o novo produto e captura seu ID
            $objeto_insert->store();

            // Agora, cria os insumos associados a esse produto
            $Insumos = InsumosDetalhados::where('produto_id', '=', $key)->get();

            foreach ($Insumos as $cadInsumos) {
                $objeto_insumo = new InsumosDetalhados();
                $objeto_insumo->produto_id = $objeto_insert->id; // Relaciona os insumos ao novo produto clonado
                $objeto_insumo->nome_insumo = $cadInsumos->nome_insumo;
                $objeto_insumo->descricao = $cadInsumos->nome_insumo;
                $objeto_insumo->quantidade = $cadInsumos->quantidade;
                $objeto_insumo->produto_insumo_ligacao_id = $cadInsumos->produto_insumo_ligacao_id;
                $objeto_insumo->unidade_conversao_saida_id = $cadInsumos->unidade_conversao_saida_id;

                // Salva o insumo detalhado clonado
                $objeto_insumo->store();
            }
        }

        // Fecha a transação
        TTransaction::close();
    }

    public static function Cores($key)
    {
        TTransaction::open('minierp');
        return $objeto = Produto::find($key)->tecido_id;
        TTransaction::close();
    }

    public static function ValorCustoIsumos($key, $quantidade)
    {
        TTransaction::open('minierp');

        $objeto = Produto::find($key);
        $total = $objeto->preco_venda * $quantidade;

        return $total;
        TTransaction::close();
    }

    public static function ValidarEstoque($solicitado, $produto, $compra)
    {
        // Inicia uma transação com o banco de dados
        TTransaction::open('minierp');

        // Recupera o emitente com base na sessão
        $emitente = Emitente::find(TSession::getValue('emitente_id'));

        // Recupera os detalhes dos insumos relacionados ao produto
        $objetos = InsumosDetalhados::where('produto_id', '=', $produto)->get();

        // Remove as pendências existentes para este pedido de compra
        $apagar = InsumosPendencia::where('pedido_compra_id', '=', $compra)->get();
        foreach ($apagar as $item) {
            InsumosPendencia::find($item->id)->delete();
        }

        // Inicializa as variáveis para a mensagem e controle
        $obj = '';
        $temEstoqueInsuficiente = false; // Flag para verificar estoque insuficiente
        $isCinza = true; // Alterna as cores dos blocos
        $totalResultados = count($objetos); // Total de itens
        $itensExibidos = 0; // Contador para itens exibidos
        $maxExibidos = 4; // Máximo de itens a exibir

        // Loop para verificar o estoque de cada item
        foreach ($objetos as $itens) {
            $estoque = ValidacaoEstoqueLocal::view($itens->produto_insumo_ligacao_id, $emitente->local_estoque_id);

            // Verifica se o solicitado excede o estoque
            if ($solicitado > $estoque) {
                // Grava a pendência no banco
                $pendencia = new InsumosPendencia();
                $pendencia->produto_id = $itens->produto_insumo_ligacao_id;
                $pendencia->solicitado = $solicitado;
                $pendencia->pedido_compra_id = $compra;
                $pendencia->quantidade = round($itens->quantidade, 6);

                $pendencia->total = $itens->quantidade * $solicitado;
                $pendencia->store();
            }

            // Se o estoque for insuficiente, acumula os dados para exibição
            if ($estoque < $solicitado) {
                $temEstoqueInsuficiente = true; // Marca que há estoque insuficiente
                $itensExibidos++; // Incrementa o contador de itens exibidos

                // Alterna a cor do bloco para exibição
                $backgroundColor = $isCinza ? '#f2f2f2' : '#ffffff';
                $isCinza = !$isCinza;

                // Acumula os dados do item na mensagem
                if ($itensExibidos <= $maxExibidos) {
                    $obj .=
                        '
                    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #333; background-color: ' .
                        $backgroundColor .
                        '; padding: 10px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 10px;">
                        <p>
                            <strong>Produto:</strong> 
                            ' .
                        $itens->produto_insumo_ligacao_id .
                        ' - ' .
                        $itens->nome_insumo .
                        '
                        </p>
                        <p><strong>Disponível:</strong> ' .
                        $estoque .
                        ' ' .
                        $itens->unidade_conversao_saida->sigla .
                        '</p>
                        <p><strong>Solicitado:</strong> ' .
                        $solicitado .
                        ' ' .
                        $itens->unidade_conversao_saida->sigla .
                        '</p>
                    </div>
                ';
                }
            }
        }

        // Se algum item tiver estoque insuficiente, monta a mensagem
        if ($temEstoqueInsuficiente) {
            // Adiciona mensagem de aviso caso existam mais itens além dos exibidos
            if ($itensExibidos > $maxExibidos) {
                $obj .=
                    '
                <div style="font-family: Arial, sans-serif; font-size: 10px; color: #333; background-color: #ffecd2; padding: 10px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 10px;">
                    <p><strong>Atenção:</strong> Existem outros itens com estoque insuficiente. Favor verificar todos os itens no sistema.</p>
                    <p><strong>Total de itens encontrados:</strong> ' .
                    $totalResultados .
                    '</p>
                </div>
            ';
            }

            // Monta o bloco principal da mensagem
            $obj =
                '
            <div style="font-family: Arial, sans-serif; font-size: 10px; color: #333; background-color: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                <p style="font-weight: bold; color: #e74c3c;">Atenção: Produto com Estoque Insuficiente! Insumos ou matéria-prima</p>
                ' .
                $obj .
                '
            </div>
        ';

            // Exibe a mensagem personalizada
            new TMessage('info', $obj);
        }

        // Fecha a transação
        TTransaction::close();
    }

    public static function CardStoque($produto_id)
    {
        TTransaction::open('minierp');
        $objetos = ViewQuantidadePorLocalProduto::where('produto_id', '=', $produto_id)->get();

        $estoque = Emitente::find(TSession::getValue('emitente_id'))->local_estoque->nome;

        TTransaction::close();

        // Cores para destacar cada cartão (você pode adicionar mais cores ou ajustar conforme necessário)
        $colors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#FFC300', '#33FFF5'];

        $html =
            '
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3 style="font-weight: bold; color: #4A4A4A; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-warehouse" style="color: #FF5733;"></i>
                    Estoque Atual: <span style="color: #FF5733;">' .
            htmlspecialchars($estoque) .
            '</span>
                </h3>
                <p class="text-muted">Aqui estão os itens disponíveis no seu estoque atual.</p>';

        // Mensagem adicional se houver mais de um item
        if (count($objetos) > 1) {
            $html .= '<p class="text-info">Você pode fazer movimentações entre outros estoques para atender à solicitação do pedido.</p>';
        }

        // Mensagem personalizada para quando o resultado for igual a 1
        if (count($objetos) == 1) {
            $html .= '
            <div class="card shadow-sm border-danger mb-4" style="border-left: 5px solid #FF0000;">
                <div class="card-body">
                    <h5 class="card-title text-danger">
                        <i class="fas fa-exclamation-circle"></i> Atenção!
                    </h5>
                    <p class="card-text">Seu estoque não atende o mínimo disponível para continuar o pedido. Por favor, compre novos itens ou ajuste o estoque.</p>
                </div>
            </div>';
        }

        $html .= '
            </div>
        </div>
        <div class="row">';

        $index = 0; // Índice para alternar as cores

        foreach ($objetos as $item) {
            // Escolhe uma cor da lista, alternando entre elas
            $color = $colors[$index % count($colors)];
            $index++;

            $html .=
                '
            <div class="col-md-4">
                <div class="card shadow-sm" style="border-left: 5px solid ' .
                $color .
                ';">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-container" style="color: ' .
                $color .
                '; font-size: 2rem;">
                                    <i class="fas fa-box"></i>&nbsp;
                                </div>
                                <div class="ms-3">
                                    <h6 class="card-title mb-0">' .
                htmlspecialchars($item->nome) .
                '</h6>
                                </div>
                            </div>
                            <div class="ms-auto" style="color: ' .
                $color .
                '; font-weight: bold;">
                                ' .
                htmlspecialchars($item->estoque_diponivel) .
                '
                            </div>
                        </div>
                        <hr style="margin: 10px 0;">
                    </div>
                </div>
            </div>';
        }

        $html .= '
        </div>
    </div>';

        return $html;
    }

    public static function VendedorConectado()
    {
        $objeto = SystemUsers::find(TSession::getValue('userid'));

        return $objeto->cliente_id;
    }

    public static function EstoqueDiponivel($produto_id, $local_estoque_id)
    {
        $repository = new TRepository('ViewEstoqueDiponivelLocal'); // Substitua pelo nome correto da sua view

        // Define os critérios da consulta
        $criteria = new TCriteria();
        $criteria->add(new TFilter('produto_id', '=', $produto_id));
        $criteria->add(new TFilter('local_estoque_id', '=', $local_estoque_id));
        $criteria->setProperty('limit', 1); // Limita o resultado a 1 registro

        // Executa a consulta e obtém o primeiro resultado
        $result = $repository->load($criteria);
        $record = $result[0] ?? null; // Obtém o primeiro registro ou null se não houver resultado

        // Inicializa a variável de resposta
        $resposta = 0;

        // Valida o registro retornado
        if ($record) {
            $resposta = $record->estoque_disponivel !== null && $record->estoque_disponivel > 0 ? $record->estoque_disponivel : 0;
        }

        return $resposta;
    }

    public static function LancamentoManualEstoque($tipo_movimentracao_id, $local_estoque_id, $produto_id, $quantidade, $emitente_id)
    {
        $objeto = new MovimentacaoEstoque();
        $objeto->tipo_movimentracao_id = $tipo_movimentracao_id;
        $objeto->local_estoque_id = $local_estoque_id;
        $objeto->produto_id = $produto_id;
        $objeto->quantidade = $quantidade;
        $objeto->emitente_id = $emitente_id;
        $objeto->system_unit_id = TSession::getValue('userunitid');
        $objeto->system_users_id = TSession::getValue('userid');
        $objeto->ajuste_estoque = "N";
        $objeto->dia_mov = date('d');
        $objeto->data_movimentacao = date('Y-m-d H:i:s');

        $objeto->observacao = 'Lançamento emprestimo';
        $objeto->store();

        return $objeto;
    }

    public static function criarObjetoTransportadora($tipoContratacao, $dados)
    {
        $stdTransportadora = new stdClass();

        switch ($tipoContratacao) {
            case '1':
            case '2':
            case '3':
                if (!empty($dados['documento'])) {
                    $stdTransportadora->xNome = MasterClass::limitarTexto($dados['nome'], 57);
                    $stdTransportadora->IE = $dados['insc_estadual'] ?? null;
                    $stdTransportadora->xEnder = MasterClass::limitarTexto($dados['rua'] . ',' . $dados['bairro'] . ',' . $dados['complemento'], 57);
                    $stdTransportadora->xMun = $dados['cidade'];
                    $stdTransportadora->UF = $dados['uf'];
                    $stdTransportadora->CNPJ = strlen(MasterClass::TrataDoc($dados['documento'])) == 14 ? MasterClass::TrataDoc($dados['documento']) : null;
                    $stdTransportadora->CPF = strlen(MasterClass::TrataDoc($dados['documento'])) != 14 ? MasterClass::TrataDoc($dados['documento']) : null;
                }
                break;

            case '4':
                $stdTransportadora->xNome = substr($dados['razaosocial'], 0, 40);
                $stdTransportadora->IE = $dados['ie'] ?? null;
                $stdTransportadora->xEnder = $dados['endereco'] ?? null;
                $stdTransportadora->xMun = $dados['cidade'] . '-' . $dados['cmun'];
                $stdTransportadora->UF = $dados['siglaUF'];
                $stdTransportadora->CNPJ = MasterClass::TrataDoc($dados['cnpj']);
                $stdTransportadora->CPF = null;
                break;

            case '5':
                $stdTransportadora->xNome = MasterClass::limitarTexto($dados['nome'], 57);
                $stdTransportadora->IE = $dados['insc_estadual'];
                $stdTransportadora->xEnder = $dados['rua'] . ', ' . $dados['bairro'];
                $stdTransportadora->xMun = $dados['cidade'];
                $stdTransportadora->UF = $dados['uf'];

                if (strlen($dados['documento']) == 11) {
                    $stdTransportadora->CPF = MasterClass::TrataDoc($dados['documento']);
                    $stdTransportadora->CNPJ = null;
                } else {
                    $stdTransportadora->CNPJ = MasterClass::TrataDoc($dados['documento']);
                    $stdTransportadora->CPF = null;
                }
                break;

            case '9':
                // Sem ocorrência de transporte
                return null;

            default:
                throw new InvalidArgumentException("Tipo de contratação de frete inválido.");
        }

        return $stdTransportadora;
    }

    public static function processarDados($id)
    {
        // Recuperar Emitente
        $Emitente = Emitente::where('id', '=', TSession::getValue('emitente_id'))->first();

        // Recuperar Cliente a partir de PedidoVenda
        $venda = PedidoVenda::find($id)->cliente;

        // Recuperar Endereço Principal do Cliente
        $Cliente = PessoaEndereco::where('pessoa_id', '=', $venda->id)
            ->where('principal', '=', 'T')
            ->first();

        if (!$Emitente || !$Cliente) {
            throw new Exception("Emitente ou Cliente não encontrado");
        }

        // Calcular Alíquota e CFOP
        $aliquota = CompositorAliquotaICMS::calcular(strtoupper($Emitente->siglaUF), strtoupper($Cliente->cidade->estado->sigla));

        $resultado = MasterClass::calcularCfop(strtoupper($Emitente->siglaUF), strtoupper($Cliente->cidade->estado->sigla));

        $aliquota_intena_destino = CompositorAliquotaICMS::calcular(strtoupper($Cliente->cidade->estado->sigla), strtoupper($Cliente->cidade->estado->sigla));

        // Montar stdClass com os dados
        $stdIde = new stdClass();
        $stdIde->idDest = $resultado['idDest'];
        $stdIde->aliquota = $aliquota;
        $stdIde->aliquotaInternaDestino = $aliquota_intena_destino;

        return $stdIde;
    }

    /**
     * Calcula o dígito verificador (CDV) da chave de acesso da NF-e
     *
     * @param string $chaveAcesso Chave de Acesso da NF-e
     * @return int Dígito Verificador
     */
    public static function popularCDV($data, $cuf, $cnpj, $modelo, $serie, $numero, $datEmissao, $tipoEmissao, $codigoMunicipal)
    {
        // Gerar a chave de acesso
        $chaveAcesso = self::gerarChaveAcesso($cuf, $cnpj, $modelo, $serie, $numero, $datEmissao, $tipoEmissao, $codigoMunicipal);

        // Preencher o campo cdv no objeto $data
        $data->cdv = substr($chaveAcesso, -1); // Pega o último dígito (CDV)
    }

    public static function calcularCfopAplicavel($emitente, $cliente, $produtosVenda, $natureza)
    {
        // Obtém os códigos de UF de origem e destino
        $uf_origem = MasterClass::cestado($emitente->cuf);
        $uf_destino = MasterClass::cestado($cliente->cuf);

        // Calcula o resultado baseado na origem e destino
        $resultado = MasterClass::calcularCfop($uf_origem, $uf_destino);

        // Inicializa o cfop_aplicavel
        $cfop_aplicavel = 0;

        // Loop para iterar os produtos e aplicar o CFOP correto
        foreach ($produtosVenda as $itens_nfe) {
            // Procura o CFOP aplicável baseado na saída do produto e no destino
            $cfops = MasterClass::SeachCFOP($itens_nfe->cfop_saida, $resultado['idDest']);

            // Verifica a natureza do cabeçalho
            if ($natureza->uso_cabeca == 0) {
                // Se a natureza não usar o cabeçalho, aplica o CFOP encontrado
                $cfop_aplicavel = $cfops;
            } else {
                // Se a natureza usa o cabeçalho, usa o CFOP da natureza
                $cfop_aplicavel = $natureza->cfop;
            }

            // Interrompe o loop após o primeiro resultado
            break;
        }

        // Retorna o CFOP aplicável encontrado

        $resp = new stdClass();
        $resp->aplicavel = $cfop_aplicavel;
        $resp->idDest = $resultado['idDest'];
        return $resp;
    }

   
    public static function verificarDuplicidade($serie, $numero)
    {
        // Abrir conexão com o banco

        // Criar um repositório para a tabela de notas fiscais
        $repository = new TRepository('PedidoVenda');

        // Definir critérios para buscar duplicidade
        $criteria = new TCriteria();
        $criteria->add(new TFilter('serie', '=', $serie));
        $criteria->add(new TFilter('numero', '=', $numero));

        // Verificar se já existe uma nota com esses dados
        $existingNotes = $repository->load($criteria);

        // Retornar verdadeiro se duplicado
        return !empty($existingNotes);
    }

    public static function deserializarBase64($Objeto)
    {
        if (!empty($Objeto)) {
            return unserialize(base64_decode($Objeto));
        }
        return null; // Retorna null se o objeto estiver vazio
    }

  
   

    public static function normalizarInfAdProd($input)
    {
        $input = trim($input);
        $aFind = ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç'];
        $aSubs = ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C'];
        $inputReplaced = str_replace($aFind, $aSubs, $input);

        // Substitui & por 'e' e remove caracteres inválidos
        $string = str_replace('&', 'e', $inputReplaced);
        $string = preg_replace("/[^a-zA-Z0-9 @#,-_.;:$%\/]/", "", $string);
        $inputReplaced = preg_replace("/[<>]/", "", $string);

        // Garante que o valor não seja vazio
        if (strlen(trim($inputReplaced)) < 1) {
            $inputReplaced = 'N/A'; // Valor padrão em caso de vazio
        }
    }

    public static function converterData($data)
    {
        $dt = new DateTime($data);
        return $dt->format('Y-m-d\TH:i:s');
    }

    public static function ValidarValorVenda($quantidade, $valor)
    {
        $soma = $valor * $quantidade;
        return $soma;
    }

    public static function ExtessaoFiles($files)
    {
        $extensao = pathinfo($files, PATHINFO_EXTENSION);

        // Mapeia a extensão do arquivo para o ícone correspondente
        $icone = '';
        switch (strtolower($extensao)) {
            case 'pdf':
                $icone = '<i class="fas fa-file-pdf" style="color: red;"></i>';
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $icone = '<i class="fas fa-image" style="color: #007bff;"></i>';
                break;
            case 'doc':
            case 'docx':
                $icone = '<i class="fas fa-file-word" style="color: #0078d4;"></i>';
                break;
            case 'xls':
            case 'xlsx':
                $icone = '<i class="fas fa-file-excel" style="color: #28a745;"></i>';
                break;
            case 'ppt':
            case 'pptx':
                $icone = '<i class="fas fa-file-powerpoint" style="color: #ff6347;"></i>';
                break;
            case 'txt':
                $icone = '<i class="fas fa-file-alt" style="color: #6c757d;"></i>';
                break;
            case 'zip':
            case 'rar':
                $icone = '<i class="fas fa-file-archive" style="color: #ffc107;"></i>';
                break;
            default:
                $icone = '<i class="fas fa-file" style="color: #6c757d;"></i>';
                break;
        }
        return $icone;
    }
    public static function validarFinaceiro($key)
    {
        $objeto = PedidoVenda::find($key);

        if ($objeto->pg_comissao_id != 0) {
            throw new Exception("A venda não pode ser alterada, pois o comissionamento já foi pago.");
        }
    }

    public static function validarDesconto($limite, $valor_item, $desconto)
    {
        // Formata o desconto para garantir que seja um valor decimal (removendo pontos e substituindo vírgulas por ponto)
        $desconto = (float) str_replace(',', '.', str_replace('.', '', $desconto));

        // Converte o limite percentual em valor monetário (reais) com base no valor do item
        $limite_em_reais = ($valor_item * $limite) / 100;

        // Verifica se o desconto em reais é maior que o limite calculado
        if ($desconto > $limite_em_reais) {
            // O desconto informado é maior que o limite permitido
            // Ajusta o desconto para o limite em reais
            return MasterClass::validarValor($limite_em_reais);
        }

        // Se o desconto está dentro do limite, retorna o valor do desconto original
        return MasterClass::validarValor($desconto);
    }

public static function verificarCampoVazio_return($campo, $nomeCampo)
{
    // Verifica se o campo é null ou uma string vazia (ou com apenas espaços)
    return (is_null($campo) || trim($campo) === '') ? $nomeCampo : null;
}



        public static function verificarCampoVazio($campo, $nomeCampo, $tabela, $direcionar)
    {
        if (empty($campo)) {
            $pageParam = []; // ex.: = ['key' => 10]

            TApplication::loadPage($direcionar, 'onShow', $pageParam);

            throw new Exception("O campo $nomeCampo está vazio. Por favor, preencha " . $tabela);
        }
    }
    public static function SalveViewXML2($xmlContent, $chave)
    {
        $path = 'app/xml_envio';
        if (is_dir($path)) {
            // O diretório já existe
        } else {
            mkdir($path, 0777, true);
        }

        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        $dom->formatOutput = true;
        $filePath = $path . '/' . $chave . '.xml';
        $dom->save($filePath);

        return $filePath;
    }

    public static function SalveViewXML($xmlContent)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        $dom->formatOutput = true;
        $filePath = 'app/arquivo.xml';
        $dom->save($filePath);

        return $filePath;
    }
    public static function formatarValorPercentual($valor)
    {
        // Remove whitespaces and formatting characters
        $valor = preg_replace('/\s+/', '', $valor);

        // Check if the value is a valid number
        if (!is_numeric($valor)) {
            return "Valor inválido!";
        }

        // Multiply the value by 100 to get the desired output format
        $valor = round($valor * 100);

        // Convert the value to a string with the desired format
        $valorFormatado = str_pad($valor, 15, "0", STR_PAD_LEFT);

        // Return the formatted value
        return $valorFormatado;
    }

    public static function Modelos($key)
    {
        // Código gerado pelo snippet: "Conexão com banco de dados"
        TTransaction::open('minierp');

        $objeto = PedidoVenda::find($key);
        return $objeto->modelo;

        TTransaction::close();
        // -----
    }

    public static function CadastrarNewTabela($key)
    {
        $Response = Produto::where('classificacao_produto_id', '=', 2)->get();

        foreach ($Response as $itens) {
            // Busca se o item já existe na tabela
            $validar = TabelaPrecoRevenda::where('forca_venda_id', '=', $key)
                ->where('produto_id', '=', $itens->id)
                ->first();

            if (!isset($validar)) {
                // Se não existir, cria um novo registro
                $objeto = new TabelaPrecoRevenda();
                $objeto->forca_venda_id = $key;
                $objeto->produto_id = $itens->id;
                $objeto->valor_original = $itens->preco_venda;
                $objeto->preco_venda_revenda = $itens->preco_venda;
                $objeto->nome_produto = $itens->nome;

                $objeto->store();
            } else {
                // Se o preço original for diferente do preço atual, atualiza o preco_venda_revenda
                if ($validar->valor_original != $itens->preco_venda) {
                    $validar->valor_original = $itens->preco_venda;
                    $validar->preco_venda_revenda = $itens->preco_venda;
                    $validar->store(); // Atualiza o registro existente
                }
            }
        }
    }

    public static function ClienteProduto($produto_id, $cliente_id)
    {
        $pessoa = Pessoa::find($cliente_id);

        $pessoagrupo = ForcaVenda::where('id', '=', $pessoa->forca_venda_id)
            ->where('id', '!=', 1)
            ->first();

        if (isset($pessoagrupo)) {
            $produto = TabelaPrecoRevenda::where('produto_id', '=', $produto_id)->first();
            return self::formatarSemArredondar($produto->preco_venda_revenda);
        } else {
            $produto = Produto::find($produto_id);

            if ($produto->preco_promocao && $produto->preco_promocao_inicio && $produto->preco_promocao_validade) {
                // Obtém a data atual
                $hoje = Carbon::now();

                $inicioPromocao = Carbon::parse($produto->preco_promocao_inicio);
                $validadePromocao = Carbon::parse($produto->preco_promocao_validade);

                if ($hoje->between($inicioPromocao, $validadePromocao)) {
                    return self::formatarSemArredondar($produto->preco_promocao);
                }
            }

            if ($pessoa->pessoa_atacado_id == 1) {
                return self::formatarSemArredondar($produto->preco_venda10);
            } else {
                return self::formatarSemArredondar($produto->preco_venda);
            }
        }
    }

    /**
     * Formata um número com duas casas decimais sem arredondar.
     *
     * @param float $valor O valor a ser formatado.
     * @return string O valor formatado com duas casas decimais.
     */
    private static function formatarSemArredondar($valor)
    {
        $valor = (string) $valor;
        $partes = explode('.', $valor);

        if (count($partes) === 2) {
            // Mantém apenas as duas primeiras casas decimais
            $decimais = substr($partes[1], 0, 2);
            return $partes[0] . '.' . str_pad($decimais, 2, '0', STR_PAD_RIGHT);
        }

        // Se não houver casas decimais, adiciona ".00"
        return $valor . '.00';
    }

    public static function ValidaEndereco($key)
    {
        $pessoa = PessoaEndereco::where('pessoa_id', '=', $key)->get();

        $contado = count($pessoa);
        if ($contado == 1) {
            $objeto = PessoaEndereco::find($pessoa[0]->id);
            if ($objeto) {
                $objeto->principal = "T";
                $objeto->store();
            }
        }
    }

    public static function removerAcentoNFE($texto)
    {
        // Remove acentos
        $texto = preg_replace('/[áàâãªä]/ui', 'a', $texto);
        $texto = preg_replace('/[éèêë]/ui', 'e', $texto);
        $texto = preg_replace('/[íìîï]/ui', 'i', $texto);
        $texto = preg_replace('/[óòôõö]/ui', 'o', $texto);
        $texto = preg_replace('/[úùûü]/ui', 'u', $texto);
        $texto = preg_replace('/[ç]/ui', 'c', $texto);

        // Remove caracteres especiais
        $texto = preg_replace('/[^!-ÿ]/u', ' ', $texto);

        // Remove espaços extras
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Garante que o texto tenha pelo menos um caractere válido
        $texto = trim($texto);
        if (strlen($texto) < 1) {
            $texto = 'N/A'; // Valor padrão em caso de string vazia
        }

        return $texto;
    }

    public static function removerAcentoNFSE($texto)
    {
        // Remove acentos
        $texto = preg_replace('/[áàâãªä]/ui', 'a', $texto);
        $texto = preg_replace('/[éèêë]/ui', 'e', $texto);
        $texto = preg_replace('/[íìîï]/ui', 'i', $texto);
        $texto = preg_replace('/[óòôõö]/ui', 'o', $texto);
        $texto = preg_replace('/[úùûü]/ui', 'u', $texto);
        $texto = preg_replace('/[ç]/ui', 'c', $texto);

        // Remove caracteres especiais
        $texto = preg_replace('/[^a-z0-9]/i', ' ', $texto);

        // Remove espaços extras
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Converte para minúsculas
        $texto = strtolower($texto);

        return trim($texto);
    }

    public static function modeda_string_LIMPA($value)
    {
        return number_format($value, 2, ',', '.');
    }

    public static function MoedaNF($valor)
    {
        // Remover pontos e substituir vírgulas por pontos
        $valor = str_replace('.', '', $valor); // Remove separador de milhares
        $valor = str_replace(',', '.', $valor); // Troca vírgula decimal por ponto decimal

        // Converter para float
        $valor = floatval($valor);

        // Truncar o valor para duas casas decimais sem arredondar
        $valor_truncado = floor($valor * 100) / 100;

        // Retornar o valor formatado com duas casas decimais
        return number_format($valor_truncado, 2, ',', '.');
    }

    public static function Moeda($total, $desconto)
    {
        // Remover pontos e substituir vírgulas por pontos
        $total = str_replace(',', '.', str_replace('.', '', $total));
        $desconto = str_replace(',', '.', str_replace('.', '', $desconto));

        // Converter para float
        $total = floatval($total);
        $desconto = floatval($desconto);

        // Validar se o desconto é maior que o total
        if ($desconto > $total) {
            return 'Erro: O valor do desconto não pode ser maior que o valor total.';
        }

        // Se o desconto for zero ou negativo, o resultado é igual ao total
        if ($desconto <= 0) {
            return number_format($total, 2, ',', '.');
        }

        // Subtrair os valores
        $resultado = $desconto - $total;

        // Garantir que o resultado tenha a escala correta (dividir por 100 se necessário)
        if ($resultado > -1000 && $resultado < 1000) {
            $resultado = $resultado / 100;
        }

        // Usar abs() para garantir que o valor final seja sempre positivo
        $resultado = abs($resultado);

        // Formatar o resultado para ter duas casas decimais
        return number_format($resultado, 2, ',', '.');
    }

    public static function calcularICMS($valorMercadoria, $aliquotaICMS)
    {
        // Certifique-se de validar os parâmetros e tratar erros conforme necessário

        // Calcula o ICMS
        $icms = $valorMercadoria * ($aliquotaICMS / 100);

        return $icms;
    }

    public static function CalculosMagico($frete, $produto, $valorTotalNota)
    {
        // Certifique-se de que os valores são tratados como números decimais
        $proporcaoProduto = $produto / $valorTotalNota;

        // Calcula a parte proporcional do frete para o produto
        $parteFrete = $frete * $proporcaoProduto;

        return $parteFrete;
    }

    public static function cestado($valor)
    {
        return substr($valor, 0, 2);
    }

    // public static function TrataDoc($valor)
    // {
    //     $antes = ['+', '.', '-', '/', '(', ')', ' '];
    //     $depos = ['', '', '', '', '', '', ''];
    //     return str_replace($antes, $depos, $valor);
    // }
  public static function TrataDoc(?string $valor): string
{
    if (is_null($valor)) {
        return '';
    }

    return str_replace(['+', '.', '-', '/', '(', ')', ' '], '', $valor);
}
    // MasterClass::getverifica()

    public function removeSpecialCharacters($str)
    {
        // Remove caracteres especiais (/-.,)
        $cleanedStr = preg_replace('/[-\/.,]/', '', $str);
        return $cleanedStr;
    }

    public static function isCpfOrCnpj($str)
    {
        // Remove caracteres não numéricos
        $cleanedStr = preg_replace('/\D/', '', $str);

        // Identifica o tamanho da string (11 para CPF, 14 para CNPJ)
        $length = strlen($cleanedStr);

        // Validação de CPF
        if ($length === 11) {
            if (preg_match('/^(\d)\1{10}$/', $cleanedStr)) {
                return false; // CPF com todos os dígitos iguais é inválido
            }

            for ($t = 9; $t < 11; $t++) {
                $sum = 0;
                for ($i = 0; $i < $t; $i++) {
                    $sum += $cleanedStr[$i] * ($t + 1 - $i);
                }
                $digit = ($sum * 10) % 11;
                $digit = $digit === 10 ? 0 : $digit;

                if ($cleanedStr[$t] != $digit) {
                    return false; // CPF inválido
                }
            }
            return true; // CPF válido
        }

        // Validação de CNPJ
        if ($length === 14) {
            if (preg_match('/^(\d)\1{13}$/', $cleanedStr)) {
                return false; // CNPJ com todos os dígitos iguais é inválido
            }

            $weights = [
                [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], // Pesos para o primeiro dígito
                [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], // Pesos para o segundo dígito
            ];

            foreach ([12, 13] as $index => $digitPosition) {
                $sum = 0;
                foreach ($weights[$index] as $i => $weight) {
                    $sum += $cleanedStr[$i] * $weight;
                }
                $remainder = $sum % 11;
                $expectedDigit = $remainder < 2 ? 0 : 11 - $remainder;

                if ($cleanedStr[$digitPosition] != $expectedDigit) {
                    return false; // CNPJ inválido
                }
            }
            return true; // CNPJ válido
        }

        // Caso não seja CPF nem CNPJ
        return false;
    }

    public static function getverifica($caminhoPFX, $senhaPFX, $id)
    {
        // Carrega o certificado PFX
        $certificado = file_get_contents($caminhoPFX);

        if (!$certificado) {
            return false; // Falha ao carregar o certificado
        }

        // Tenta decodificar o certificado PFX
        if (openssl_pkcs12_read($certificado, $p12, $senhaPFX)) {
            $cert_info = openssl_x509_parse($p12['cert']);

            // A data de validade é armazenada em $cert_info['validTo_time_t']
            // Você pode formatar a data conforme necessário
            $dataValidade = date('Y-m-d H:i:s', $cert_info['validTo_time_t']);

            $base64Data = base64_encode($caminhoPFX);

            $certificados_extra = file_get_contents($caminhoPFX);
            $certificado_extra_base64_pfx = base64_encode($certificados_extra);

            // Código gerado pelo snippet: "Conexão com banco de dados"
            TTransaction::open('conectarbanco');

            $object = Emitente::find($id);
            if ($object) {
                $object->centificado_base64 = $certificado_extra_base64_pfx;
                $object->validade_certificado = $dataValidade;
                $object->store();
            }

            TTransaction::close();
        } else {
            return false; // Falha ao decodificar o certificado
        }
    }

    public static function converterData_H($data)
    {
        $dt = new DateTime($data);
        return $dt->format('d/m/Y H:i:s');
    }

    public static function converterDataBr($data)
    {
        $dt = new DateTime($data);
        return $dt->format('d/m/Y');
    }
    public static function converterUSA($data)
    {
        $dt = new DateTime($data);
        return $dt->format('Y-m-d');
    }

    public static function converterDataBrCompetencia($data)
    {
        $dt = new DateTime($data);
        return $dt->format('m/Y');
    }

    public static function ZerosAEsquerda($numero, $quandidade)
    {
        // Adiciona 5 zeros à esquerda do número
        $numeroFormatado = str_pad($numero, $quandidade, '0', STR_PAD_LEFT);

        return $numeroFormatado;
    }

    public static function limitarTexto($texto, $limite)
    {
        // Verifica se o texto possui mais caracteres que o limite
        if (strlen($texto) > $limite) {
            // Corta o texto para o limite especificado e adiciona reticências
            $texto = substr($texto, 0, $limite) . '...';
        }

        // Retorna o texto resultante
        return $texto;
    }

    public static function validarValor($valor)
    {
        $minimo = 0;
        $descricao = 'Valor da nfe em R$ - Use o formato com ponto na separação de decimais e não use vírgula, ex. 100.00';

        if (!is_numeric($valor)) {
            return 'O valor não é numérico.';
        }

        if ($valor < $minimo) {
            return "O valor mínimo permitido é $minimo.";
        }

        // Formate o valor com duas casas decimais
        $valorFormatado = number_format($valor, 2, '.', '');

        return $valorFormatado;
    }

    public static function ErroXML($mensagem)
    {
        $Resposta2 = explode('{http://www.portalfiscal.inf.br/nfe}', $mensagem);
        $resultado = '';
        foreach ($Resposta2 as $lista):
            $resultado .= $lista;
        endforeach;

        $Resposta1 = explode('Este XML não é válido.', $resultado);

        $stringLimpa = preg_replace("/(The value|\[facet 'pattern']|is not accepted by the pattern)/", '', $Resposta1[1]);

        return [
            'codigo' => 302,
            'mensagem' => $stringLimpa,
        ];
    }

    /**
     * Converte uma data para o formato "d/m/Y".
     *
     * @param string $data A data a ser convertida.
     * @return string A data convertida.
     */
    public static function data_BR($data)
    {
        $dataConvertida = date('d/m/Y', strtotime($data));
        return $dataConvertida;
    }
    public static function data_USA($data)
    {
        $dataConvertida = date('Y-m-d', strtotime($data));
        return $dataConvertida;
    }

    public static function calcularDataFutura($dias)
    {
        $dataAtual = date('Y-m-d'); // Data atual no formato "AAAA-MM-DD"
        $dataFutura = date('Y-m-d', strtotime("-$dias days", strtotime($dataAtual)));
        return $dataFutura;
    }


 
  function EntreDatasX($date_1,$date_2){
 
 return $date_1;
 
 $data_inicio = new DateTime("2025-03-10");
$data_fim = new DateTime("2025-03-16");

// Resgata diferença entre as datas
$dateInterval = $data_inicio->diff($data_fim);
$dias = $dateInterval->d + ($dateInterval->y * 12);

 
 
    
    return $dias;
 }
    /**
     * Salva informações relacionadas a uma venda em formato XML no banco de dados.
     *
     * @param int $id - Identificador único da venda.
     * @param string $file - Conteúdo do arquivo XML ou caminho do arquivo no sistema de arquivos.
     * @param string $chave - Chave associada à venda.
     * @param string $variavel_banco - Nome da variável no objeto de venda que armazenará o conteúdo do XML.
     * @param string|null $protocolo_envio - Protocolo de envio associado à venda (pode ser nulo).
     * @param int $controleSerie_id - Identificador relacionado ao controle de série.
     * @param int|null $finalizar - Indicador para finalizar a venda (pode ser nulo).
     *
     * @return bool - Retorna verdadeiro se a operação for bem-sucedida.
     */
    public static function SalvaXML($id, $file, $chave, $variavel_banco, $protocolo_envio = null, $controleSerie_id = null, $finalizar = null, $ValorNF = null)
    {
        $objeto = PedidoVenda::find($id);
        
        
                    
            
            $data_inicio = new DateTime($objeto->dt_pedido);
            $data_fim = new DateTime(date('Y-m-d'));
            
              // Resgata diferença entre as datas
            $dateInterval = $data_inicio->diff($data_fim);
            $dias = $dateInterval->days;
            

 
 
        
        if ($objeto) {
            if ($variavel_banco != 'xml') {
                // Se não for 'xml', atribui diretamente o conteúdo do arquivo à variável no objeto
                $objeto->$variavel_banco = $file;
            } else {
                // Se for 'xml', converte o conteúdo do arquivo para base64 e atribui à variável no objeto
                $base64_encoded = base64_encode($file);
                $objeto->$variavel_banco = $base64_encoded;
            }
            // Atribui a chave e o protocolo de envio ao objeto de venda
            $objeto->chave = $chave;
            $objeto->protocolo_envio = $protocolo_envio ?? null;
            // Verifica se a venda deve ser finalizada
            if ($finalizar == 1) {
                $objeto->status = 'AUTORIZADA';
                if ($objeto->agrupada == 1) {
                    $objeto->estado_pedido_venda_id = 17;
                    $objeto->dias_atendimento=$dias;
                    } else {
                    if ($objeto->referenciada_check == 0) {
                        $objeto->estado_pedido_venda_id = 11;
                                       $objeto->dias_atendimento=$dias;
                    } else {
                        $objeto->estado_pedido_venda_id = 16;
                              $objeto->dias_atendimento= $dias;
                    }
                }

                $objeto->codigo_sefaz = 100;
                $objeto->protocolo_numero = $protocolo_envio;
                $objeto->data_final_processo = date('Y-m-d H:i:s');
                $objeto->data_final = date('Y-m-d');
                $objeto->referenciada = 'N';

                //     InsertparcelaFinaceiro::gera($id,$ValorNF);
                // SolicitacaoEventos::create( $id , 'Gerando Financeiro', 'Cobrança', '');
                //  SolicitacaoEventos::create($id, 'Ajuste estoque ', 'Baixa', '');
            }

            // Armazena o objeto no banco de dados
            $objeto->store();
        }
        // Fecha a transação no banco de dados

        // Retorna verdadeiro indicando que a operação foi bem-sucedid
        return true;
    }

    public static function CartaOFF($pedido)
    {
        // Substitua 'campo_condicao' pelo campo que deseja verificar na tabela PedidoEvento
        $pedido = PedidoEvento::where('venda_id', '=', $pedido)->first();

        TTransaction::close();
        if (isset($pedido->id)) {
            return true;
        }

        return false;
    }

    public static function calcularCfop($uf_emitente, $uf_destino)
    {
        // Verificar se a UF do emitente é igual à UF do destinatário
        if ($uf_emitente == $uf_destino) {
            // Se forem diferentes, atribuir um valor específico para idDest
            return ['idDest' => 1]; // Por exemplo, 1 para mesma UF e CFOP 5102
        } else {
            // Se forem iguais, atribuir outro valor específico para idDest
            return ['idDest' => 2]; // Por exemplo, 2 para UFs diferentes e CFOP 6102
        }

        // Se não atender a nenhuma das condições anteriores, atribuir 3 para idDest
        return ['idDest' => 3];
    }

public static function SeachCFOP($id, $idDest)
{
    $objeto = CfopControle::where('cfop', '=', $id)
                          ->where('tipo_id', '=', $idDest)
                          ->first();
 
    
    // Se não for devolução, monta o CFOP padrão
    switch ($idDest) {
        case 1:
            return '5' . $objeto->cfop;
        case 2:
            return '6' . $objeto->cfop;
        case 3:
            return '7' . $objeto->cfop;
        default:
            throw new Exception("Destino inválido.");
    }
}

    public static function DesabilitarBotao($grupo)
    {
        $objeto = SystemUserGroup::where('system_user_id', '=', TSession::getValue('userid'))
            ->where('system_group_id', '=', $grupo)
            ->first();
        if ($objeto) {
            return true;
        } else {
            return false;
        }
    }

    public static function cleanPasta($value)
    {
        $dataHora = $value;
        $caractereSubstituto = '_';

        // Remova espaços, dois pontos ':' e hífens '-' e substitua por um caractere específico
        $dataHoraFormatada = str_replace([':/[^a-zA-Z0-9]/', '-'], $caractereSubstituto, $dataHora);

        return $dataHoraFormatada; // Resultado: "20231019235959"
    }

    public static function LimpaTexto($string)
    {
        $acentosEspeciais = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ç' => 'c',
            'ã' => 'a',
            'õ' => 'o',
            // Adicione mais caracteres conforme necessário
        ];

        // Substitui os acentos e caracteres especiais
        $str = strtr($string, $acentosEspeciais);

        return $str;
    }

    public static function removerAcentos($string)
    {
        $string = preg_replace('/[áàâãä]/u', 'a', $string);
        $string = preg_replace('/[ÁÀÂÃÄ]/u', 'A', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[ÉÈÊË]/u', 'E', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[ÍÌÎÏ]/u', 'I', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[ÓÒÔÕÖ]/u', 'O', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ÚÙÛÜ]/u', 'U', $string);
        $string = preg_replace('/[ç]/u', 'c', $string);
        $string = preg_replace('/[Ç]/u', 'C', $string);

        return $string;
    }

    public static function valorPorExtenso($valor)
    {
        // Separa a parte inteira e decimal
        $parteInteira = floor($valor);
        $parteDecimal = round(($valor - $parteInteira) * 100);

        $fmt = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);

        $extensoInteira = $fmt->format($parteInteira);
        $extensoDecimal = $fmt->format($parteDecimal);

        $extenso = ucfirst($extensoInteira) . ' reais';

        if ($parteDecimal > 0) {
            $extenso .= ' e ' . $extensoDecimal . ' centavos';
        }

        return $extenso;
    }

    public static function LetrasMaiuculas($texto)
    {
        return strtoupper($texto);
    }

    public static function SalvarXMLFile($responseAssinado, $cnpjEmit, $ambiente, $chave, $pasta)
    {
        try {
            // Obtém data de geração
            $dataAtual = date('Y/m/d'); // Formato ano/mês/dia
            [$ano, $mes, $dia] = explode('/', $dataAtual);

            // Define caminho da pasta e do arquivo
            $pathAssinadas = "app/XML/{$pasta}/{$cnpjEmit}/{$ambiente}/autorizada/{$ano}/{$mes}/{$dia}";
            $caminhoArquivo = "{$pathAssinadas}/{$chave}-nfe.xml";

            // Cria a pasta se não existir
            if (!is_dir($pathAssinadas)) {
                mkdir($pathAssinadas, 0777, true);
            }

            // Salva o XML assinado no caminho especificado
            $resultado = file_put_contents($caminhoArquivo, $responseAssinado);

            // Retorna o caminho do arquivo salvo se bem-sucedido
            //  echo  $caminhoArquivo;
        } catch (Exception $e) {
            // Trata exceções (opcional)
            error_log('Erro ao assinar e salvar XML: ' . $e->getMessage());
            return false;
        }
    }
}
