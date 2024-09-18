<?php
require __DIR__ . '../../vendor/autoload.php';

use \EvLimma\PdfModifier\PdfModifier;

try {
    $pdfModifier = new PdfModifier('DocumentoPDF.pdf', 'Helvetica', 12);
    
    $pdfModifier->addText(1, 10, 10, 'Texto na página 1 com fonte padrão. x=10, y=10');
    $pdfModifier->addText(1, 10, 50, 'Texto com Arial, tamanho 16', 'Arial', 16);
    $pdfModifier->addText(2, 10, 10, 'Texto com Courier, tamanho 10', 'Courier', 10);
    
    // Abre o arquivo PDF modificado
    $pdfModifier->outputPdf('I', 'pdf_com_fontes_variadas.pdf');  
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}