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
        Schema::create('shortlist_candidates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shortlist_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('candidate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('screening_run_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('rank')->nullable();

            $table->decimal('final_score', 5, 2);

            $table->timestamps();

            $table->unique([
                'shortlist_id',
                'candidate_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shortlist_candidates');
    }
};
