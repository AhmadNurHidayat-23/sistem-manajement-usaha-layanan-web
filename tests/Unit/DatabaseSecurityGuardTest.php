<?php

namespace Tests\Unit;

use RuntimeException;
use Tests\TestCase;

class DatabaseSecurityGuardTest extends TestCase
{
    /**
     * Pastikan lingkungan pengujian secara default menggunakan SQLite :memory:.
     */
    public function test_pengujian_menggunakan_sqlite_in_memory(): void
    {
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        $this->assertSame('sqlite', $connection, 'Koneksi database testing harus sqlite');
        $this->assertSame(':memory:', $database, 'Database testing harus berupa in-memory (:memory:)');
    }

    /**
     * Pastikan koneksi mysql dinetralkan ke kredensial terblokir sehingga tidak ada akses ke service_management.
     */
    public function test_koneksi_mysql_dinetralkan_dari_database_utama(): void
    {
        $mysqlDb = (string) config('database.connections.mysql.database');
        $this->assertNotSame('service_management', $mysqlDb, 'Database MySQL tidak boleh mengarah ke service_management');
        $this->assertSame('simaul_testing_blocked', $mysqlDb);
    }

    /**
     * Verifikasi bahwa pengaman bootstrap menolak koneksi jika default connection adalah MySQL.
     */
    public function test_pengaman_menolak_koneksi_mysql_utama(): void
    {
        $mockApp = $this->app;
        $mockApp['config']->set('database.default', 'mysql');
        $mockApp['config']->set('database.connections.mysql.database', 'service_management');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CRITICAL DATABASE SAFETY VIOLATION');

        TestCase::enforceDatabaseSafety($mockApp);
    }

    /**
     * Verifikasi bahwa pengaman bootstrap menolak file sqlite fisik (non :memory:).
     */
    public function test_pengaman_menolak_file_sqlite_fisik(): void
    {
        $mockApp = $this->app;
        $mockApp['config']->set('database.default', 'sqlite');
        $mockApp['config']->set('database.connections.sqlite.database', '/path/to/physical/database.sqlite');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CRITICAL TEST ISOLATION FAILURE');

        TestCase::enforceDatabaseSafety($mockApp);
    }
}
