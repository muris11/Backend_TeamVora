<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->renameColumn('assigned_to', 'assignee_id');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('creator_id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']);
            $table->dropForeign(['creator_id']);
            $table->renameColumn('assignee_id', 'assigned_to');
            $table->dropColumn(['priority', 'creator_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }
};
