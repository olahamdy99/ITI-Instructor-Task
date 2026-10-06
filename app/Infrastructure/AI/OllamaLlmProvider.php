<?php

namespace App\Infrastructure\AI;

use App\Application\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaLlmProvider implements LlmProviderInterface
{
    public function complete(
        string $systemPrompt,
        string $userPrompt
    ): string {
        $url = rtrim(
            config('services.ollama.url'),
            '/'
        );

        $model = config(
            'services.ollama.chat_model',
            'llama3.2:3b'
        );

        $response = Http::timeout(120)
            ->post($url . '/api/chat', [
                'model' => $model,
                'stream' => false,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Ollama completion request failed: '
                . $response->body()
            );
        }

        $content = $response->json('message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException(
                'Ollama returned an empty completion.'
            );
        }

        return trim($content);
    }
}