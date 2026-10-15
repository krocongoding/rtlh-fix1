<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileMasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | WILAYAH
        |--------------------------------------------------------------------------
        */

        $districts = DB::table('regions')
            ->where('code', 'like', '32.74.%')
            ->where('level', 1)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'type',
                'level',
            ]);

        $villages = DB::table('regions')
            ->where('code', 'like', '32.74.%')
            ->where('level', 2)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'code',
                'name',
                'type',
                'level',
            ]);

        /*
        |--------------------------------------------------------------------------
        | MASTER ASSESSMENT
        |--------------------------------------------------------------------------
        */

        $categories = DB::table('master_categories')
            ->where('is_active', true)
            ->orderBy('id')
            ->get([
                'id',
                'code',
                'name',
                'description',
            ]);

        $values = DB::table('master_values')
            ->where('is_active', true)
            ->orderBy('master_category_id')
            ->orderBy('sort_order')
            ->get([
                'id',
                'master_category_id',
                'code',
                'label',
                'sort_order',
            ]);

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'Master data berhasil diambil.',

            'data' => [
                'regions' => [
                    'districts' => $districts,
                    'villages' => $villages,
                ],

                'assessment' => [
                    'categories' => $categories,
                    'values' => $values,
                ],
            ],

            'meta' => [
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}