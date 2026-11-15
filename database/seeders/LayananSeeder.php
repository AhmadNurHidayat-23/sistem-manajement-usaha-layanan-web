<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Jalankan seeder kategori dan katalog layanan untuk tiap cabang dengan harga pasar realistis 2026.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        // 1. Sinkronisasi Rename Kategori Lama ke Bahasa Indonesia
        $categoryRenames = [
            'Haircut & Styling' => 'Potong Rambut & Styling',
            'Grooming & Shaving' => 'Grooming & Cukur',
            'Hair & Scalp Treatment' => 'Perawatan Rambut & Kulit Kepala',
            'Deep Clean Sepatu & Tas' => 'Pembersihan Sepatu & Tas',
            'Auto Car Wash & Interior' => 'Cuci Mobil & Interior',
            'Paint Protection & Coating' => 'Proteksi Cat & Coating',
            'Care Helm & Aksesoris' => 'Perawatan Helm & Aksesoris',
        ];
        foreach ($categoryRenames as $old => $new) {
            ServiceCategory::where('name', $old)->update(['name' => $new]);
        }

        // Kategori Layanan Tiap Cabang
        $catA1 = ServiceCategory::firstOrCreate(['name' => 'Potong Rambut & Styling']);
        $catA2 = ServiceCategory::firstOrCreate(['name' => 'Grooming & Cukur']);
        $catA3 = ServiceCategory::firstOrCreate(['name' => 'Perawatan Rambut & Kulit Kepala']);

        $catB1 = ServiceCategory::firstOrCreate(['name' => 'Laundry Kiloan & Reguler']);
        $catB2 = ServiceCategory::firstOrCreate(['name' => 'Pembersihan Sepatu & Tas']);
        $catB3 = ServiceCategory::firstOrCreate(['name' => 'Dry Clean Busana Formal']);

        $catC1 = ServiceCategory::firstOrCreate(['name' => 'Cuci Mobil & Interior']);
        $catC2 = ServiceCategory::firstOrCreate(['name' => 'Proteksi Cat & Coating']);
        $catC3 = ServiceCategory::firstOrCreate(['name' => 'Perawatan Helm & Aksesoris']);

        // 2. Daftar Layanan dengan Tarif Standar Menengah Pasar Indonesia (2026)
        $services = [
            // Cabang A: Barbershop & Studio (Cabang Pusat)
            [
                'name' => 'Potong Rambut Pria',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA1->id,
                'description' => 'Potong rambut pria dengan teknik presisi, konsultasi gaya, dan finishing rapi.',
                'price' => 50000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Potong Rambut & Styling',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA1->id,
                'description' => 'Paket potong rambut lengkap dengan cuci rambut menyegarkan dan penataan pomade premium.',
                'price' => 65000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Cukur & Rapikan Jenggot',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA2->id,
                'description' => 'Perapihan dan pembentukan kumis serta jenggot dengan handuk hangat aromaterapi dan razor shave presisi.',
                'price' => 35000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan Kulit Kepala',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA3->id,
                'description' => 'Perawatan intensif pembersihan ketombe dan stimulasi akar rambut dengan serum keratin serta pijat relaksasi.',
                'price' => 75000,
                'unit' => 'treatment',
                'service_type' => 'appointment',
                'estimated_duration' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Pewarnaan Rambut',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA1->id,
                'description' => 'Pewarnaan rambut profesional menggunakan cat berkualitas aman untuk menghasilkan warna natural maupun modern.',
                'price' => 140000,
                'unit' => 'treatment',
                'service_type' => 'appointment',
                'estimated_duration' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Paket Grooming Lengkap',
                'branch_id' => $branchA->id,
                'service_category_id' => $catA2->id,
                'description' => 'Layanan komplit mencakup potong rambut, cukur jenggot, pembersihan komedo, dan totok wajah menyegarkan.',
                'price' => 95000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 60,
                'is_active' => true,
            ],

            // Cabang B: Laundry & Shoe Care (Cabang Selatan)
            [
                'name' => 'Cuci Kering Setrika',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB1->id,
                'description' => 'Layanan cuci bersih pakaian harian tuntas dengan setrika uap higienis dan kemasan plastik rapi.',
                'price' => 9000,
                'unit' => 'kg',
                'service_type' => 'drop_off',
                'estimated_duration' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Laundry Express',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB1->id,
                'description' => 'Layanan pencucian cepat selesai dalam hitungan jam dengan prioritas pengerjaan dan hasil bersih maksimal.',
                'price' => 16000,
                'unit' => 'kg',
                'service_type' => 'drop_off',
                'estimated_duration' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Deep Clean Sepatu',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB2->id,
                'description' => 'Pencucian menyeluruh luar dan dalam sepatu sneakers, kanvas, maupun kulit menggunakan pembersih khusus material.',
                'price' => 45000,
                'unit' => 'pasang',
                'service_type' => 'drop_off',
                'estimated_duration' => 1440,
                'is_active' => true,
            ],
            [
                'name' => 'Dry Clean Jas & Kebaya',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB3->id,
                'description' => 'Perawatan busana formal, setelan jas, dan kebaya pesta dengan teknologi dry clean tanpa merusak serat kain.',
                'price' => 60000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 2880,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Bed Cover',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB1->id,
                'description' => 'Pencucian sprei kasur ukuran besar dan selimut tebal dengan teknologi pembersih anti-bakteri dan ekstraksi tungau.',
                'price' => 35000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 1440,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Karpet',
                'branch_id' => $branchB->id,
                'service_category_id' => $catB1->id,
                'description' => 'Pencucian karpet rumah dan permadani dengan mesin polisher berputar serta proses pengeringan bebas bau apek.',
                'price' => 50000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 2880,
                'is_active' => true,
            ],

            // Cabang C: Auto Care & Detailing (Cabang Barat)
            [
                'name' => 'Cuci Mobil Premium',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC1->id,
                'description' => 'Cuci mobil salju pH netral menyeluruh, pembersihan sela pintu, semir ban, dan pengeringan lap microfiber lembut.',
                'price' => 50000,
                'unit' => 'kendaraan',
                'service_type' => 'drop_off',
                'estimated_duration' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Detailing Interior',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC1->id,
                'description' => 'Pembersihan menyeluruh jok, plafon, karpet dasar, sela AC, dan sterilisasi kabin dari noda maupun aroma tidak sedap.',
                'price' => 275000,
                'unit' => 'unit',
                'service_type' => 'both',
                'estimated_duration' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan Eksterior Mobil',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC2->id,
                'description' => 'Pembersihan jamur kaca, penghilang aspal bodi, dan aplikasi wax pelindung kilap cat eksterior kendaraan.',
                'price' => 200000,
                'unit' => 'unit',
                'service_type' => 'both',
                'estimated_duration' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Detailing Mobil Lengkap',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC2->id,
                'description' => 'Restorasi cat bodi menghilangkan baret halus (swirl mark), poles multi-step, dan pembersihan ruang mesin tuntas.',
                'price' => 550000,
                'unit' => 'unit',
                'service_type' => 'appointment',
                'estimated_duration' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Nano Ceramic Coating',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC2->id,
                'description' => 'Aplikasi lapisan pelindung nano keramik berkualitas untuk kilau tahan lama dan efek hidrofobik tolak air ekstrem.',
                'price' => 1250000,
                'unit' => 'unit',
                'service_type' => 'appointment',
                'estimated_duration' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Helm',
                'branch_id' => $branchC->id,
                'service_category_id' => $catC3->id,
                'description' => 'Pembersihan busa padding helm, pemolesan kaca visor, pembersihan cangkang luar, dan sterilisasi ozon anti-bakteri.',
                'price' => 30000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 240,
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::updateOrCreate(
                ['name' => $serviceData['name'], 'branch_id' => $serviceData['branch_id']],
                $serviceData
            );
        }
    }
}
