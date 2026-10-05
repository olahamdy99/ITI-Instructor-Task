<?php

namespace App\Services;

class DocumentTextCleaner
{
    public function clean(string $text): string
    {
        // Normalize line endings.
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Replace tabs with spaces.
        $text = str_replace("\t", ' ', $text);

        // Collapse multiple spaces into one.
        $text = preg_replace('/[ ]{2,}/', ' ', $text);

        // Remove excessive empty lines.
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Trim spaces around each line.
        $lines = array_map(
            fn ($line) => trim($line),
            explode("\n", $text)
        );

        $text = implode("\n", $lines);

        return trim($text);
    }
}