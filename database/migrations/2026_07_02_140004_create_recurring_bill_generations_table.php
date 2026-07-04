<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_bill_generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recurring_bill_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('split_bill_id')->constrained()->cascadeOnDelete();
            $table->date('generated_at');
            $table->timestamps();

            $table->index('recurring_bill_id');
            $table->index('split_bill_id');
            $table->unique(['recurring_bill_id', 'split_bill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_bill_generations');
    }
};
