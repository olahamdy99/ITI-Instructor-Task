<?php

namespace App\Infrastructure\AI;

use App\Application\Contracts\EmbeddingProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaEmbeddingProvider implements EmbeddingProviderInterface
{
    public function embed(string $text): array
    {
        $response = Http::timeout(60)->post(
            config('services.ollama.url') . '/api/embed',
            [
                'model' => config('services.ollama.embedding_model'),
                'input' => $text,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Ollama embedding request failed: ' . $response->body()
            );
        }

        $embedding = $response->json('embeddings.0');

        if (!is_array($embedding)) {
            throw new RuntimeException(
                'Invalid embedding response from Ollama.'
            );
        }

        if (count($embedding) !== $this->dimensions()) {
            throw new RuntimeException(
                'Unexpected embedding dimension returned by Ollama.'
            );
        }

        return $embedding;
    }

    public function dimensions(): int
    {
        return 768;
    }
}