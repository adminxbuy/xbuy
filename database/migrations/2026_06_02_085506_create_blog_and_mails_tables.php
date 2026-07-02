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
        // Articles/Blog table
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('category')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Subscribers table
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('active'); // active, unsubscribed
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('token')->nullable();
            $table->string('source')->default('footer_newsletter'); // e.g. footer_newsletter, sales_alert, checkout
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();
        });

        // Mail Campaigns/Logs table
        Schema::create('mail_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('body');
            $table->string('target_group'); // all_subscribers, all_users, sellers, buyers
            $table->string('type')->default('general_alert'); // sales_alert, newsletter, general_alert
            $table->string('status')->default('sent'); // draft, sent, failed
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_campaigns');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('articles');
    }
};
