<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('split_bills', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->renameColumn('created_by', 'creator_id');
            $table->renameColumn('date', 'due_date');
            $table->dropColumn('status');
        });

        Schema::table('split_bills', function (Blueprint $table) {
            $table->string('status')->default('active');
            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('split_bills', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->renameColumn('creator_id', 'created_by');
            $table->renameColumn('due_date', 'date');
            $table->dropColumn('status');
        });

        Schema::table('split_bills', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
