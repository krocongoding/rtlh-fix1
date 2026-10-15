<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterCategory;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncApiController extends Controller
{
    /**
     * Ambil semua master data sekaligus untuk di-cache di Android (Room).
     *
     * GET /api/v1/surveyor/sync/master-data
     *
     * Response berisi semua kategori beserta nilai-nilainya,
     * dikelompokkan per kode kategori supaya mudah di-lookup.
     *
     * Contoh penggunaan di Android:
     *   val floorMaterials = masterData["FLOOR_MATERIAL"]
     */
    public function masterData(): JsonResponse
    {
        $categories = MasterCategory::with([
            'values' => fn ($q) => $q
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->select(['id', 'master_category_id', 'code', 'label', 'sort_order']),
        ])
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        // Format: { "FLOOR_MATERIAL": [ {id, code, label}, ... ], ... }
        $data = $categories->mapWithKeys(fn ($cat) => [
            $cat->code => $cat->values->map(fn ($v) => [
                'id'    => $v->id,
                'code'  => $v->code,
                'label' => $v->label,
            ])->values(),
        ]);

        return response()->json([
            'data'       => $data,
            'synced_at'  => now()->toIso8601String(),
        ]);
    }

    /**
     * Ambil semua wilayah aktif untuk di-cache di Android.
     *
     * GET /api/v1/surveyor/sync/regions
     *
     * Struktur hierarki:
     *   level 1 = kecamatan
     *   level 2 = desa/kelurahan (parent_id → kecamatan)
     */
    public function regions(): JsonResponse
    {
        $regions = Region::kotaCirebon()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'code', 'name', 'type', 'level']);

        return response()->json([
            'data'      => $regions->map(fn ($r) => [
                'id'        => $r->id,
                'parent_id' => $r->parent_id,
                'code'      => $r->code,
                'name'      => $r->name,
                'type'      => $r->type,
                'level'     => $r->level,
            ])->values(),
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
