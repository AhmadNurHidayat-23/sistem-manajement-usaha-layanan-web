<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMobileController extends Controller
{
    /**
     * Daftar cabang aktif.
     */
    public function branches(Request $request): JsonResponse
    {
        $query = Branch::where('is_active', true)->orderBy('name');

        if ($request->filled('service_mode')) {
            $mode = $request->input('service_mode');
            $query->whereIn('service_mode', [$mode, 'both']);
        }

        $branches = $query->get(['id', 'name', 'address', 'phone', 'opening_time', 'closing_time', 'service_mode', 'max_slot_capacity']);

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Katalog layanan aktif dengan filter cabang dan pencarian.
     */
    public function services(Request $request): JsonResponse
    {
        $query = Service::with(['category', 'branch', 'images'])
            ->where('is_active', true);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('service_category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $searchTerm = '%'.$request->input('q').'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $services = $query->orderBy('name')->get();

        $data = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => (float) $service->price,
                'unit' => $service->unit ?? 'sesi',
                'estimated_duration' => $service->estimated_duration,
                'duration_formatted' => $service->formatted_duration,
                'is_weight_based' => $service->isWeightBased(),
                'service_type' => $service->service_type,
                'category' => $service->category ? [
                    'id' => $service->category->id,
                    'name' => $service->category->name,
                ] : null,
                'branch' => $service->branch ? [
                    'id' => $service->branch->id,
                    'name' => $service->branch->name,
                ] : null,
                'image_url' => $service->image_url,
                'images' => $service->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'image_url' => $img->image_url,
                        'sort_order' => $img->sort_order,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
