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
        Schema::create('interview_probes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shortlist_candidate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('question');

            $table->text('reason')->nullable();

            $table->string('priority')->default('medium');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_probes');
    }
};
