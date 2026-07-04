<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('split_bills', function (Blueprint $table) {
            $table->foreignUuid('parent_recurring_bill_id')
                ->nullable()
                ->constrained('recurring_bills')
                ->nullOnDelete()
                ->after('creator_id');
        });
    }

    public function down(): void
    {
        Schema::table('split_bills', function (Blueprint $table) {
            $table->dropForeign(['parent_recurring_bill_id']);
            $table->dropColumn('parent_recurring_bill_id');
        });
    }
};
