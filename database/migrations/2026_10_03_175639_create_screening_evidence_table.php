<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screening_evidence', function (Blueprint $table) {
            $table->id();

            $table->foreignId('screening_run_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('evaluation_criterion_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('evidence_text');

            $table->foreignId('candidate_document_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedInteger('page_number')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_evidence');
    }
};
