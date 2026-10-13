<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Aktivitas;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordAndAktivitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Lupa Password');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHas('status');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get("/reset-password/{$token}?email=".urlencode($user->email));

        $response->assertStatus(200);
        $response->assertSee('Reset Password');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_owner_can_view_all_activity_logs(): void
    {
        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        Aktivitas::create([
            'user_id' => $owner->id,
            'aktivitas' => 'Tambah Cabang',
            'modul' => 'cabang',
            'keterangan' => 'Cabang Baru ditambahkan',
        ]);

        $response = $this->actingAs($owner)->get(route('aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas');
        $response->assertSee('Tambah Cabang');
    }

    public function test_manager_can_access_activity_logs_scoped_to_their_branches(): void
    {
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $branchA = Branch::create(['name' => 'Cabang A', 'address' => 'Jl. A', 'is_active' => true]);
        $branchB = Branch::create(['name' => 'Cabang B', 'address' => 'Jl. B', 'is_active' => true]);

        $manager = User::factory()->create(['role_id' => $managerRole->id]);
        $manager->branches()->attach($branchA->id);

        $userInBranchA = User::factory()->create(['role_id' => $customerRole->id]);
        $userInBranchA->branches()->attach($branchA->id);

        $userInBranchB = User::factory()->create(['role_id' => $customerRole->id]);
        $userInBranchB->branches()->attach($branchB->id);

        Aktivitas::create([
            'user_id' => $userInBranchA->id,
            'aktivitas' => 'Booking Cabang A',
            'modul' => 'booking',
            'keterangan' => 'Booking di cabang A',
        ]);

        Aktivitas::create([
            'user_id' => $userInBranchB->id,
            'aktivitas' => 'Booking Cabang B',
            'modul' => 'booking',
            'keterangan' => 'Booking di cabang B',
        ]);

        $response = $this->actingAs($manager)->get(route('aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee('Booking Cabang A');
        $response->assertDontSee('Booking Cabang B');
    }

    public function test_customer_cannot_access_activity_logs(): void
    {
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $customerRole->id]);

        $response = $this->actingAs($customer)->get(route('aktivitas.index'));

        $response->assertStatus(403);
    }
}
