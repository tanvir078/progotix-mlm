<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // This legacy migration shipped empty in the worktree.
        // The real refund tracking fields are added in a later migration
        // after the MLM tables exist, so fresh installs stay consistent.
    }

    public function down(): void
    {
        //
    }
};
