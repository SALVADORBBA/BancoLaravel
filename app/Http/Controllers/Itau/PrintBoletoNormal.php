<?php

namespace App\Http\Controllers\itau;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Http\Controllers\Controller;
use App\Models\ContasReceber;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
class PrintBoletoNormal extends Controller
{
    public function create(Request $request)
    {

    $titulos = ContasReceber::find($request->id);
//composer require picqer/php-barcode-generator

        // Recebe os dados do request ou define dados fixos para o boleto
        $logo_banco = 'https://banco.developerapi.com.br/download.php?file=logo/bancos/4/logo-tau.jpg';
        $resultado = '341-7';
        $numero = $titulos->linhadigitavel;
        $data_vencimento = '15/02/2025';
        $Cedente = 'Nome do Cedente';
        $cnpj = '12345678000199';
        $dadosbanco = '1234/12345';
        $DataDoDoc = '15/02/2025';
        $NumeroDodoc = '123456';
        $especie = 'R$';
        $DataDoProces = '15/02/2025';
        $digito_verificador = 'X123456';
        $ValorDocumento = $titulos->valor;
        $info = 'Informações importantes sobre o boleto.';
        $nome = 'Nome do Pagador';
        $CpfDoSacado = '123.456.789-00';
        $RuaNumeroBairro = 'Rua Exemplo, 123 - Bairro Exemplo';
        $CidadeUf = 'Cidade Exemplo - UF';
        $CEP = '12345-678';
        $instrucoes = 'Instruções para o pagamento do boleto.';
 
        $img = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logo_banco));
        $image_data = file_get_contents("https://banco.developerapi.com.br/logo/bancos/4/logo-tau.jpg");
        $image_base64 = base64_encode($image_data);
        $img = 'data:image/jpeg;base64,' . $image_base64;

        $barra = $titulos->codigobarras;  // Pode vir de $titulos->codigobarras

        // Gerar o código de barras
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($barra, $generator::TYPE_CODE_128);
        
        // Salvar o código de barras em um arquivo temporário
        $tempBarcodePath = storage_path('app/public/barcode.png');
        file_put_contents($tempBarcodePath, $barcode);
        
        // Ler a imagem gerada e converter para base64
        $image_data = file_get_contents($tempBarcodePath);
        $image_base64 = base64_encode($image_data);
        
        // Criar a string base64 com o prefixo de imagem
        $img_barras = 'data:image/png;base64,' . $image_base64;
  
       
        $path =    storage_path('app/public/itau');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
          
        }
        $fileName=$barra;
        $qrCodeContent = '00020101021226920014br.gov.bcb.pix2570qrcodepix-h.bb.com.br/pix/v2/cobv/988b5a2a-3ebd-4ba1-8a2b-b90ad989d9a3520400005303986540535.875802BR5919PADARIA PESSOA ROSA6008BRASILIA62070503***63048E6B';
        $qrCode = new QrCode($qrCodeContent);
        $qrCode->setSize(150);
        
        $writer = new PngWriter();

        $filePath = $path . $fileName . '.png';
        $writer->write($qrCode)->saveToFile($filePath);

   

   // Ler a imagem gerada e converter para base64
   $image_data_qr = file_get_contents($filePath);
   $image_base64_qr = base64_encode($image_data_qr);
   
   // Criar a string base64 com o prefixo de imagem
   $img_qr = 'data:image/png;base64,' . $image_base64_qr;


     
        
        
        // Criando o conteúdo HTML do boleto
  $html = "<style>td.BoletoCodigoBanco {
    font-size: 6mm;
    font-family: arial, verdana;
    font-weight: bold;
    FONT-STYLE: italic;
    text-align: center;
    vertical-align: bottom;
    border-bottom: 0.15mm solid #000000;
    border-right: 0.15mm solid #000000;
    padding-bottom: 1mm;
    margin-top: 2mm;
}

td.BoletoLogo {
    border-bottom: 0.15mm solid #000000;
    border-right: 0.15mm solid #000000;
    text-align: center;
    height: 10mm
}

td.BoletoLinhaDigitavel {
    font-size: 4mm;
    font-family: arial, verdana;
    font-weight: bold;
    text-align: center;
    vertical-align: bottom;
    border-bottom: 0.15mm solid #000000;
    padding-bottom: 2mm;
}

td.BoletoTituloEsquerdo {
    font-size: 0.2cm;
    font-family: arial, verdana;
    padding-left: 0.15mm;
    border-right: 0.15mm solid #000000;
    text-align: left
}

