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
        Schema::create('review_queue_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shortlist_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'critical',
            ])->default('medium');

            $table->enum('status', [
                'pending',
                'assigned',
                'in_review',
                'approved',
                'rejected',
                'escalated',
            ])->default('pending');

            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_queue_items');
    }
};
