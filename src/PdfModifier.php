<?php

namespace EvLimma\PdfModifier;

use setasign\Fpdi\Fpdi;

class PdfModifier
{
    private $pdf;
    private $pageCount;
    private $defaultFont;
    private $defaultFontSize;
    private $defaultMaxWidth;
    private $defaultLineHeight;
    private $defaultMaxLines;
    private $defaultBorder;
    private $limitText;

    /**
     * Construtor para inicializar o FPDI, carregar o PDF e definir fonte e tamanho
     *
     * @param string $sourcePdf
     * @param string|null $font
     * @param integer|null $fontSize
     * @param integer|null $maxWidth
     * @param integer|null $lineHeight
     * @param integer|null $maxLines
     * @param integer|null $border
     */
    public function __construct(
        string $sourcePdf,
        ?string $font = 'Helvetica',
        ?int $fontSize = 12,
        ?int $maxWidth = 0,
        ?int $lineHeight = 5,
        ?int $maxLines = null,
        ?int $border = 0,
        ?string $limitText = ' (...)&#10;&#10;(...) * Veja o restante do conteúdo no sistema que gerou este pdf.'
    ) {
        $this->pdf = new Fpdi();
        $this->pageCount = $this->pdf->setSourceFile($sourcePdf);
        $this->defaultFont = $font;
        $this->defaultFontSize = $fontSize;
        $this->defaultMaxWidth = $maxWidth;
        $this->defaultLineHeight = $lineHeight;
        $this->defaultMaxLines = $maxLines;
        $this->defaultBorder = $border;
        $this->limitText = $limitText;
    }

    /**
     * Função para adicionar texto com opção de sobrescrever a fonte e o tamanho
     *
     * @param integer $pageNo
     * @param float $x
     * @param float $y
     * @param string $text
     * @param string|null $font
     * @param integer|null $fontSize
     * @param integer|null $maxWidth
     * @param integer|null $lineHeight
     * @param integer|null $maxLines
     * @param integer|null $border
     * @return void
     */
    public function addText(
        int $pageNo,
        float $x,
        float $y,
        string $text,
        ?string $font = null,
        ?int $fontSize = null,
        ?int $maxWidth = null,
        ?int $lineHeight = null,
        ?int $maxLines = null,
        ?int $border = null
    ): void {
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
            $currentMaxWidth = $maxWidth ?? $this->defaultMaxWidth;
            $currentLineHeight = $lineHeight ?? $this->defaultLineHeight;
            $currentMaxLines = $maxLines ?? $this->defaultMaxLines;
            $currentBorder = $border ?? $this->defaultBorder;

            // Define a fonte e o tamanho do texto
            $this->pdf->SetFont($currentFont, '', $currentFontSize);

            // Define a posição do texto
            $this->pdf->SetXY($x, $y);

            $text = $this->convertDecode($text);

            // Adiciona o texto na página
            if ($currentMaxLines) {
                $this->LimitedMultiCell($currentMaxWidth, $currentLineHeight, $text, $currentBorder, $currentMaxLines);
                return;
            }
            
            //$this->pdf->Cell(179, 10, mb_convert_encoding($text,"Windows-1252","UTF-8"));
            $this->pdf->MultiCell($currentMaxWidth, $currentLineHeight, $text, $currentBorder);
        } else {
            throw new \Exception("Número de página inválido: $pageNo");
        }
    }

    /**
     * Dividir o texto original pelas quebras de linha existentes
     *
     * @param [type] $w
     * @param [type] $h
     * @param [type] $txt
     * @param [type] $border
     * @param [type] $maxLines
     * @return void
     */
    function LimitedMultiCell($w, $h, $txt, $border, $maxLines): void
    {
        $paragraphs = explode("\n", $txt);
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $wrappedLines = $this->wrapText($paragraph, $w);
            $lines = array_merge($lines, $wrappedLines);

            if (count($lines) >= $maxLines) {
                break;
            }
        }

        $limitedLines = array_slice($lines, 0, $maxLines);
        $outputText = implode("\n", $limitedLines);

        if (strlen($txt) !== strlen($outputText)) {
            $outputText .= $this->convertDecode($this->limitText);
        }

        $this->pdf->MultiCell($w, $h, $outputText, $border);
    }

    /**
     * Calcula a largura da linha atual com a palavra adicionada
     *
     * @param [type] $text
     * @param [type] $width
     * @return mixed
     */
    function wrapText($text, $width): mixed
    {
        $lines = [];
        $words = explode(' ', $text);
        $currentLine = '';

        foreach ($words as $word) {
            $lineWidth = $this->pdf->GetStringWidth(trim($currentLine . ' ' . $word));

            if ($lineWidth <= $width) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * Ajustar acentos e quebra de linha
     *
     * @param string|null $text
     * @return string|null
     */
    private function convertDecode(?string $text): ?string
    {
        if (!$text) {
            return false;
        }

        $text = mb_convert_encoding($text, "Windows-1252", "UTF-8");
        $text = html_entity_decode($text);
        //$text = str_replace("\r", "", $text);

        return $text;
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
