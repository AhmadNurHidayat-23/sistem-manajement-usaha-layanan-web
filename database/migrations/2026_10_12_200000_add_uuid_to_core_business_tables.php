<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tabel-tabel yang memerlukan public identifier opak UUID v4.
     *
     * @var array<int, string>
     */
    protected array $tables = [
        'transactions',
        'bookings',
        'orders',
        'services',
        'branches',
        'employees',
        'memberships',
        'promotions',
        'inventory_items',
        'ulasans',
        'users',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable()->after('id');
                });

                // Backfill existing records dengan UUID unik
                $rows = DB::table($tableName)->whereNull('uuid')->select('id')->get();
                foreach ($rows as $row) {
                    DB::table($tableName)->where('id', $row->id)->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
                }

                // Tambahkan unique index setelah seluruh baris terisi
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unique('uuid');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropUnique(['uuid']);
                    $table->dropColumn('uuid');
                });
            }
        }
    }
};
