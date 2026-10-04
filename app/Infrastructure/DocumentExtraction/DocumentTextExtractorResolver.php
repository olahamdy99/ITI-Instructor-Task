<?php

namespace App\Infrastructure\DocumentExtraction;

use App\Application\Contracts\DocumentTextExtractorInterface;
use RuntimeException;

class DocumentTextExtractorResolver
{
    /**
     * @param DocumentTextExtractorInterface[] $extractors
     */
    public function __construct(
        private array $extractors
    ) {
    }

    public function resolve(string $mimeType): DocumentTextExtractorInterface
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($mimeType)) {
                return $extractor;
            }
        }

        throw new RuntimeException(
            "Unsupported document mime type: {$mimeType}"
        );
    }
}