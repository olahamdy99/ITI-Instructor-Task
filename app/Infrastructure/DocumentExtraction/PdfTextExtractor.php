<?php

namespace App\Infrastructure\DocumentExtraction;

use App\Application\Contracts\DocumentTextExtractorInterface;
use Smalot\PdfParser\Parser;

class PdfTextExtractor implements DocumentTextExtractorInterface
{
    public function supports(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    public function extract(string $filePath): string
    {
        $parser = new Parser();

        $pdf = $parser->parseFile($filePath);

        return trim($pdf->getText());
    }
}