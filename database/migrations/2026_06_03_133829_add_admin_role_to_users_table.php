<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('admin_role', [
                'super_admin',
                'operations',
                'support',
                'finance',
                'content',
                'moderator',
            ])->nullable()->after('role')->comment('Only populated when role = admin');
        });

        // Set all existing admins as super_admin
        \App\Models\User::where('role', 'admin')->update(['admin_role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_role');
        });
    }
};
