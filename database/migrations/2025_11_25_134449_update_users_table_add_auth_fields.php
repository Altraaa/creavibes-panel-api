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
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('profile_photo_path')->nullable()->after('last_login_ip');
            $table->string('phone_number', 20)->nullable()->after('profile_photo_path');
            $table->text('bio')->nullable()->after('phone_number');
            $table->string('role')->default('user')->after('bio');
            $table->index(['email', 'is_active']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'is_active']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'is_active',
                'email_verified_at',
                'last_login_at',
                'last_login_ip',
                'profile_photo_path',
                'phone_number',
                'bio',
                'role'
            ]);
        });
    }
};
