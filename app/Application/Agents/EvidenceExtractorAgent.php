<?php

namespace App\Application\Agents;

use App\Services\RagAnswerService;

class EvidenceExtractorAgent
{
    public function __construct(
        private RagAnswerService $ragAnswerService
    ) {
    }

    public function run(
        string $criterion,
        int $candidateDocumentId
    ): array {
        $question = sprintf(
            'Extract factual evidence that shows whether the candidate satisfies this criterion: %s',
            $criterion
        );

        $result = $this->ragAnswerService->answer(
            $question,
            $candidateDocumentId,
            5
        );

        if ($result['status'] === 'refused') {
            return [
                'status' => 'no_evidence',
                'criterion' => $criterion,
                'evidence' => [],
                'reason' => $result['reason'],
            ];
        }

        return [
            'status' => 'completed',
            'criterion' => $criterion,
            'evidence_summary' => $result['answer'],
            'citations' => $result['citations'],
            'confidence' => $result['confidence'],
        ];
    }
}