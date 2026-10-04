<?php

namespace App\Infrastructure\DocumentExtraction;

use App\Application\Contracts\DocumentTextExtractorInterface;
use PhpOffice\PhpWord\IOFactory;

class DocxTextExtractor implements DocumentTextExtractorInterface
{
    public function supports(string $mimeType): bool
    {
        return $mimeType ===
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }

    public function extract(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);

        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . PHP_EOL;
                }
            }
        }

        return trim($text);
    }
}