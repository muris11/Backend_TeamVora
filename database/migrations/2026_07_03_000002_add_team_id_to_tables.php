<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users — add team_id + change role to enum
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
            $table->string('role')->default('member')->after('team_id');
        });

        // Cash Books
        Schema::table('cash_books', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });

        // Split Bills
        Schema::table('split_bills', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });

        // Recurring Bills
        Schema::table('recurring_bills', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });

        // Tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });

        // Daily Logs
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });

        // Team Media
        Schema::table('team_media', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        $tables = ['team_media', 'daily_logs', 'tasks', 'recurring_bills', 'split_bills', 'cash_books', 'users'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tbl) {
                $tbl->dropForeign(['team_id']);
                $tbl->dropColumn('team_id');
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
