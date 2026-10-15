<?php

namespace App\Services\Katalog;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LayananService
{
    /**
     * Semua kategori layanan.
     */
    public function getCategories(bool $activeOnly = false): Collection
    {
        $query = ServiceCategory::query()->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): ServiceCategory
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        return ServiceCategory::create($data);
    }

    /**
     * Perbarui kategori.
     */
    public function updateCategory(ServiceCategory $category, array $data): ServiceCategory
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : $category->is_active;

        $category->update($data);

        return $category;
    }

    /**
     * Toggle category active status.
     */
    public function toggleCategoryStatus(ServiceCategory $category): ServiceCategory
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category;
    }

    /**
     * Daftar layanan terfilter.
     */
    public function getServicesForUser(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $roleName = $user->role?->name;
        $query = Service::query()->with(['category', 'branch'])->latest();

        if ($roleName === 'manager') {
            $branchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->where(function (Builder $q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds)
                    ->orWhereNull('branch_id');
            });
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Daftar layanan aktif.
     */
    public function getPublicCatalog(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Service::query()
            ->with(['category', 'branch', 'images'])
            ->where('is_active', true)
            ->whereHas('category', function (Builder $q) {
                $q->where('is_active', true);
            })
            ->latest();

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Layanan katalog landing page: 9 card untuk Semua Cabang (tepat 3 card per cabang), atau 6 card untuk filter cabang tertentu.
     *
     * @return Collection<int, Service>
     */
    public function getLandingCatalog(?int $branchId = null): Collection
    {
        if ($branchId) {
            return Service::query()
                ->with(['category', 'branch', 'images'])
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->whereHas('category', function (Builder $q) {
                    $q->where('is_active', true);
                })
                ->orderBy('id')
                ->get();
        }

        $representativeNames = [
            // Cabang A (Barbershop)
            'Potong Rambut Pria',
            'Cukur & Rapikan Jenggot',
            'Paket Grooming Lengkap',
            // Cabang B (Laundry & Shoe Care)
            'Cuci Kering Setrika',
            'Deep Clean Sepatu',
            'Cuci Karpet',
            // Cabang C (Auto Care & Detailing)
            'Cuci Mobil Premium',
            'Detailing Mobil Lengkap',
            'Nano Ceramic Coating',
        ];

        $branches = Branch::where('is_active', true)->orderBy('id')->get();
        $landingServices = new Collection;

        foreach ($branches as $branch) {
            $branchServices = Service::query()
                ->with(['category', 'branch', 'images'])
                ->where('branch_id', $branch->id)
                ->where('is_active', true)
                ->whereHas('category', function (Builder $q) {
                    $q->where('is_active', true);
                })
                ->orderBy('id')
                ->get();

            // Prioritaskan 3 layanan representatif per cabang
            $picked = $branchServices->filter(fn ($s) => in_array($s->name, $representativeNames))
                ->sortBy(fn ($s) => array_search($s->name, $representativeNames));

            if ($picked->count() < 3) {
                $remaining = $branchServices->reject(fn ($s) => $picked->contains('id', $s->id));
                $picked = $picked->merge($remaining);
            }

            foreach ($picked->take(3) as $service) {
                $landingServices->push($service);
            }
        }

        return $landingServices;
    }

    /**
     * Terapkan filter pencarian.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('service_category_id', $filters['category_id']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }
    }

    /**
     * Layanan unggulan landing page.
     */
    public function getFeaturedService(?int $branchId = null): ?Service
    {
        $query = Service::query()
            ->with(['category', 'branch'])
            ->where('is_active', true);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $featuredByRating = (clone $query)
            ->whereHas('ulasans')
            ->withAvg('ulasans', 'rating')
            ->orderByDesc('ulasans_avg_rating')
            ->first();

        if ($featuredByRating) {
            return $featuredByRating;
        }

        return $query->orderByDesc('price')->first();
    }

    /**
     * Create a new service.
     */
    public function createService(array $data, mixed $imageFile = null): Service
    {
        if ($imageFile) {
            $data['image_path'] = $imageFile->store('services', 'public');
        }

        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        return Service::create($data);
    }

    /**
     * Perbarui layanan.
     */
    public function updateService(Service $service, array $data, mixed $imageFile = null): Service
    {
        if ($imageFile) {
            $data['image_path'] = $imageFile->store('services', 'public');
        }

        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : $service->is_active;

        $service->update($data);

        return $service;
    }

    /**
     * Toggle service active status.
     */
    public function toggleServiceStatus(Service $service): Service
    {
        $service->update(['is_active' => ! $service->is_active]);

        return $service;
    }

    /**
     * Otorisasi akses layanan pengguna.
     *
     * @throws AuthorizationException
     */
    public function authorizeServiceAccess(User $user, Service $service): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager') {
            if (is_null($service->branch_id) || $user->branches()->where('branches.id', $service->branch_id)->exists()) {
                return;
            }
        }

        throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengelola layanan ini.');
    }
}
