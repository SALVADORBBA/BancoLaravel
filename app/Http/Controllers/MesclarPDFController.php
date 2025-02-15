<?php

namespace App\Http\Controllers;

 
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
class MesclarPDFController extends Controller
{
     
 
        public function mesclar(Request $request)
        {
            // Validar a entrada
            $request->validate([
                'primeiroPDF' => 'required|string',
                'segundoPDF'  => 'required|string',
                'nameFile'    => 'required|string',
            ]);
    
            // Obter os caminhos dos arquivos PDF enviados
            $primeiroPDF = storage_path("app/public/" . $request->primeiroPDF);
            $segundoPDF  = storage_path("app/public/" . $request->segundoPDF);
            $nameFile    = $request->nameFile;
    
            // Verificar se os arquivos existem
            if (!file_exists($primeiroPDF) || !file_exists($segundoPDF)) {
                return response()->json(['error' => 'Um ou mais arquivos não foram encontrados.'], 404);
            }
    
            // Pasta de destino
            $pastaDestino = storage_path("app/public/documentos/pdf/Mesclagem/boleto");
    
            // Criar a pasta, se não existir
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }
    
            // Nome do arquivo final
            $nomeArquivoFinal = $pastaDestino . '/' . $nameFile . '.pdf';
    
            // Mesclar PDFs
            $resultado = $this->mergePDFs([$primeiroPDF, $segundoPDF], $nomeArquivoFinal);
    
            if (is_string($resultado)) {
                return response()->json(['error' => $resultado], 500);
            }
    
            return response()->json([
                'success' => true,
                'file_url' => asset("storage/documentos/pdf/Mesclagem/boleto/{$nameFile}.pdf")
            ]);
        }
    
        private function mergePDFs($arquivosPDF, $nomeArquivoFinal)
        {
            $pdf = new Fpdi();
    
            // Tamanho da página (80 colunas, altura ajustável)
            $largura80Colunas = 80;
            $altura = 200;
    
            foreach ($arquivosPDF as $arquivo) {
                if (!file_exists($arquivo)) {
                    return "Arquivo não encontrado: $arquivo";
                }
    
                $pageCount = $pdf->setSourceFile($arquivo);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplIdx = $pdf->importPage($i);
                    $pdf->AddPage('P', [$largura80Colunas, $altura]);
                    $pdf->useTemplate($tplIdx);
                }
            }
    
            try {
                $pdf->Output($nomeArquivoFinal, 'F'); // Salvar arquivo
            } catch (\Exception $e) {
                return "Erro ao salvar o PDF: " . $e->getMessage();
            }
    
            return true;
        }
    }
    