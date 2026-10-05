<?php

namespace App\Application\Contracts;

interface EmbeddingProviderInterface
{
    public function embed(string $text): array;

    public function dimensions(): int;
}