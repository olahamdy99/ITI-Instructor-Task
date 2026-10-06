<?php

namespace App\Services;

class RagAnswerService
{
    public function __construct(
        private DocumentRetrievalService $retrievalService
    ) {
    }

    public function prepareContext(
        string $question,
        int $candidateDocumentId,
        int $limit = 5
    ): array {
        $retrieval = $this->retrievalService->searchWithConfidence(
            $question,
            $candidateDocumentId,
            $limit
        );

        if ($retrieval['status'] === 'refused') {
            return [
                'status' => 'refused',
                'reason' => $retrieval['reason'],
                'confidence' => $retrieval['confidence'],
                'context' => null,
                'citations' => [],
            ];
        }

        $contextParts = [];
        $citations = [];

        foreach ($retrieval['results'] as $index => $result) {
            $citation = $result['citation'];

            $contextParts[] =
                '[Evidence ' . ($index + 1) . "]\n"
                . 'Source: ' . $citation['source'] . "\n"
                . 'Chunk: ' . $citation['chunk_index'] . "\n"
                . $result['content'];

            $citations[] = $citation;
        }

        return [
            'status' => 'ready',
            'reason' => null,
            'confidence' => $retrieval['confidence'],
            'context' => implode("\n\n", $contextParts),
            'citations' => $citations,
        ];
    }
}