td.BoletoTituloDireito {
    font-size: 2mm;
    font-family: arial, verdana;
    padding-left: 0.15mm;
    text-align: left
}

td.BoletoValorEsquerdo {
    font-size: 3mm;
    font-family: arial, verdana;
    text-align: center;
    border-right: 0.15mm solid #000000;
    font-weight: bold;
    border-bottom: 0.15mm solid #000000;
    padding-top: 0.5mm
}

td.BoletoValorDireito {
    font-size: 3mm;
    font-family: arial, verdana;
    text-align: right;
    padding-right: 3mm;
    padding-top: 0.8mm;
    border-bottom: 0.15mm solid #000000;
    font-weight: bold;
}

td.BoletoTituloSacado {
    font-size: 2mm;
    font-family: arial, verdana;
    padding-left: 0.15mm;
    vertical-align: top;
    padding-top: 0.15mm;
    text-align: left
}

td.BoletoValorSacado {
    font-size: 3mm;
    font-family: arial, verdana;
    font-weight: bold;
    text-align: left
}

td.BoletoTituloSacador {
    font-size: 2mm;
    font-family: arial, verdana;
    padding-left: 0.15mm;
    vertical-align: bottom;
    padding-bottom: 0.8mm;
    border-bottom: 0.15mm solid #000000
}

td.BoletoValorSacador {
    font-size: 3mm;
    font-family: arial, verdana;
    vertical-align: bottom;
    padding-bottom: 0.15mm;
    border-bottom: 0.15mm solid #000000;
    font-weight: bold;
    text-align: left
}

td.BoletoPontilhado {
    border-bottom: 0.1mm solid #626161;
    /* Ajuste a largura e a cor dos pontos conforme necessÃ¡rio */
    font-size: 0.01px;
    /* Ajuste o tamanho da fonte para reduzir o espaÃ§amento */
    /* margin-top: 1px;
    margin-bottom: 10px; */
    padding-bottom: 10px;
    padding-top: 5px;
}

.BoletoPontilhado {
    border-bottom: 0.1mm solid #626161;
    /* Ajuste a largura e a cor dos pontos conforme necessÃ¡rio */
    font-size: 0.01px;
    /* Ajuste o tamanho da fonte para reduzir o espaÃ§amento */
    /* margin-top: 1px;
    margin-bottom: 10px; */
    padding-bottom: 10px;
    padding-top: 5px;
}

ul.BoletoInstrucoes {
    font-size: 3mm;
    font-family: verdana, arial
}

.creditos {


    font-size: 3mm;
    font-family: arial, verdana;
    margin-top: 2px;
    padding-top: 3px;
    text-align: right;
}

.ambiente {



    color: #9e1a1a;

}

.Pontilhado {
    border-bottom: 0.1mm dashed #626161;
    /* Ajuste a largura e a cor dos pontos conforme necessÃ¡rio */
    font-size: 0.01px;
    /* Ajuste o tamanho da fonte para reduzir o espaÃ§amento */
    /* margin-top: 1px;
   margin-bottom: 10px; */
    padding-bottom: 2px;
    padding-top: 2px;
}</style>";

 $html .= " 
 

    <TABLE cellSpacing=0 cellPadding=0 border=0 class=Boleto>

        <TR>
            <TD style='width: 0.9cm'></TD>
            <TD style='width: 1cm'></TD>
            <TD style='width: 1.9cm'></TD>

            <TD style='width: 0.5cm'></TD>
            <TD style='width: 1.3cm'></TD>
            <TD style='width: 0.8cm'></TD>
            <TD style='width: 1cm'></TD>

            <TD style='width: 1.9cm'></TD>
            <TD style='width: 1.9cm'></TD>

            <TD style='width: 3.8cm'></TD>

            <TD style='width: 3.8cm'> </TD>

        <tr>
            <td colspan=11>
<table style='width: 100%; border-collapse: collapse;'>
    <tr>
        <!-- Coluna do texto -->
        <td style='width: calc(100% - 150px); text-align: left; padding-right: 1cm; vertical-align: top;'>
            <h3 style='color: green;'>Pague agora via PIX, basta acessar o aplicativo de sua instituição financeira</h3>
        </td>

        <!-- Coluna do QR Code -->
        <td style='width: 150px; text-align: right; vertical-align: top;'>
            <img src= $img_qr alt='QR Code' style='max-width: 100%; height: auto;'>
        </td>
    </tr>
