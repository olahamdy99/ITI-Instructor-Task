<?php

namespace App\Services;

use App\Infrastructure\DocumentExtraction\DocumentTextExtractorResolver;
use App\Models\CandidateDocument;
use App\Services\DocumentChunker;
use App\Services\DocumentTextCleaner;
use App\Application\Contracts\EmbeddingProviderInterface;   
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DocumentProcessingService
{
    public function __construct(
        private DocumentTextExtractorResolver $extractorResolver,
        private DocumentTextCleaner $textCleaner,
        private DocumentChunker $documentChunker,
        private EmbeddingProviderInterface $embeddingProvider
    ) {
    }

    public function process(CandidateDocument $document): array
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

            $cleanText = $this->textCleaner->clean($text);

            $chunks = $this->documentChunker->chunk(
                $cleanText
            );

            $document->chunks()->delete();

            foreach ($chunks as $chunk) { 
                $embedding = $this->embeddingProvider->embed( $chunk['content'] );
                $documentChunk = $document->chunks()->create([
                    'chunk_index' => $chunk['chunk_index'],
                    'content' => $chunk['content'],
                    'page_number' => null,
                    'metadata' => [
                        'mime_type' => $document->mime_type,
                        'original_name' => $document->original_name,
                        'embedding_provider' => 'ollama',
                        'embedding_model' => 'nomic-embed-text',
                        'embedding_dimensions' => $this->embeddingProvider->dimensions(),
                    ],
                ]);

            $vector = '[' . implode(',', $embedding) . ']';

            DB::statement(
                'UPDATE document_chunks
                SET embedding = CAST(? AS vector)
                WHERE id = ?',
                [
                    $vector,
                    $documentChunk->id,
                ]
            );
            }

            $document->update([
                'processing_status' => 'processed',
                'failure_reason' => null,
            ]);

            return $chunks;
        } catch (Throwable $exception) {
            $document->update([
                'processing_status' => 'failed',
                'failure_reason' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}