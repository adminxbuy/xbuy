<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Drop old tables from first implementation ─────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'staff_id')) {
                $table->dropForeign(['staff_id']);
                $table->dropColumn('staff_id');
            }
        });
        Schema::dropIfExists('staff_monthly_payrolls');
        Schema::dropIfExists('staff_payroll_notices');
        Schema::dropIfExists('staff_profiles');

        // ── 1. Add staff columns to users table ──────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_role')->nullable()->after('admin_role'); // superadmin / admin / staff
            $table->boolean('is_staff')->default(false)->after('staff_role');
        });

        // ── 2. Create staff_profiles table ───────────────────────────────
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('designation')->default('Platform Operations Executive');
            $table->date('appointment_date')->nullable();
            $table->string('appointment_letter_ref')->nullable();
            $table->text('bank_account')->nullable();       // encrypted
            $table->text('upi_id')->nullable();             // encrypted
            $table->text('pan_number')->nullable();          // encrypted
            $table->text('aadhaar_number')->nullable();      // encrypted
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        // ── 3. Create staff_earnings table ───────────────────────────────
        Schema::create('staff_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('sale_value', 12, 2);
            $table->string('category')->nullable();
            $table->decimal('gross_commission', 12, 2)->default(0);
            $table->decimal('platform_expenses', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->decimal('calculated_amount', 12, 2)->default(0);
            $table->boolean('min_cap_applied')->default(false);
            $table->boolean('max_cap_applied')->default(false);
            $table->decimal('final_earning', 12, 2)->default(0);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['pending', 'paid', 'adjusted'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['staff_user_id', 'order_id']);
            $table->index(['staff_user_id', 'month', 'year']);
            $table->index(['status', 'month', 'year']);
        });

        // ── 4. Create staff_payroll_notices table ────────────────────────
        Schema::create('staff_payroll_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->enum('notice_type', [
                'salary_slip',
                'amendment_notice',
                'policy_change',
                'warning',
                'general'
            ]);
            $table->string('subject');
            $table->text('message');
            $table->string('pdf_attachment')->nullable();
            $table->enum('delivery_method', ['email', 'whatsapp', 'both']);
            $table->boolean('sent_via_email')->default(false);
            $table->boolean('sent_via_whatsapp')->default(false);
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->timestamps();

            $table->index(['staff_user_id', 'notice_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payroll_notices');
        Schema::dropIfExists('staff_earnings');
        Schema::dropIfExists('staff_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_role', 'is_staff']);
        });
    }
};
