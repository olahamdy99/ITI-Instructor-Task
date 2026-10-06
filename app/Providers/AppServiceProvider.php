<?php

namespace App\Providers;
use App\Infrastructure\DocumentExtraction\DocxTextExtractor;
use App\Infrastructure\DocumentExtraction\DocumentTextExtractorResolver;
use App\Infrastructure\DocumentExtraction\PdfTextExtractor;
use App\Application\Contracts\EmbeddingProviderInterface;
use App\Application\Contracts\LlmProviderInterface;
use App\Infrastructure\AI\OllamaEmbeddingProvider;
use App\Infrastructure\AI\OllamaLlmProvider;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentTextExtractorResolver::class, function () {
            return new DocumentTextExtractorResolver([
                new PdfTextExtractor(),
                new DocxTextExtractor(),
            ]);
        });

        $this->app->bind(
            EmbeddingProviderInterface::class,
            OllamaEmbeddingProvider::class
        );

        $this->app->bind(
            LlmProviderInterface::class,
            OllamaLlmProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
