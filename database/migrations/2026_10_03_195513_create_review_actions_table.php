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
        Schema::create('review_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_queue_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('action', [
                'approve',
                'reject',
                'edit_and_approve',
                'escalate',
                'reassign',
            ]);

            $table->text('comment')->nullable();

            $table->json('edited_payload')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_actions');
    }
};
