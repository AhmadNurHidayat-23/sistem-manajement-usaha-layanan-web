<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test master database tables and schema structure exist.
     */
    public function test_master_database_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasColumn('users', 'role_id'));
        $this->assertTrue(Schema::hasTable('branches'));
        $this->assertTrue(Schema::hasTable('user_branches'));
        $this->assertTrue(Schema::hasTable('service_categories'));
        $this->assertTrue(Schema::hasTable('services'));
        $this->assertTrue(Schema::hasTable('employees'));
        $this->assertTrue(Schema::hasTable('bookings'));
        $this->assertTrue(Schema::hasTable('booking_details'));
        $this->assertTrue(Schema::hasTable('orders'));
        $this->assertTrue(Schema::hasTable('order_details'));
        $this->assertTrue(Schema::hasTable('transactions'));
        $this->assertTrue(Schema::hasTable('transaction_details'));
        $this->assertTrue(Schema::hasTable('payments'));
        $this->assertTrue(Schema::hasTable('cashier_shifts'));
        $this->assertTrue(Schema::hasTable('employee_skills'));
        $this->assertTrue(Schema::hasTable('employee_shifts'));
        $this->assertTrue(Schema::hasTable('employee_commissions'));
        $this->assertTrue(Schema::hasTable('memberships'));
        $this->assertTrue(Schema::hasTable('loyalty_points'));
        $this->assertTrue(Schema::hasTable('loyalty_transactions'));
        $this->assertTrue(Schema::hasTable('promotions'));
        $this->assertTrue(Schema::hasTable('promotion_usages'));
    }
}
