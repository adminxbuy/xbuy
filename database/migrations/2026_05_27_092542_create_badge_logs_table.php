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
        Schema::create('badge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('cascade');
            $table->enum('previous_badge', ['basic', 'verified', 'fulfilled']);
            $table->enum('new_badge', ['basic', 'verified', 'fulfilled']);
            $table->enum('change_type', ['upgrade', 'downgrade', 'manual']);
            $table->enum('changed_by', ['system', 'admin']);
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_logs');
    }
};
