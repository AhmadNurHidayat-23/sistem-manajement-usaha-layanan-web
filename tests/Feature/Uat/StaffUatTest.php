<?php

namespace Tests\Feature\Uat;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUatTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);

        $this->owner = User::where('email', 'owner@example.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->branch1 = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $this->branch2 = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
    }

    public function test_uat_manager_branch_scoped_operations(): void
    {
        $response = $this->post('/login', [
            'email' => 'manager@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('manager.dashboard'));

        $dashboard = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Dashboard Manajer');

        $booking = $this->actingAs($this->manager)->get(route('bookings.index'));
        $booking->assertStatus(200);

        $queue = $this->actingAs($this->manager)->get(route('queue.index'));
        $queue->assertStatus(200);

        $employees = $this->actingAs($this->manager)->get(route('employees.index'));
        $employees->assertStatus(200);

        $inventory = $this->actingAs($this->manager)->get(route('inventori.index'));
        $inventory->assertStatus(200);

        $transactions = $this->actingAs($this->manager)->get(route('pos.transactions.index'));
        $transactions->assertStatus(200);

        $promotions = $this->actingAs($this->manager)->get(route('promotions.index'));
        $promotions->assertStatus(200);

        $report = $this->actingAs($this->manager)->get(route('reports.index', ['branch_id' => $this->branch1->id]));
        $report->assertStatus(200);

        $unauthorizedBranch = $this->actingAs($this->manager)->get(route('branches.show', $this->branch2));
        $unauthorizedBranch->assertStatus(403);

        $createBranch = $this->actingAs($this->manager)->get(route('branches.create'));
        $createBranch->assertStatus(403);
    }

    public function test_uat_owner_full_operations(): void
    {
        $response = $this->post('/login', [
            'email' => 'owner@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('owner.dashboard'));

        $dashboard = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Dashboard Owner');

        $branches = $this->actingAs($this->owner)->get(route('branches.index'));
        $branches->assertStatus(200);
        $branches->assertSee($this->branch1->display_name);
        $branches->assertSee($this->branch2->display_name);

        $services = $this->actingAs($this->owner)->get(route('catalog.services.index'));
        $services->assertStatus(200);

        $categories = $this->actingAs($this->owner)->get(route('catalog.categories.index'));
        $categories->assertStatus(200);

        $employees = $this->actingAs($this->owner)->get(route('employees.index'));
        $employees->assertStatus(200);

        $transactions = $this->actingAs($this->owner)->get(route('pos.transactions.index'));
        $transactions->assertStatus(200);

        $members = $this->actingAs($this->owner)->get(route('members.index'));
        $members->assertStatus(200);

        $promos = $this->actingAs($this->owner)->get(route('promotions.index'));
        $promos->assertStatus(200);

        $reports = $this->actingAs($this->owner)->get(route('reports.index'));
        $reports->assertStatus(200);

        $ulasan = $this->actingAs($this->owner)->get(route('ulasan.index'));
        $ulasan->assertStatus(200);
        $ulasan->assertSee('Ulasan &amp; Rating', false);

        $aktivitas = $this->actingAs($this->owner)->get(route('aktivitas.index'));
        $aktivitas->assertStatus(200);
        $aktivitas->assertSee('Log Aktivitas');

        $notifikasi = $this->actingAs($this->owner)->get(route('notifikasi.index'));
        $notifikasi->assertStatus(200);
        $notifikasi->assertSee('Log Notifikasi');
    }
}
