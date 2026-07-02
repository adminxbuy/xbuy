<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create staff_profiles table
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('designation')->default('Staff Member');
            $table->date('joined_at')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('appointment_letter_ref')->nullable();
            $table->timestamps();
        });

        // 2. Create staff_payroll_notices table
        Schema::create('staff_payroll_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // e.g. Notice, Salary Slip
            $table->string('subject');
            $table->mediumText('message');
            $table->string('attachment_path')->nullable();
            $table->string('delivery_method'); // email, whatsapp, both
            $table->timestamps();
        });

        // 3. Create staff_monthly_payrolls table
        Schema::create('staff_monthly_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->string('month', 7); // e.g. '2026-06'
            $table->integer('orders_count')->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0.00);
            $table->decimal('expenses_amount', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 4. Add staff_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');
        });

        // 5. Seed default configurations in site_settings table
        $settings = [
            [
                'key' => 'payroll_commission_percent',
                'value' => '10',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Platform commission percentage earned by staff per order'
            ],
            [
                'key' => 'payroll_min_cap',
                'value' => '5',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Minimum cap amount per order for staff commission in INR'
            ],
            [
                'key' => 'payroll_max_cap',
                'value' => '50',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Maximum cap amount per order for staff commission in INR'
            ],
            [
                'key' => 'payroll_platform_expense',
                'value' => '50',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Platform expense per order or per month for staff'
            ],
            [
                'key' => 'payroll_payment_day',
                'value' => '5',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Monthly payment day for payroll disbursement'
            ],
            [
                'key' => 'payroll_amendment_trigger',
                'value' => '100',
                'type' => 'integer',
                'group' => 'payroll',
                'description' => 'Number of monthly orders to trigger amendment bonus progress'
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn('staff_id');
        });

        Schema::dropIfExists('staff_monthly_payrolls');
        Schema::dropIfExists('staff_payroll_notices');
        Schema::dropIfExists('staff_profiles');
    }
};
