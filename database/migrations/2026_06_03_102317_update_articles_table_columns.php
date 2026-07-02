<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('content');
            $table->string('featured_image')->nullable()->after('cover_image');
            $table->foreignId('author_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('is_published');
            $table->string('meta_title')->nullable()->after('seo_title');
            $table->text('meta_description')->nullable()->after('seo_description');
            $table->json('tags')->nullable()->after('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn([
                'excerpt',
                'featured_image',
                'author_id',
                'status',
                'meta_title',
                'meta_description',
                'tags'
            ]);
        });
    }
};
