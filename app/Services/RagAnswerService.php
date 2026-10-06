<?php

namespace App\Services;

use App\Application\Contracts\LlmProviderInterface;

class RagAnswerService
{
    public function __construct(
        private DocumentRetrievalService $retrievalService,
        private LlmProviderInterface $llmProvider
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

    public function answer(
        string $question,
        int $candidateDocumentId,
        int $limit = 5
    ): array {
        $prepared = $this->prepareContext(
            $question,
            $candidateDocumentId,
            $limit
        );

        if ($prepared['status'] === 'refused') {
            return [
                'status' => 'refused',
                'answer' => null,
                'reason' => $prepared['reason'],
                'confidence' => $prepared['confidence'],
                'citations' => [],
            ];
        }

        $systemPrompt = <<<'PROMPT'
You are an HR talent screening assistant.

Answer the question using ONLY the provided evidence.

Rules:
- Do not use outside knowledge.
- Do not invent candidate experience, skills, achievements, or qualifications.
- If the evidence does not support a claim, say that the evidence is insufficient.
- Keep the answer concise and factual.
- Refer to evidence using labels such as [Evidence 1], [Evidence 2].
PROMPT;

        $userPrompt =
            "Question:\n"
            . $question
            . "\n\nEvidence:\n"
            . $prepared['context'];

        $answer = $this->llmProvider->complete(
            $systemPrompt,
            $userPrompt
        );

        return [
            'status' => 'answered',
            'answer' => $answer,
            'reason' => null,
            'confidence' => $prepared['confidence'],
            'citations' => $prepared['citations'],
        ];
    }
}