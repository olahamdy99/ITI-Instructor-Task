<?php

namespace App\Application\Agents;

use App\Application\Contracts\LlmProviderInterface;
use App\Services\RagAnswerService;
use RuntimeException;

class EvidenceExtractorAgent
{
    public function __construct(
        private RagAnswerService $ragAnswerService,
        private LlmProviderInterface $llmProvider
    ) {
    }

    public function run(
        string $criterion,
        int $candidateDocumentId,
        int $limit = 5
    ): array {
        $prepared = $this->ragAnswerService->prepareContext(
            "Find evidence for this criterion: {$criterion}",
            $candidateDocumentId,
            $limit
        );

        if ($prepared['status'] === 'refused') {
            return [
                'status' => 'no_evidence',
                'criterion' => $criterion,
                'conclusion' => 'not_supported',
                'evidence' => [],
                'reason' => $prepared['reason'],
                'confidence' => $prepared['confidence'],
            ];
        }

        $promptPath = resource_path(
            'prompts/agents/evidence-extractor/v1/system.txt'
        );

        if (! file_exists($promptPath)) {
            throw new RuntimeException(
                'Evidence Extractor prompt was not found.'
            );
        }

        $systemPrompt = trim(
            file_get_contents($promptPath)
        );

        $userPrompt =
            "Criterion:\n"
            . $criterion
            . "\n\nEvidence:\n"
            . $prepared['context'];

        $response = $this->llmProvider->complete(
            $systemPrompt,
            $userPrompt
        );

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            throw new RuntimeException(
                'Evidence Extractor returned invalid JSON.'
            );
        }

        return [
            'status' => 'completed',
            'criterion' => $criterion,
            'conclusion' => $decoded['conclusion'] ?? 'not_supported',
            'evidence' => $decoded['evidence'] ?? [],
            'citations' => $prepared['citations'],
            'confidence' => $prepared['confidence'],
        ];
    }
}