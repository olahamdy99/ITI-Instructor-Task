<?php

namespace App\Application\Contracts;

interface LlmProviderInterface
{
    public function complete(
        string $systemPrompt,
        string $userPrompt
    ): string;
}