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
        $semanticResults = DB::select(
            '
       SELECT
           id,
           candidate_document_id,
           chunk_index,
           content,
           page_number,
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

        return $semanticResults;
    }

    public function keywordSearch(
    string $query,
    int $candidateDocumentId,
    int $limit = 5
    ): array {
        return DB::select(
            '
            SELECT
                id,
                candidate_document_id,
                chunk_index,
                content,
                page_number,
                metadata,
                ts_rank_cd(
                    to_tsvector(\'simple\', content),
                    plainto_tsquery(\'simple\', ?)
                ) AS keyword_score
            FROM document_chunks
            WHERE candidate_document_id = ?
            AND to_tsvector(\'simple\', content)
                @@ plainto_tsquery(\'simple\', ?)
            ORDER BY keyword_score DESC
            LIMIT ?
            ',
            [
                $query,
                $candidateDocumentId,
                $query,
                $limit,
            ]
        );
    }

    public function hybridSearch( string $query, int $candidateDocumentId, int $limit = 5 ): array { $candidateLimit = max($limit * 3, 10);
    $semanticResults = $this->search(
        $query,
        $candidateDocumentId,
        $candidateLimit
    );

    $keywordResults = $this->keywordSearch(
        $query,
        $candidateDocumentId,
        $candidateLimit
    );

    $scores = [];
    $documents = [];

    $k = 60;

    foreach ($semanticResults as $rank => $result) {
        $id = $result->id;

        $scores[$id] = ($scores[$id] ?? 0)
            + 1 / ($k + $rank + 1);

        $documents[$id] = $result;
    }

    foreach ($keywordResults as $rank => $result) {
        $id = $result->id;

        $scores[$id] = ($scores[$id] ?? 0)
            + 1 / ($k + $rank + 1);

        $documents[$id] = $result;
    }

    arsort($scores);

    $results = [];

    foreach (
        array_slice($scores, 0, $limit, true)
        as $id => $score
    ) {
        $document = $documents[$id];

        $metadata = is_string($document->metadata)? json_decode($document->metadata, true): $document->metadata;
        $metadata = is_array($metadata) ? $metadata : [];
        $results[] = [
            'id' => $document->id,
            'candidate_document_id' => $document->candidate_document_id,
            'chunk_index' => $document->chunk_index,
            'content' => $document->content,
            'metadata' => $document->metadata,
            'fusion_score' => $score,

            'citation' => [
                'source' => $metadata['original_name'] ?? 'Unknown source',
                'chunk_index' => $document->chunk_index,
                'page_number' => $document->page_number ?? null,
            ],
        ];
    }

    return $results;
    }

    public function searchWithConfidence(string $query,int $candidateDocumentId,int $limit = 5,float $maxSemanticDistance = 0.45): array 
    {
        $semanticResults = $this->search(
            $query,
            $candidateDocumentId,
            $limit
        );

        if (empty($semanticResults)) {
            return [
                'status' => 'refused',
                'reason' => 'No evidence found in the candidate documents.',
                'confidence' => null,
                'results' => [],
            ];
        }

        $bestDistance = (float) $semanticResults[0]->distance;

        if ($bestDistance > $maxSemanticDistance) {
            return [
                'status' => 'refused',
                'reason' => 'Insufficient evidence found in the candidate documents.',
                'confidence' => [
                    'best_semantic_distance' => $bestDistance,
                    'threshold' => $maxSemanticDistance,
                ],
                'results' => [],
            ];
        }

        return [
            'status' => 'accepted',
            'reason' => null,
            'confidence' => [
                'best_semantic_distance' => $bestDistance,
                'threshold' => $maxSemanticDistance,
            ],
            'results' => $this->hybridSearch(
                $query,
                $candidateDocumentId,
                $limit
            ),
        ];
    }
}