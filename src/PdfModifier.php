<?php

namespace EvLimma\PdfModifier;

use setasign\Fpdi\Fpdi;

class PdfModifier
{
    private $pdf;
    private $pageCount;
    private $defaultFont;
    private $defaultFontSize;

    /**
     * Construtor para inicializar o FPDI, carregar o PDF e definir fonte e tamanho
     *
     * @param [type] $sourcePdf
     * @param string $font
     * @param integer $fontSize
     */
    public function __construct($sourcePdf, $font = 'Helvetica', $fontSize = 12)
    {
        $this->pdf = new Fpdi();
        $this->pageCount = $this->pdf->setSourceFile($sourcePdf);
        $this->defaultFont = $font;
        $this->defaultFontSize = $fontSize;
    }

    /**
     * Função para adicionar texto com opção de sobrescrever a fonte e o tamanho
     *
     * @param [type] $pageNo
     * @param [type] $x
     * @param [type] $y
     * @param [type] $text
     * @param [type] $font
     * @param [type] $fontSize
     * @return void
     */
    public function addText($pageNo, $x, $y, $text, $font = null, $fontSize = null)
    {
        // Verifica se a página já foi carregada e se o número da página é válido
        if ($pageNo > 0 && $pageNo <= $this->pageCount) {
            // Importa a página específica apenas uma vez
            static $importedPages = [];
            if (!isset($importedPages[$pageNo])) {
                $importedPages[$pageNo] = $this->pdf->importPage($pageNo);
                $this->pdf->AddPage();
                $this->pdf->useTemplate($importedPages[$pageNo]);
            }

            // Usa a fonte e o tamanho fornecidos ou o padrão do construtor
            $currentFont = $font ?? $this->defaultFont;
            $currentFontSize = $fontSize ?? $this->defaultFontSize;
            
            // Define a fonte e o tamanho do texto
            $this->pdf->SetFont($currentFont, '', $currentFontSize);
            
            // Define a posição do texto
            $this->pdf->SetXY($x, $y);
            
            // Adiciona o texto na página
            $this->pdf->Cell(0, 10, mb_convert_encoding($text,"Windows-1252","UTF-8"));
        } else {
            throw new \Exception("Número de página inválido: $pageNo");
        }
    }

    /**
     * Função para salvar ou exibir o PDF modificado
     *
     * @param string $outputPath    - 'F' para salvar no servidor; 'I' para abrir no navegador
     * @param string $fileName
     * @return void
     */
    public function outputPdf($outputPath = 'I', $fileName = 'arquivo_modificado.pdf')
    {
        $this->pdf->Output($outputPath, $fileName);
    }
}

