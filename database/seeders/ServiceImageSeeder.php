<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceImageSeeder extends Seeder
{
    /**
     * Ekstensi file gambar yang didukung sistem layanan.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_EXTENSIONS = ['webp', 'png', 'jpg', 'jpeg'];

    /**
     * Slot gambar standar untuk setiap layanan (slot 1, 2, 3).
     *
     * @var array<int, string>
     */
    public const SLOTS = ['1', '2', '3'];

    /**
     * Direktori penyimpanan gambar untuk setiap nama layanan.
     *
     * @var array<string, string>
     */
    protected array $serviceDirectories = [
        // Cabang A: Barbershop & Studio
        'Potong Rambut Pria' => 'services/barbershop/potong-rambut-pria',
        'Potong Rambut & Styling' => 'services/barbershop/potong-rambut-styling',
        'Cukur & Rapikan Jenggot' => 'services/barbershop/cukur-rapikan-jenggot',
        'Perawatan Kulit Kepala' => 'services/barbershop/perawatan-kulit-kepala',
        'Pewarnaan Rambut' => 'services/barbershop/pewarnaan-rambut',
        'Paket Grooming Lengkap' => 'services/barbershop/paket-grooming-lengkap',

        // Cabang B: Laundry & Shoe Care
        'Cuci Kering Setrika' => 'services/laundry/cuci-kering-setrika',
        'Laundry Express' => 'services/laundry/laundry-express',
        'Deep Clean Sepatu' => 'services/laundry/deep-clean-sepatu',
        'Dry Clean Jas & Kebaya' => 'services/laundry/dry-clean-jas-kebaya',
        'Cuci Bed Cover' => 'services/laundry/cuci-bed-cover',
        'Cuci Karpet' => 'services/laundry/cuci-karpet',

        // Cabang C: Auto Care & Detailing
        'Cuci Mobil Premium' => 'services/auto-care/cuci-mobil-premium',
        'Detailing Mobil Lengkap' => 'services/auto-care/detailing-mobil-lengkap',
        'Detailing Interior' => 'services/auto-care/detailing-interior',
        'Nano Ceramic Coating' => 'services/auto-care/nano-ceramic-coating',
        'Cuci Helm' => 'services/auto-care/cuci-helm',
        'Perawatan Eksterior Mobil' => 'services/auto-care/perawatan-eksterior-mobil',
    ];

    /**
     * Resolusi path file aktual untuk slot tertentu di direktori layanan.
     * Mendukung format flat standard: services/{branch}/{slug}-{slot}.{ext}
     * dan format slot: .webp, .png, .jpg, .jpeg
     */
    public function resolveSlotImagePath(string $dir, string $slot): ?string
    {
        $disk = Storage::disk('public');
        $slotNum = (int) $slot;
        $slotFormatted = sprintf('%02d', $slotNum);

        $parentDir = dirname($dir);
        $slug = basename($dir);

        // Daftar kombinasi kandidat path relatif yang mungkin
        $candidates = [];
        foreach (self::SUPPORTED_EXTENSIONS as $ext) {
            // 1. Format utama flat: services/barbershop/potong-rambut-pria-1.jpg
            $candidates[] = "{$parentDir}/{$slug}-{$slotNum}.{$ext}";
            $candidates[] = "{$parentDir}/{$slug}-{$slotFormatted}.{$ext}";
            $candidates[] = "{$dir}-{$slotNum}.{$ext}";
            $candidates[] = "{$dir}-{$slotFormatted}.{$ext}";

            // 2. Format subfolder: services/barbershop/potong-rambut-pria/01.jpg
            $candidates[] = "{$dir}/{$slotFormatted}.{$ext}";
            $candidates[] = "{$dir}/{$slotNum}.{$ext}";
            $candidates[] = "{$dir}/{$slot}.{$ext}";
        }

        foreach ($candidates as $candidate) {
            if ($disk->exists($candidate)) {
                return $candidate;
            }

            if (file_exists(storage_path('app/public/'.$candidate))) {
                return $candidate;
            }

            if (file_exists(public_path('storage/'.$candidate))) {
                return $candidate;
            }
        }

        // Scan direktori parent jika ada variasi nama file
        $searchDirs = [$parentDir, $dir];
        foreach ($searchDirs as $sDir) {
            if ($disk->exists($sDir)) {
                $files = $disk->files($sDir);
                foreach ($files as $file) {
                    $normalized = str_replace('\\', '/', $file);
                    $filename = pathinfo($normalized, PATHINFO_FILENAME);
                    $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));

                    if (
                        ($filename === "{$slug}-{$slotNum}" || $filename === "{$slug}-{$slot}" || $filename === $slot || $filename === $slotFormatted)
                        && in_array($ext, self::SUPPORTED_EXTENSIONS, true)
                    ) {
                        return $normalized;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Dapatkan direktori penyimpanan layanan.
     */
    public function getServiceDirectory(Service $service): string
    {
        if (isset($this->serviceDirectories[$service->name])) {
            return $this->serviceDirectories[$service->name];
        }

        $serviceSlug = Str::slug($service->name);
        $branchName = strtolower($service->branch?->name ?? '');

        $branchFolder = 'barbershop';
        if (str_contains($branchName, 'selatan') || str_contains($branchName, 'laundry')) {
            $branchFolder = 'laundry';
        } elseif (str_contains($branchName, 'barat') || str_contains($branchName, 'auto')) {
            $branchFolder = 'auto-care';
        }

        $candidates = [
            "services/{$branchFolder}/{$serviceSlug}",
            "services/barbershop/{$serviceSlug}",
            "services/laundry/{$serviceSlug}",
            "services/auto-care/{$serviceSlug}",
            "services/autocare/{$serviceSlug}",
        ];

        foreach ($candidates as $candidate) {
            if (
                Storage::disk('public')->exists($candidate)
                || is_dir(storage_path('app/public/'.$candidate))
                || $this->resolveSlotImagePath($candidate, '1') !== null
            ) {
                return $candidate;
            }
        }

        return "services/{$branchFolder}/{$serviceSlug}";
    }

    /**
     * Seed 3 gambar relevan lokal untuk setiap layanan aktif dengan ekstensi aktual.
     */
    public function run(): void
    {
        $activeServices = Service::where('is_active', true)->get();
        $processedServiceIds = [];

        foreach ($activeServices as $service) {
            $target = $this->getServiceDirectory($service);
            $processedServiceIds[] = $service->id;

            $firstPath = null;

            foreach (self::SLOTS as $index => $slot) {
                $sortOrder = $index + 1;
                $resolvedPath = $this->resolveSlotImagePath($target, (string) $sortOrder);

                $parentDir = dirname($target);
                $slug = basename($target);

                // Gunakan path file aktual jika ada, atau fallback terstruktur
                $path = $resolvedPath ?? "{$parentDir}/{$slug}-{$sortOrder}.".self::SUPPORTED_EXTENSIONS[2];

                if ($sortOrder === 1) {
                    $firstPath = $path;
                }

                ServiceImage::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'sort_order' => $sortOrder,
                    ],
                    [
                        'image_path' => $path,
                    ]
                );
            }

            // Set fallback thumbnail pada model Service
            if ($firstPath) {
                $service->update(['image_path' => $firstPath]);
            }

            // Hapus image dengan sort_order > 3 jika ada
            ServiceImage::where('service_id', $service->id)
                ->where('sort_order', '>', 3)
                ->delete();
        }

        if (! empty($processedServiceIds)) {
            ServiceImage::whereNotIn('service_id', $processedServiceIds)->delete();
        }
    }
}
