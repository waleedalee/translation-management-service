<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to add performance-optimizing indexes.
     */
    public function up(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            // Add a fulltext index for faster content searching
            $table->fullText('content');
            
            // Add an index for tags to speed up JSON contains queries
            // Note: This is MySQL specific and may not work on all database engines
            DB::statement('ALTER TABLE translations ADD INDEX tags_index ((cast(tags as char(255))));');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            // Drop the fulltext index
            $table->dropFullText('translations_content_fulltext');
            
            // Drop the tags index 
            DB::statement('ALTER TABLE translations DROP INDEX tags_index;');
        });
    }
}; 