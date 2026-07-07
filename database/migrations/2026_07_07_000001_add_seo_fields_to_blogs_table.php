<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('focus_keyword', 100)->nullable()->after('content');
            $table->string('seo_title', 70)->nullable()->after('focus_keyword');
            $table->string('seo_description', 200)->nullable()->after('seo_title');
            $table->text('seo_keywords')->nullable()->after('seo_description');
            $table->string('canonical_url')->nullable()->after('seo_keywords');
            $table->string('og_image')->nullable()->after('canonical_url');
            $table->string('twitter_card', 20)->default('summary_large_image')->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'focus_keyword',
                'seo_title',
                'seo_description',
                'seo_keywords',
                'canonical_url',
                'og_image',
                'twitter_card',
            ]);
        });
    }
};
