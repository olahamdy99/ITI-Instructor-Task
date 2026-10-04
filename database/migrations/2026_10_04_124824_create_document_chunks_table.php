<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('chunk_index');

            $table->text('content');

            $table->unsignedInteger('page_number')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'candidate_document_id',
                'chunk_index',
            ]);
        });

        DB::statement(
            'ALTER TABLE document_chunks ADD COLUMN embedding vector(1536)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};
