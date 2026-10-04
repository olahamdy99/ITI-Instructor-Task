<?php

namespace App\Providers;
use App\Infrastructure\DocumentExtraction\DocxTextExtractor;
use App\Infrastructure\DocumentExtraction\DocumentTextExtractorResolver;
use App\Infrastructure\DocumentExtraction\PdfTextExtractor;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
