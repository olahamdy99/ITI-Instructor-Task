<?php

namespace App\Services;

use App\Infrastructure\DocumentExtraction\DocumentTextExtractorResolver;
use App\Models\CandidateDocument;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DocumentProcessingService
{
    public function __construct(
        private DocumentTextExtractorResolver $extractorResolver
    ) {
    }

    public function process(CandidateDocument $document): string
    {
        try {
            $document->update([
                'processing_status' => 'processing',
                'failure_reason' => null,
            ]);


            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            $fullPath = $disk->path($document->file_path);
            
            // $fullPath = Storage::disk('local')->path(
            //     $document->file_path
            // );

            $extractor = $this->extractorResolver->resolve(
                $document->mime_type
            );

            $text = $extractor->extract($fullPath);

            if (trim($text) === '') {
                throw new \RuntimeException(
                    'No extractable text was found in the document.'
                );
            }

            $document->update([
                'processing_status' => 'processed',
                'failure_reason' => null,
            ]);

            return $text;
        } catch (Throwable $exception) {
            $document->update([
                'processing_status' => 'failed',
                'failure_reason' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}