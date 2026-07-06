<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old check constraint from the enum
        DB::statement("ALTER TABLE blogs DROP CONSTRAINT IF EXISTS blogs_status_check");
        // Add new check constraint that includes 'scheduled'
        DB::statement("ALTER TABLE blogs ADD CONSTRAINT blogs_status_check CHECK (status IN ('draft', 'published', 'scheduled'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE blogs DROP CONSTRAINT IF EXISTS blogs_status_check");
        DB::statement("ALTER TABLE blogs ADD CONSTRAINT blogs_status_check CHECK (status IN ('draft', 'published'))");
    }
};
