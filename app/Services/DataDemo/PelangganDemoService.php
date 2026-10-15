<?php

namespace App\Services\DataDemo;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\MembershipPromo\MemberService;
use Illuminate\Support\Facades\Hash;

class PelangganDemoService
{
    public function __construct(
        protected MemberService $memberService
    ) {}

    /**
     * Siapkan akun customer realistis dan daftarkan keanggotaan member.
     *
     * @param  (callable(string, string): void)|null  $logger
     * @return array<string, User>
     */
    public function jalankan(?callable $logger = null): array
    {
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();
        $demoPassword = Hash::make('password123');

        $branches = Branch::all();
        $branchIds = $branches->pluck('id')->toArray();
        $owner = User::whereIn('email', ['owner@simaul.com', 'owner@example.com'])->firstOrFail();

        $customersData = [
            ['name' => 'Andi Pratama', 'email' => 'andi.p@customer.local', 'phone' => '081211112201', 'is_member' => true],
            ['name' => 'Bima Saputra', 'email' => 'bima.s@customer.local', 'phone' => '081211112202', 'is_member' => true],
            ['name' => 'Rina Maharani', 'email' => 'rina.m@customer.local', 'phone' => '081211112203', 'is_member' => true],
            ['name' => 'Dimas Nugroho', 'email' => 'dimas.n@customer.local', 'phone' => '081211112204', 'is_member' => true],
            ['name' => 'Siti Rahmawati', 'email' => 'siti.r@customer.local', 'phone' => '081211112205', 'is_member' => true],
            ['name' => 'Fajar Ramadhan', 'email' => 'fajar.r@customer.local', 'phone' => '081211112206', 'is_member' => true],
            ['name' => 'Nabila Putri', 'email' => 'nabila.p@customer.local', 'phone' => '081211112207', 'is_member' => true],
            ['name' => 'Arif Setiawan', 'email' => 'arif.s@customer.local', 'phone' => '081211112208', 'is_member' => true],
            ['name' => 'Maya Lestari', 'email' => 'maya.l@customer.local', 'phone' => '081211112209', 'is_member' => true],
            ['name' => 'Reza Firmansyah', 'email' => 'reza.f@customer.local', 'phone' => '081211112210', 'is_member' => true],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.p@customer.local', 'phone' => '081211112211', 'is_member' => false],
            ['name' => 'Dian Anggraini', 'email' => 'dian.a@customer.local', 'phone' => '081211112212', 'is_member' => false],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra.g@customer.local', 'phone' => '081211112213', 'is_member' => true],
        ];

        $customers = [];
        foreach ($customersData as $cData) {
            $user = User::updateOrCreate(
                ['email' => $cData['email']],
                [
                    'name' => $cData['name'],
                    'phone' => $cData['phone'],
                    'password' => $demoPassword,
                    'role_id' => $customerRole->id,
                ]
            );
            $user->branches()->syncWithoutDetaching($branchIds);
            $customers[$cData['name']] = $user;

            if ($cData['is_member'] && ! $user->membership) {
                try {
                    $this->memberService->createMember($owner, [
                        'user_id' => $user->id,
                        'member_code' => 'MBR-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                    ]);
                } catch (\Throwable $e) {
                    if ($logger) {
                        $logger('warn', "Pendaftaran member {$user->name} dilewati: {$e->getMessage()}");
                    }
                }
            }
        }

        // Customer demo awal
        $defaultCustomer = User::whereIn('email', ['pelanggan@simaul.com', 'customer@example.com'])->first();
        if ($defaultCustomer) {
            $customers['Rian Saputra'] = $defaultCustomer;
            if (! $defaultCustomer->membership) {
                try {
                    $this->memberService->createMember($owner, [
                        'user_id' => $defaultCustomer->id,
                        'member_code' => 'MBR-'.str_pad((string) $defaultCustomer->id, 5, '0', STR_PAD_LEFT),
                    ]);
                } catch (\Throwable $e) {
                    if ($logger) {
                        $logger('warn', "Pendaftaran member Rian Saputra dilewati: {$e->getMessage()}");
                    }
                }
            }
        }

        return $customers;
    }
}