</table>


                <table border='0' cellpadding='1' cellspacing='1' style='width:100%'>
                    <tbody>
                        <tr>
                            <td class=Pontilhado></td>
                        </tr>
                    </tbody>
                </table>

            </td>
        </tr>
        </TR>
        <tr>
            <td colspan=11 class=BoletoPontilhado>&nbsp;</td>
        </tr>
        <TR>
            <TD colspan=4 class=BoletoLogo><img src='$img' width='150'>
            </TD>
            <TD colspan=2 class=BoletoCodigoBanco> $resultado </TD>
            <TD colspan=6 class=Boletolinhadigitavel>$numero</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo>Local de Pagamento</TD>
            <TD class=BoletoTituloDireito>Vencimento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'> ATÉ O VENC.
                PREFERENCIALMENTE NO BANCO DO ITAU
            </TD>
            <TD class=BoletoValorDireito>$data_vencimento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo> Nome do Beneficários</TD>
            <TD class=BoletoTituloDireito>Agência/Código do Beneficário</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>$Cedente CNPJ: $cnpj
            </TD>
            <TD class=BoletoValorDireito>$dadosbanco</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoTituloEsquerdo>Data do Documento</TD>
            <TD colspan=4 class=BoletoTituloEsquerdo>Número do Documento</TD>
            <TD class=BoletoTituloEsquerdo>Espécie</TD>
            <TD class=BoletoTituloEsquerdo>Aceite</TD>
            <TD class=BoletoTituloEsquerdo>Data do Processamento</TD>
            <TD class=BoletoTituloDireito>Nosso Número</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoValorEsquerdo>$DataDoDoc</TD>
            <TD colspan=4 class=BoletoValorEsquerdo>$NumeroDodoc</TD>
            <TD class=BoletoValorEsquerdo>$especie</TD>
            <TD class=BoletoValorEsquerdo>N</TD>
            <TD class=BoletoValorEsquerdo>$DataDoProces</TD>
            <TD class=BoletoValorDireito>$digito_verificador </TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoTituloEsquerdo>Uso do Banco</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Carteira</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Moeda</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Quantidade</TD>
            <TD class=BoletoTituloEsquerdo>(x) Valor</TD>
            <TD class=BoletoTituloDireito>(=) Valor do Documento</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD colspan=2 class=BoletoValorEsquerdo> 109 </TD>
            <TD colspan=2 class=BoletoValorEsquerdo>R$</TD>
            <TD colspan=2 class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD class=BoletoValorDireito>$ValorDocumento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo>Instruções</TD>
            <TD class=BoletoTituloDireito>(-) Desconto</TD>
        </TR>
        <TR>
          <td colspan=\"10\" rowspan=\"9\" class=\"BoletoValorEsquerdo\"
    style=\"text-align: left; vertical-align: top; padding-left: 0.1cm;\">

  sasa
