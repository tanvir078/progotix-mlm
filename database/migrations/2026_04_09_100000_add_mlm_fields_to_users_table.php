<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('member_code')->nullable()->after('username');
            $table->foreignId('referrer_id')->nullable()->after('remember_token')->constrained('users')->nullOnDelete();
            $table->decimal('balance', 12, 2)->default(0)->after('referrer_id');
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            $baseUsername = Str::of($user->email ?: 'member'.$user->id)
                ->before('@')
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '-')
                ->trim('-')
                ->value();

            $username = $baseUsername !== '' ? $baseUsername : 'member-'.$user->id;

            while (DB::table('users')
                ->where('username', $username)
                ->where('id', '!=', $user->id)
                ->exists()) {
                $username = $baseUsername.'-'.$user->id;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'username' => $username,
                    'member_code' => 'PGX-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
            $table->unique('member_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['member_code']);
            $table->dropConstrainedForeignId('referrer_id');
            $table->dropColumn(['username', 'member_code', 'balance']);
        });
    }
};
