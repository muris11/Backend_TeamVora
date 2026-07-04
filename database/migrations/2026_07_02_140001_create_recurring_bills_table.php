<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom_days']);
            $table->unsignedSmallInteger('interval_days')->nullable();
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('last_generated_at')->nullable();
            $table->date('next_generation_at');
            $table->json('assignee_ids')->nullable();
            $table->unsignedTinyInteger('notify_days_before_due')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('next_generation_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_bills');
    }
};
