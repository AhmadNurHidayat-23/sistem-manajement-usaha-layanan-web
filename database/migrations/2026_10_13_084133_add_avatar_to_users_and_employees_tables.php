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
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'avatar')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'avatar')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }
};
