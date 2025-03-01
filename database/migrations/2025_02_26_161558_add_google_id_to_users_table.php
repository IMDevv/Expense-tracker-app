<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('id');
            // Make password nullable since Google users won't have a password
            if (DB::getDriverName() === 'pgsql') {
                // For PostgreSQL, we need to drop the not null constraint
                DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL');
            } else {
                $table->string('password')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE users ALTER COLUMN password SET NOT NULL');
            } else {
                $table->string('password')->nullable(false)->change();
            }
        });
    }
};