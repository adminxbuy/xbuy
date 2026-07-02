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
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'buyer_id') && !Schema::hasColumn('support_tickets', 'user_id')) {
                $table->renameColumn('buyer_id', 'user_id');
            }
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained('orders')->onDelete('set null');
            $table->string('priority')->default('medium')->after('status'); // low, medium, high
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->onDelete('set null');
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'priority', 'assigned_to']);
            if (Schema::hasColumn('support_tickets', 'user_id') && !Schema::hasColumn('support_tickets', 'buyer_id')) {
                $table->renameColumn('user_id', 'buyer_id');
            }
        });
    }
};
