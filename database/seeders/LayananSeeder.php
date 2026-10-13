<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Jalankan seeder kategori dan katalog layanan terpisah untuk tiap cabang (Tepat 6 layanan per cabang = 18 layanan).
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

        // Kategori Layanan Cabang A (Barbershop & Studio)
        $catA1 = ServiceCategory::firstOrCreate(['name' => 'Potong Rambut & Styling']);
        $catA2 = ServiceCategory::firstOrCreate(['name' => 'Grooming & Cukur']);
        $catA3 = ServiceCategory::firstOrCreate(['name' => 'Perawatan Rambut & Kulit Kepala']);

        // Kategori Layanan Cabang B (Laundry & Shoe Care)
        $catB1 = ServiceCategory::firstOrCreate(['name' => 'Laundry Kiloan & Reguler']);
        $catB2 = ServiceCategory::firstOrCreate(['name' => 'Pembersihan Sepatu & Tas']);
        $catB3 = ServiceCategory::firstOrCreate(['name' => 'Dry Clean Busana Formal']);

        // Kategori Layanan Cabang C (Auto Care & Detailing)
        $catC1 = ServiceCategory::firstOrCreate(['name' => 'Cuci Mobil & Interior']);
        $catC2 = ServiceCategory::firstOrCreate(['name' => 'Proteksi Cat & Coating']);
        $catC3 = ServiceCategory::firstOrCreate(['name' => 'Perawatan Helm & Aksesoris']);

        // 2. Sinkronisasi Rename Service Lama ke Bahasa Indonesia
        $serviceRenames = [
            // Cabang A
            'Gentlemen Executive Haircut & Styling' => 'Potong Rambut Pria',
            'Signature Beard Sculpting & Hot Towel' => 'Cukur & Rapikan Jenggot',
            'Keratin Scalp Therapy & Anti-Dandruff' => 'Perawatan Kulit Kepala',
            'Hair Coloring & Bleaching Highlight' => 'Pewarnaan Rambut',
            'Royal Full Grooming & Face Massage' => 'Paket Grooming Lengkap',

            // Cabang B
            'Cuci Kering Setrika Express 5kg' => 'Cuci Kering Setrika',
            'Deep Clean Sepatu Sneakers & Leather' => 'Deep Clean Sepatu',
            'Dry Clean Jas Formal & Kebaya Pesta' => 'Dry Clean Jas & Kebaya',
            'Cuci Bed Cover Jumbo & Karpet Tebal' => 'Cuci Bed Cover',
            'Cuci Karpet Permadani & Masjid Premium' => 'Cuci Karpet',

            // Cabang C
            'Premium Car Wash & Fogging Interior' => 'Cuci Mobil Premium',
            'Full Auto Detailing & Multi-Stage Paint Correction' => 'Detailing Mobil Lengkap',
            'Interior Deep Clean & Leather Treatment' => 'Detailing Interior',
            'Nano Ceramic Coating Proteksi 1 Layer' => 'Nano Ceramic Coating',
            'Cuci Helm Full Face & Disinfeksi Ozon' => 'Cuci Helm',
            'Cuci Helm Full Face & Disinfeksi Ozon (Drop-off)' => 'Cuci Helm',
        ];
        foreach ($serviceRenames as $old => $new) {
            $oldServices = Service::where('name', $old)->get();
            foreach ($oldServices as $oldService) {
                $existingTarget = Service::where('name', $new)
                    ->where('branch_id', $oldService->branch_id)
                    ->where('id', '!=', $oldService->id)
                    ->first();

                if ($existingTarget) {
                    $hasRelations = $oldService->orders()->exists()
                        || $oldService->orderDetails()->exists()
                        || $oldService->bookings()->exists()
                        || $oldService->bookingDetails()->exists()
                        || $oldService->transactionDetails()->exists()
                        || $oldService->ulasans()->exists()
                        || $oldService->images()->exists()
                        || $oldService->employeeCommissions()->exists();

                    if (! $hasRelations && ! $oldService->is_active) {
                        $oldService->delete();
                    }
                } else {
                    $oldService->update(['name' => $new]);
                }
            }
        }

        // ==========================================
        // LAYANAN CABANG A: PUSAT (Mode Appointment - 6 Layanan)
        // ==========================================
        Service::updateOrCreate(
            ['name' => 'Potong Rambut Pria', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA1->id,
                'description' => 'Potong rambut pria dengan teknik presisi, konsultasi gaya, dan finishing rapi.',
                'price' => 95000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 45,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Potong Rambut & Styling', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA1->id,
                'description' => 'Paket potong rambut lengkap dengan cuci rambut menyegarkan dan penataan pomade premium.',
                'price' => 110000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 50,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Cukur & Rapikan Jenggot', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA2->id,
                'description' => 'Perapihan dan pembentukan kumis serta jenggot dengan handuk hangat aromaterapi dan razor shave presisi.',
                'price' => 65000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 30,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Perawatan Kulit Kepala', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA3->id,
                'description' => 'Perawatan intensif pembersihan ketombe dan stimulasi akar rambut dengan serum keratin serta pijat relaksasi.',
                'price' => 160000,
                'unit' => 'treatment',
                'service_type' => 'appointment',
                'estimated_duration' => 60,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Pewarnaan Rambut', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA1->id,
                'description' => 'Pewarnaan rambut profesional menggunakan cat berkualitas aman untuk menghasilkan warna natural maupun modern.',
                'price' => 185000,
                'unit' => 'treatment',
                'service_type' => 'appointment',
                'estimated_duration' => 90,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Paket Grooming Lengkap', 'branch_id' => $branchA->id],
            [
                'service_category_id' => $catA2->id,
                'description' => 'Layanan komplit mencakup potong rambut, cukur jenggot, pembersihan komedo, dan totok wajah menyegarkan.',
                'price' => 125000,
                'unit' => 'sesi',
                'service_type' => 'appointment',
                'estimated_duration' => 60,
                'is_active' => true,
            ]
        );

        // ==========================================
        // LAYANAN CABANG B: SELATAN (Mode Drop-off - 6 Layanan)
        // ==========================================
        Service::updateOrCreate(
            ['name' => 'Cuci Kering Setrika', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB1->id,
                'description' => 'Layanan cuci bersih pakaian harian tuntas dengan setrika uap higienis dan kemasan plastik rapi.',
                'price' => 65000,
                'unit' => 'kg',
                'service_type' => 'drop_off',
                'estimated_duration' => 180,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Laundry Express', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB1->id,
                'description' => 'Layanan pencucian cepat selesai dalam hitungan jam dengan prioritas pengerjaan dan hasil bersih maksimal.',
                'price' => 85000,
                'unit' => 'kg',
                'service_type' => 'drop_off',
                'estimated_duration' => 120,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Deep Clean Sepatu', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB2->id,
                'description' => 'Pencucian menyeluruh luar dan dalam sepatu sneakers, kanvas, maupun kulit menggunakan pembersih khusus material.',
                'price' => 80000,
                'unit' => 'pasang',
                'service_type' => 'drop_off',
                'estimated_duration' => 1440,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Dry Clean Jas & Kebaya', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB3->id,
                'description' => 'Perawatan busana formal, setelan jas, dan kebaya pesta dengan teknologi dry clean tanpa merusak serat kain.',
                'price' => 120000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 2880,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Cuci Bed Cover', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB1->id,
                'description' => 'Pencucian sprei kasur ukuran besar dan selimut tebal dengan teknologi pembersih anti-bakteri dan ekstraksi tungau.',
                'price' => 95000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 1440,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Cuci Karpet', 'branch_id' => $branchB->id],
            [
                'service_category_id' => $catB1->id,
                'description' => 'Pencucian karpet rumah dan permadani dengan mesin polisher berputar serta proses pengeringan bebas bau apek.',
                'price' => 85000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 2880,
                'is_active' => true,
            ]
        );

        // ==========================================
        // LAYANAN CABANG C: BARAT (Mode Both - 6 Layanan)
        // ==========================================
        Service::updateOrCreate(
            ['name' => 'Cuci Mobil Premium', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC1->id,
                'description' => 'Cuci mobil salju pH netral menyeluruh, pembersihan sela pintu, semir ban, dan pengeringan lap microfiber lembut.',
                'price' => 90000,
                'unit' => 'kendaraan',
                'service_type' => 'drop_off',
                'estimated_duration' => 45,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Detailing Mobil Lengkap', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC2->id,
                'description' => 'Restorasi cat bodi menghilangkan baret halus (swirl mark), poles multi-step, dan pembersihan ruang mesin tuntas.',
                'price' => 450000,
                'unit' => 'unit',
                'service_type' => 'appointment',
                'estimated_duration' => 150,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Detailing Interior', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC1->id,
                'description' => 'Pembersihan menyeluruh jok, plafon, karpet dasar, sela AC, dan sterilisasi kabin dari noda maupun aroma tidak sedap.',
                'price' => 220000,
                'unit' => 'unit',
                'service_type' => 'both',
                'estimated_duration' => 90,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Nano Ceramic Coating', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC2->id,
                'description' => 'Aplikasi lapisan pelindung nano keramik berkualitas untuk kilau tahan lama dan efek hidrofobik tolak air ekstrem.',
                'price' => 650000,
                'unit' => 'unit',
                'service_type' => 'appointment',
                'estimated_duration' => 180,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Cuci Helm', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC3->id,
                'description' => 'Pembersihan busa padding helm, pemolesan kaca visor, pembersihan cangkang luar, dan sterilisasi ozon anti-bakteri.',
                'price' => 55000,
                'unit' => 'item',
                'service_type' => 'drop_off',
                'estimated_duration' => 240,
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Perawatan Eksterior Mobil', 'branch_id' => $branchC->id],
            [
                'service_category_id' => $catC2->id,
                'description' => 'Pembersihan jamur kaca, penghilang aspal bodi, dan aplikasi wax pelindung kilap cat eksterior kendaraan.',
                'price' => 175000,
                'unit' => 'unit',
                'service_type' => 'both',
                'estimated_duration' => 60,
                'is_active' => true,
            ]
        );
    }
}
