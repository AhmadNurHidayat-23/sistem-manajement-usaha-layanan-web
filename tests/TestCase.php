<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Inisialisasi aplikasi dengan isolasi penuh konfigurasi cache.
     *
     * @return Application
     */
    public function createApplication()
    {
        // 1. Isolasi config cache: pastikan path cache testing tidak pernah menunjuk ke cache produksi
        $dummyCache = sys_get_temp_dir().DIRECTORY_SEPARATOR.'simaul_testing_isolated_config.php';
        putenv("APP_CONFIG_CACHE={$dummyCache}");
        $_ENV['APP_CONFIG_CACHE'] = $dummyCache;
        $_SERVER['APP_CONFIG_CACHE'] = $dummyCache;

        $app = require Application::inferBasePath().'/bootstrap/app.php';

        $this->traitsUsedByTest = class_uses_recursive(static::class);

        // Paksa flag agar framework tidak memuat cache produksi lama jika sempat terbaca
        $app->instance('config_loaded_from_cache', false);

        $app->make(Kernel::class)->bootstrap();

        // 2. Validasi Keamanan Database pada fase Bootstrap (sebelum trait atau runner apa pun aktif)
        static::enforceDatabaseSafety($app);

        // 3. Netralkan koneksi operasional (MySQL/MariaDB) di container testing
        static::neutralizeOperationalConnections($app);

        return $app;
    }

    /**
     * Setup test environment dengan pemeriksaan keamanan sebelum parent::setUp().
     */
    protected function setUp(): void
    {
        // PENTING: parent::setUp() memanggil setUpTheTestEnvironment() -> setUpTraits() -> RefreshDatabase.
        // Pemeriksaan ini WAJIB dijalankan sebelum parent::setUp() agar trait tidak sempat mengeksekusi operasi database.
        if ($this->app) {
            static::enforceDatabaseSafety($this->app);
        }

        parent::setUp();
    }

    /**
     * Hook bawaan trait RefreshDatabase sebelum penyegaran database pengujian dijalankan.
     */
    protected function beforeRefreshingDatabase()
    {
        if ($this->app) {
            static::enforceDatabaseSafety($this->app);
        }
    }

    /**
     * Validasi isolasi database: Memastikan koneksi hanya SQLite :memory: dan bukan database utama.
     *
     * @throws RuntimeException
     */
    public static function enforceDatabaseSafety(Application $app): void
    {
        $defaultConn = (string) $app['config']->get('database.default');
        $activeDb = (string) $app['config']->get("database.connections.{$defaultConn}.database");
        $mysqlDb = (string) $app['config']->get('database.connections.mysql.database');

        // Larang koneksi yang mengarah ke database operasional service_management
        if ($defaultConn === 'mysql' || $activeDb === 'service_management' || $mysqlDb === 'service_management') {
            throw new RuntimeException(
                "CRITICAL DATABASE SAFETY VIOLATION: Test suite attempted to run against operational database 'service_management' (connection: {$defaultConn})! ".
                'Execution halted immediately before any query, migration, seeder, or RefreshDatabase could execute.'
            );
        }

        // Pengujian WAJIB menggunakan SQLite :memory:
        if ($defaultConn !== 'sqlite' || $activeDb !== ':memory:') {
            throw new RuntimeException(
                "CRITICAL TEST ISOLATION FAILURE: Tests must only execute against in-memory SQLite (':memory:'). ".
                "Active connection: [{$defaultConn}] pointing to database [{$activeDb}]. Aborting immediately."
            );
        }
    }

    /**
     * Netralkan konfigurasi koneksi MySQL/MariaDB pada container testing untuk mencegah query mentah.
     */
    protected static function neutralizeOperationalConnections(Application $app): void
    {
        $app['config']->set('database.connections.mysql.database', 'simaul_testing_blocked');
        $app['config']->set('database.connections.mysql.username', 'blocked_user');
        $app['config']->set('database.connections.mysql.password', 'blocked_password');
        $app['config']->set('database.connections.mariadb.database', 'simaul_testing_blocked');
    }
}
