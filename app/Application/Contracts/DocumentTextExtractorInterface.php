<?php

namespace App\Application\Contracts;

interface DocumentTextExtractorInterface
{
    public function supports(string $mimeType): bool;

    public function extract(string $filePath): string;
}