</td>

            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(-) Outras Deduções/Abatimento</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(+) Mora/Multa/Juros</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(+) Outros Acréscimos</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(=) Valor Cobrado</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD rowspan=3 Class=BoletoTituloSacado>Pagador: </TD>
            <TD colspan=8 Class=BoletoValorSacado>$nome</TD>
            <TD colspan=2 Class=BoletoValorSacado>$CpfDoSacado</TD>
        </TR>
        <TR>
            <TD colspan=10 Class=BoletoValorSacado>$RuaNumeroBairro</TD>
        </TR>
        <TR>
            <TD colspan=10 Class=BoletoValorSacado>$CidadeUf&nbsp;&nbsp;&nbsp;$CEP</TD>
        </TR>
        <TR>
            <TD colspan=2 Class=BoletoTituloSacador>Pagador / Avalista:</TD>
            <TD colspan=9 Class=BoletoValorSacador>...</TD>
        </TR>
        <TR>
            <TD colspan=11 class=BoletoTituloDireito style='text-align: right; padding-right: 0.1cm'>Recibo do Pagador -
                Autenticação Mecânica</TD>
        </TR>
 
        <tr>

            <td colspan=11 class=BoletoPontilhado>&nbsp;</td>
        </tr>
        <TR>
            <TD colspan=4 class=BoletoLogo><img src='$img' width=' 150'>
            </TD>
            <TD colspan=2 class=BoletoCodigoBanco> $resultado</TD>
            <TD colspan=6 class=Boletolinhadigitavel>$numero</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo>Local de Pagamento</TD>
            <TD class=BoletoTituloDireito>Vencimento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'> ATÉ O VENC.
                PREFERENCIALMENTE NO BANCO DO ITAU
            </TD>
            <TD class=BoletoValorDireito>$data_vencimento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo>Nome do Beneficário</TD>
            <TD class=BoletoTituloDireito>Agência/Código do Beneficário</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>$Cedente CNPJ: $cnpj
            </TD>
            <TD class=BoletoValorDireito>$dadosbanco</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoTituloEsquerdo>Data do Documento</TD>
            <TD colspan=4 class=BoletoTituloEsquerdo>Número do Documento</TD>
            <TD class=BoletoTituloEsquerdo>Espécie</TD>
            <TD class=BoletoTituloEsquerdo>Aceite</TD>
            <TD class=BoletoTituloEsquerdo>Data do Processamento</TD>
            <TD class=BoletoTituloDireito>Nosso Número</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoValorEsquerdo>$DataDoDoc</TD>
            <TD colspan=4 class=BoletoValorEsquerdo>$NumeroDodoc</TD>
            <TD class=BoletoValorEsquerdo>$especie</TD>
            <TD class=BoletoValorEsquerdo>N</TD>
            <TD class=BoletoValorEsquerdo>$DataDoProces</TD>
            <TD class=BoletoValorDireito>$digito_verificador </TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoTituloEsquerdo>Uso do Banco</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Carteira</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Moeda</TD>
            <TD colspan=2 class=BoletoTituloEsquerdo>Quantidade</TD>
            <TD class=BoletoTituloEsquerdo>(x) Valor</TD>
            <TD class=BoletoTituloDireito>(=) Valor do Documento</TD>
        </TR>
        <TR>
            <TD colspan=3 class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD colspan=2 class=BoletoValorEsquerdo> 109 </TD>
            <TD colspan=2 class=BoletoValorEsquerdo>R$</TD>
            <TD colspan=2 class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD class=BoletoValorEsquerdo>&nbsp;</TD>
            <TD class=BoletoValorDireito>$ValorDocumento</TD>
        </TR>
        <TR>
            <TD colspan=10 class=BoletoTituloEsquerdo>Instruções</TD>
            <TD class=BoletoTituloDireito>(-) Desconto</TD>
        </TR>
        <TR>
            <TD colspan=10 rowspan=9 class=BoletoValorEsquerdo
                style='text-align: left; vertical-align:top; padding-left : 0.1cm'> $info</TD>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(-) Outras Deduções/Abatimento</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(+) Mora/Multa/Juros</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(+) Outros Acréscimos</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD class=BoletoTituloDireito>(=) Valor Cobrado</TD>
        </TR>
        <TR>
            <TD class=BoletoValorDireito>&nbsp;</TD>
        </TR>
        <TR>
            <TD rowspan=3 Class=BoletoTituloSacado>Pagador: </TD>
            <TD colspan=8 Class=BoletoValorSacado>$nome</TD>
            <TD colspan=2 Class=BoletoValorSacado>$CpfDoSacado</TD>
        </TR>
        <TR>
            <TD colspan=10 Class=BoletoValorSacado>$RuaNumeroBairro</TD>
        </TR>
        <TR>
            <TD colspan=10 Class=BoletoValorSacado>$CidadeUf&nbsp;&nbsp;&nbsp;$CEP</TD>
        </TR>
        <TR>
            <TD colspan=2 Class=BoletoTituloSacador>Pagador / Avalista:</TD>
            <TD colspan=9 Class=BoletoValorSacador>...</TD>
        </TR>
        <TR>
            <TD colspan=11 class=BoletoTituloDireito style='text-align: right; padding-right: 0.1cm'>Ficha de
                Compensação - Autenticação Mecânica</TD>
        </TR>
        <TR>
            <TD colspan=11 height=60 valign=top> <CENTER>    <img src='$img_barras' >$barra</CENTER></TD>
        </TR>
        <tr>
            <td colspan=11 class=Pontilhado> </td>

        </tr>

    </TABLE>

 ";
 
 
   // Carregar as configurações do DomPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true); // Habilitar PHP se necessário

$dompdf = new Dompdf($options);

// Carregar o HTML no DomPDF
$dompdf->loadHtml($html);

// Definir o tamanho do papel (A4 por padrão)
$dompdf->setPaper('A4', 'portrait');

// Renderizar o PDF (sem saída para o navegador)
$dompdf->render();

// Enviar o PDF gerado para o navegador
$dompdf->stream("boleto.pdf", array("Attachment" => 0));  // 'Attachment' => 0 faz com que o PDF seja mostrado no navegador, 1 força o download

    }
}
