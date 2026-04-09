<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('country_code', 2)->nullable()->after('email');
            $table->string('phone_code', 8)->nullable()->after('country_code');
            $table->string('phone_number', 25)->nullable()->after('phone_code');
            $table->string('city')->nullable()->after('phone_number');
            $table->string('profession')->nullable()->after('city');
            $table->string('company_name')->nullable()->after('profession');
            $table->string('profile_headline')->nullable()->after('company_name');
            $table->text('bio')->nullable()->after('profile_headline');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'country_code',
                'phone_code',
                'phone_number',
                'city',
                'profession',
                'company_name',
                'profile_headline',
                'bio',
            ]);
        });
    }
};
