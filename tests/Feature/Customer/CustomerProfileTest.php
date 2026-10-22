<?php

namespace Tests\Feature\Customer;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
        ]);
    }

    public function test_guest_cannot_access_profile_page_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('customer.profile'));
        $response->assertRedirect(route('login'));

        $updateResponse = $this->put(route('customer.profile.update'), [
            'name' => 'Budi Update',
            'phone' => '081299998888',
        ]);
        $updateResponse->assertRedirect(route('login'));
    }

    public function test_non_customer_role_is_forbidden_from_customer_profile(): void
    {
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir SIMAUL',
            'email' => 'kasir@example.com',
        ]);

        $response = $this->actingAs($cashier)->get(route('customer.profile'));
        $response->assertForbidden();

        $updateResponse = $this->actingAs($cashier)->put(route('customer.profile.update'), [
            'name' => 'Kasir Baru',
        ]);
        $updateResponse->assertForbidden();
    }

    public function test_customer_can_view_profile_page_with_their_information(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.profile'));

        $response->assertOk();
        $response->assertSee('Profil Saya');
        $response->assertSee('Budi Santoso');
        $response->assertSee('budi.santoso@example.com');
        $response->assertSee('081234567890');
        $response->assertSee('Edit Informasi Profil');
        $response->assertSee('Simpan Perubahan');
    }

    public function test_customer_can_update_their_name_and_phone(): void
    {
        $response = $this->actingAs($this->customer)->put(route('customer.profile.update'), [
            'name' => 'Budi Santoso Pratama',
            'phone' => '081298765432',
        ]);

        $response->assertRedirect(route('customer.profile'));
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->customer->refresh();
        $this->assertSame('Budi Santoso Pratama', $this->customer->name);
        $this->assertSame('081298765432', $this->customer->phone);
        $this->assertSame('budi.santoso@example.com', $this->customer->email);
    }

    public function test_customer_can_clear_optional_phone_number(): void
    {
        $response = $this->actingAs($this->customer)->put(route('customer.profile.update'), [
            'name' => 'Budi Santoso',
            'phone' => '',
        ]);

        $response->assertRedirect(route('customer.profile'));
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->customer->refresh();
        $this->assertNull($this->customer->phone);
    }

    public function test_validation_fails_when_name_is_empty(): void
    {
        $response = $this->actingAs($this->customer)
            ->from(route('customer.profile'))
            ->put(route('customer.profile.update'), [
                'name' => '',
                'phone' => '081234567890',
            ]);

        $response->assertRedirect(route('customer.profile'));
        $response->assertSessionHasErrors(['name']);

        $this->customer->refresh();
        $this->assertSame('Budi Santoso', $this->customer->name);
    }

    public function test_validation_fails_when_phone_has_invalid_format(): void
    {
        $response = $this->actingAs($this->customer)
            ->from(route('customer.profile'))
            ->put(route('customer.profile.update'), [
                'name' => 'Budi Santoso',
                'phone' => 'bukan-nomor-telepon-123@@!!',
            ]);

        $response->assertRedirect(route('customer.profile'));
        $response->assertSessionHasErrors(['phone']);
    }

    public function test_navbar_profile_menu_links_to_actual_customer_profile_route(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.dashboard'));

        $response->assertOk();
        $expectedUrl = route('customer.profile');

        $response->assertSee('href="'.$expectedUrl.'"', false);
        $response->assertDontSee('id="customer-profile-link" href="#"', false);
        $response->assertDontSee('id="mobile-profile-link" href="#"', false);
    }
}
