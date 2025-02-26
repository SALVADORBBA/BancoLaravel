<?php

namespace App\Http\Controllers\ClassGlobais;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;
use stdClass;

class ClassGenerica extends Controller
{
    /**
     * Limita o tamanho do texto ao valor especificado.
     *
     * @param string $texto O texto a ser limitado.
     * @param int $limite O limite máximo de caracteres.
     * @return string O texto limitado.
     */
    public static function limitarTexto($texto, $limite)
    {
        if (strlen($texto) > $limite) {
            $texto = substr($texto, 0, $limite);
        }
        return $texto;
    }

    /**
     * Remove caracteres especiais de uma string.
     *
     * @param string $value A string a ser limpa.
     * @return string A string sem caracteres especiais.
     */
    public static function LimpaEspecial($value)
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $value);
    }
    public static function tipodoc($tipo)
    {
        
         if (strlen($tipo) === 11) {
            return $tipo_pessoa = "PESSOA_FISICA";
        } else {
          return $tipo_pessoa = "PESSOA_JURIDICA";
        }
    
         
    } 
    public static  function cleandoc( $value){
        
        
        return $value= preg_replace('/[^a-zA-Z0-9\s]/', '', $value);
 
         
     }
  public static function TrataDoc($valor)
  {
    $antes = ['+', '.', '-', '/', '(', ')', ' '];
    $depos = ['', '', '', '', '', '', ''];
    return str_replace($antes, $depos, $valor);
  }
  
  
  
  
   

  
  
    public static function removerAcentosLetras($string)
    {
        $acentos = array(
            '/[áàâã]/u' => 'a',
            '/[éèê]/u' => 'e',
            '/[íì]/u' => 'i',
            '/[óòôõ]/u' => 'o',
            '/[úùû]/u' => 'u',
            '/[ç]/u' => 'c',
            '/[ÁÀÂÃ]/u' => 'A',
            '/[ÉÈÊ]/u' => 'E',
            '/[ÍÌ]/u' => 'I',
            '/[ÓÒÔÕ]/u' => 'O',
            '/[ÚÙÛ]/u' => 'U',
            '/[Ç]/u' => 'C',
        );
        return preg_replace(array_keys($acentos), array_values($acentos), $string);
    }

    /**
     * Converte uma data para o formato  de "Y-m-d" para "d.m.Y".
     *
     * @param string $data A data a ser convertida.
     * @return string A data convertida.
     */
    public static function CVDataBB($data)
    {
        $dataConvertida = date("d.m.Y", strtotime($data));
        return $dataConvertida;
    }
    /**
     * Converte uma data para o formato de  "d.m.Y" para "Y-m-d"
     *
     * @param string $data A data a ser convertida.
     * @return string A data convertida.
     */

    public static function CVDataBB_revessa($data)
    {
        $dataConvertida = date("Y-m-d", strtotime($data));
        return $dataConvertida;
    }
    /**
     * Converte uma data para o formato "d/m/Y".
     *
     * @param string $data A data a ser convertida.
     * @return string A data convertida.
     */
    public static function data_BR($data)
    {
        $dataConvertida = date("d/m/Y", strtotime($data));
        return $dataConvertida;
    }

    /**
     * Gera o número de identificação a partir do número do convênio e do número de controle.
     *
     * @param int $numeroConvenio O número do convênio.
     * @param int $numeroControle O número de controle.
     * @return string O número de identificação gerado.
     */
    public static function NumeroIdentificacao($numeroConvenio, $numeroControle)
    {
        $numeroConvenio = str_pad($numeroConvenio, 7, '0', STR_PAD_LEFT);
        $numeroControle = str_pad($numeroControle, 9, '0', STR_PAD_LEFT);
        return '000' . $numeroConvenio . $numeroControle;
    }

    /**
     * Converte um valor numérico para uma string formatada como moeda.
     *
     * @param float $value O valor numérico.
     * @return string O valor formatado como moeda. MillFunctionsClass::modeda_string
     */
    public static function modeda_string($value)
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public static function ReturnTrue($file, $data)
    {

        return response()->json([
            'message' => 'Operação bem-sucedida.',
            'codigo' => 200,
            'file' => $file,
            'data' => $data,
        ], 200);
    }

    public static function ReturnFalse($file, $data)
    {

        return response()->json([
            'message' => 'Dados não localziado',
            'codigo' => 400,
            'file' => $file,
            'data' => $data,
        ], 400);
    }

    public static function extractInvalidFields($data, $errors)
    {
        $invalidFields = [];
        foreach ($errors->keys() as $field) {
            $value = data_get($data, $field);
            $invalidFields[$field] = $value;
        }

        return $invalidFields;
    }

    public static function formatarValorItau($valor)
    {
        // Remover espaços em branco e caracteres de formatação
        $valor = preg_replace('/\s+/', '', $valor);

        // Verificar se o valor é um número válido
        if (!is_numeric($valor)) {
            return "Valor inválido!";
        }

        // Arredondar o valor para 2 casas decimais
        $valor = round($valor, 2);

        // Converter o valor para uma string com duas casas decimais
        $valorFormatado = number_format($valor, 2, '', '');

        // Verificar o comprimento do valor formatado
        if (strlen($valorFormatado) > 15) {
            return "Valor inválido!";
        }

        // Preencher com zeros à esquerda até ter 15 dígitos
        $valorFormatado = str_pad($valorFormatado, 15, "0", STR_PAD_LEFT);

        // Retornar o valor formatado
        return $valorFormatado;
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


    ///banco do brasil
    public static function CobrancaJSON($parametros_id, $resposerCobranca)
    {

        $ParamentrosBanco = DB::table('parametros_bancos')
            ->where("id", $parametros_id)->first();

        $Beneficiario = DB::table('beneficiario')
            ->where("id", $ParamentrosBanco->beneficiario_id)->first();

        $Banco = DB::table('bancos_modulos')
            ->where("id", $ParamentrosBanco->bancos_modulos_id)->first();
        $STD = new stdClass();
        $STD->Cobranca = $resposerCobranca;
        $STD->Parametro = $ParamentrosBanco;
        $STD->Banco = $Banco;
        $STD->Beneficiario = $Beneficiario;
        return $STD;
    }

    public static function ClienteNewCobranca($documento, $nome, $endereco, $bairro, $cidade, $cep, $uf, $telefone, $email, $numero, $complemento, $system_unit_id)
    {
        // Verificar se o cliente já existe
        $existingCliente = Cliente::where('documento', $documento)
            ->where('system_unit_id', $system_unit_id)
            ->first();

        // Se o cliente já existir, retorna o registro existente
        if ($existingCliente) {
            // Verificar se existe um endereço de cobrança com o mesmo CEP
            $existingEndereco = CobrancaEndereco::where('cliente_id', $existingCliente->id)
                ->where('cep', $cep)
                ->first();

            // Se já existir um endereço de cobrança com o mesmo CEP, retorna o registro existente
            if ($existingEndereco) {
                $retorno = new stdClass();
                $retorno->id = $existingCliente->id;
                $retorno->nome = $existingCliente->nome;
                $retorno->documento = $existingCliente->documento;
                $retorno->endereco = $existingEndereco;
                $retorno->cliente_id = $existingCliente->id;
                return $retorno;
            } else {
                // Criar novo registro de endereço de cobrança
                $cobrancaEndereco = new CobrancaEndereco();
                $cobrancaEndereco->endereco = $endereco;
                $cobrancaEndereco->bairro = $bairro;
                $cobrancaEndereco->cidade = $cidade;
                $cobrancaEndereco->cep = $cep;
                $cobrancaEndereco->uf = $uf;
                $cobrancaEndereco->telefone = $telefone;
                $cobrancaEndereco->email = $email;
                $cobrancaEndereco->numero = $numero;
                $cobrancaEndereco->complemento = $complemento;
                $cobrancaEndereco->system_unit_id = $system_unit_id;
                $cobrancaEndereco->cliente_id = $existingCliente->id;
                $cobrancaEndereco->save();

                $retorno = new stdClass();
                $retorno->id = $existingCliente->id;
                $retorno->nome = $existingCliente->nome;
                $retorno->documento = $existingCliente->documento;
                $retorno->endereco = $cobrancaEndereco;
                $retorno->cliente_id = $existingCliente->id;
                return $retorno;
            }
        }

        // Caso contrário, criar um novo cliente e um novo endereço de cobrança
        $cobrancaTitulo = new MillCliente();
        $cobrancaTitulo->documento = $documento;
        $cobrancaTitulo->nome = $nome;
        $cobrancaTitulo->endereco = $endereco;
        $cobrancaTitulo->bairro = $bairro;
        $cobrancaTitulo->cidade = $cidade;
        $cobrancaTitulo->cep = $cep;
        $cobrancaTitulo->uf = $uf;
        $cobrancaTitulo->telefone = $telefone;
        $cobrancaTitulo->email = $email;
        $cobrancaTitulo->numero = $numero;
        $cobrancaTitulo->complemento = $complemento;
        $cobrancaTitulo->system_unit_id = $system_unit_id;
        $cobrancaTitulo->save();

        $cobrancaEndereco = new CobrancaEndereco();
        $cobrancaEndereco->endereco = $endereco;
        $cobrancaEndereco->bairro = $bairro;
        $cobrancaEndereco->cidade = $cidade;
        $cobrancaEndereco->cep = $cep;
        $cobrancaEndereco->uf = $uf;
        $cobrancaEndereco->telefone = $telefone;
        $cobrancaEndereco->email = $email;
        $cobrancaEndereco->numero = $numero;
        $cobrancaEndereco->complemento = $complemento;
        $cobrancaEndereco->system_unit_id = $system_unit_id;
        $cobrancaEndereco->cliente_id = $cobrancaTitulo->id;
        $cobrancaEndereco->save();

        $retorno = new stdClass();
        $retorno->id = $cobrancaTitulo->id;
        $retorno->nome = $cobrancaTitulo->nome;
        $retorno->documento = $cobrancaTitulo->documento;
        $retorno->endereco = $cobrancaEndereco;
        return $retorno;
    }

    public static function separarPrimeiroNome($nomeCompleto)
    {
        $partes = explode(' ', $nomeCompleto, 2);
        $primeiroNome = $partes[0];
        $restante = $partes[1] ?? '';
        return array($primeiroNome, $restante);
    }


    /**
     * Cria um UUID (Identificador Único Universal) baseado na versão 4.
     *
     * @param int $model O identificador do modelo.
     * @return string O UUID gerado.
     */
    public static function CreateUuid($model)
    {

        $data = random_bytes(16);

        // Set the version number (4) and variant bits
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC4122

        // Format the UUID as a string
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        return $uuid;
    }

    public static function BinarioFilesPDF($id, $system_unit_id, $parametros_bancos_id, $beneficiario_id, $linhaDigitavel)
    {

        // $pastaDestino = "documentos/pdf/sicredi/boleto/{$system_unit_id}/{$parametros_bancos_id}/{$beneficiario_id}/";
        // if (!is_dir($pastaDestino)) {
        //     mkdir($pastaDestino, 0777, true);
        // }

        // // Ler o conteúdo binário em formato de texto
        // $binario = $response; // Substitua pelo seu conteúdo binário em texto
        // $nomeArquivo = $pastaDestino . $parametros->linhaDigitavel . '.pdf';
        // $tipoConteudo = 'application/pdf';

        // // Converter o binário em um objeto de resposta do Laravel
        // $resposta = Response::make($binario, 200);
        // $resposta->header('Content-Type', $tipoConteudo);
        // $resposta->header('Content-Disposition', 'inline; filename="' . $nomeArquivo . '"');
        // $response = file_put_contents($nomeArquivo, $resposta);

        // // Criar uma nova instância do modelo MillEventosBoletos
        // $evento = new MillEventosBoletos();

        // // Preencher os campos do evento
        // $evento->linhaDigitavel = $linhaDigitavel;
        // $evento->caminho_pdf = $nomeArquivo;
        // $evento->parametros_bancos_id = $parametros_bancos_id;
        // $evento->system_unit_id = $system_unit_id;
        // $evento->cobranca_titulo_id = $id;
        // $evento->mensagem = 'Gerando PDF Boleto';
        // $evento->codigo = 300;

        // // Salvar o evento no banco de dados
        // $evento->save();
        // $Cobranca = MillCobrancaTitulo::find($id);
        // if ($Cobranca) {
        //     $Cobranca->caminho_boleto = $nomeArquivo;
        //     $Cobranca->save();
        // }
        // // Retornar os dados do evento cadastrado

        // return response()->json([
        //     'EventoBoleto' => [
        //         'mensagem' => "Caminho PDF",
        //         'caminho_pdf' => $nomeArquivo,

        //     ],
        // ], 200);

    }

    public static function calcularDigitoVerificador($primeirosDezDigitos)
    {
        // Obtém os 6 dígitos depois dos 10 primeiros (da esquerda para a direita)
        $seis_digitos_apos_dez = substr($primeirosDezDigitos, 10, 6);

        // Obtém o dígito da posição 17 (da esquerda para a direita)
        $digito_posicao_17 = $primeirosDezDigitos[16];

        // Formatação final
        $numero_formatado = $seis_digitos_apos_dez . '-' . $digito_posicao_17;
        return $numero_formatado; // Saída: 000043-8

    }

    public static function calcularDigitosVerificadoresBanrisul($numero)
    {
        // Verifica se o número possui 8 dígitos
        if (strlen($numero) !== 8) {
            throw new InvalidArgumentException('O número do Banrisul deve conter exatamente 8 dígitos.');
        }

        // Cálculo do primeiro dígito verificador (Módulo 10)
        $pesosModulo10 = array(1, 2, 1, 2, 1, 2, 1, 2);
        $somaModulo10 = 0;

        for ($i = 7; $i >= 0; $i--) {
            $produto = intval($numero[$i]) * $pesosModulo10[$i];
            $somaModulo10 += ($produto > 9) ? $produto - 9 : $produto;
        }

        $restoModulo10 = $somaModulo10 % 10;
        $primeiroDigitoVerificador = (10 - $restoModulo10) % 10;

        // Adiciona o primeiro dígito verificador ao número original
        $numero .= $primeiroDigitoVerificador;

        // Cálculo do segundo dígito verificador (Módulo 11)
        $pesosModulo11 = array(2, 3, 4, 5, 6, 7, 8, 9, 2);
        $somaModulo11 = 0;

        for ($i = 8; $i >= 0; $i--) {
            $somaModulo11 += intval($numero[$i]) * $pesosModulo11[$i];
        }

        $restoModulo11 = $somaModulo11 % 11;
        $segundoDigitoVerificador = (11 - $restoModulo11) % 10;

        // Retorna o número completo com os dígitos verificadores calculados
        return $numero . $segundoDigitoVerificador;
    }

    public static function formatDecimalToString($number) //

    {
        $maxLength = 16;
        $pattern = '/^-?\d{1,13}\.\d{2}$/';

        // Converte o número para uma string formatada
        $formattedNumber = number_format((float) $number, 2, '.', '');

        // Verifica se a string formatada está de acordo com o padrão
        if (preg_match($pattern, $formattedNumber)) {
            // Verifica o comprimento da string e ajusta, se necessário
            if (strlen($formattedNumber) <= $maxLength) {
                return $formattedNumber;
            } else {
                // Se o comprimento exceder maxLength, retorna uma string vazia ou uma mensagem de erro, como preferir
                return '';
            }
        } else {
            // Se o número não corresponder ao padrão, retorna uma string vazia ou uma mensagem de erro, como preferir
            return '';
        }
    }



    public static function GetRQCODEITAU($base64_string)
    {


        // Decodificar a string Base64 para dados binários
        $image_data = base64_decode($base64_string);

        // Caminho onde você deseja salvar a imagem decodificada
        $image_path = 'qr/qr.png';
        $pasta = 'qr';
        if (is_dir($pasta)) {
        } else {
            mkdir($pasta, 0777, true);
        }


        // Salvar os dados binários da imagem em um arquivo
        file_put_contents($image_path, $image_data);

        // Carregar a imagem original usando GD
        $original_image = imagecreatefrompng($image_path);

        // Obter as dimensões originais da imagem
        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);

        // Calcular o tamanho do corte (10% das bordas)
        $crop_percentage = 0.10;
        $crop_x = intval($original_width * $crop_percentage);
        $crop_y = intval($original_height * $crop_percentage);
        $crop_width = $original_width - 2 * $crop_x;
        $crop_height = $original_height - 2 * $crop_y;

        // Criar uma nova imagem cortada
        $cropped_image = imagecrop($original_image, ['x' => $crop_x, 'y' => $crop_y, 'width' => $crop_width, 'height' => $crop_height]);

        // Caminho onde você deseja salvar a imagem cortada
        $cropped_image_path = 'qr/imagem_cortada.png';

        // Salvar a imagem cortada em um arquivo
        imagepng($cropped_image, $cropped_image_path);

        // Liberar a memória alocada para as imagens
        imagedestroy($original_image);
        imagedestroy($cropped_image);

        // Exibir a imagem cortada

        return  $cropped_image_path;
    }


    public static function pfxToBase64($pfxFilePath)
    {

        // Carrega o conteúdo do arquivo .pfx
        $pfxData = file_get_contents($pfxFilePath);

        // Converte o arquivo .pfx para base64
        $base64Data = base64_encode($pfxData);


        return  $base64Data;
    }
}
