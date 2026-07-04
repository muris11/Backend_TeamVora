<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            if (Schema::hasColumn('bill_items', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
            $table->string('status')->default('unpaid');
            $table->string('proof_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status', 'proof_path', 'verified_by', 'verified_at']);
        });
    }
};
