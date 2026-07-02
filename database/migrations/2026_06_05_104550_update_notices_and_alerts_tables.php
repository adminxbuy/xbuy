<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_alerts', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_alerts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->string('type', 100)->change();
            }
        });

        if (Schema::hasTable('staff_payroll_notices')) {
            Schema::table('staff_payroll_notices', function (Blueprint $table) {
                if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->string('notice_type', 100)->change();
                    $table->string('delivery_method', 100)->change();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('admin_alerts', function (Blueprint $table) {
            if (Schema::hasColumn('admin_alerts', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
