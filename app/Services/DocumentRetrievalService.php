<?php

namespace App\Services;

use App\Application\Contracts\EmbeddingProviderInterface;
use Illuminate\Support\Facades\DB;

class DocumentRetrievalService
{
    public function __construct(
        private EmbeddingProviderInterface $embeddingProvider
    ) {
    }

    public function search(
        string $query,
        int $candidateDocumentId,
        int $limit = 5
    ): array {
        $embedding = $this->embeddingProvider->embed($query);
        $vector = '[' . implode(',', $embedding) . ']';
        return DB::select(
            '
       SELECT
           id,
           candidate_document_id,
           chunk_index,
           content,
           metadata,
           embedding <=> CAST(? AS vector) AS distance
       FROM document_chunks
       WHERE embedding IS NOT NULL
         AND candidate_document_id = ?
       ORDER BY embedding <=> CAST(? AS vector)
       LIMIT ?
       ',
            [
                $vector,
                $candidateDocumentId,
                $vector,
                $limit,
            ]
        );
    }
}