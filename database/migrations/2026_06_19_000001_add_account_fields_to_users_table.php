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
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('name');
            $table->string('gender')->nullable()->after('full_name');
            $table->string('birthday')->nullable()->after('gender');
            $table->boolean('vacation_mode')->default(false)->after('birthday');
            $table->boolean('facebook_linked')->default(false)->after('vacation_mode');
            $table->boolean('google_linked')->default(true)->after('facebook_linked'); // Google linked default true since we might mock it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'gender',
                'birthday',
                'vacation_mode',
                'facebook_linked',
                'google_linked'
            ]);
        });
    }
};
