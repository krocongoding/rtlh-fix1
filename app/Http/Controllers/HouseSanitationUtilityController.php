<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\HouseSanitation;
use App\Models\HouseUtility;
use App\Models\MasterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseSanitationUtilityController extends Controller
{
    public function edit(House $house)
    {
        $waterSourceCategory = MasterCategory::where(
            'code',
            'WATER_SOURCE'
        )->firstOrFail();

        $toiletTypeCategory = MasterCategory::where(
            'code',
            'TOILET_TYPE'
        )->firstOrFail();

        $fecalDisposalCategory = MasterCategory::where(
            'code',
            'FECAL_DISPOSAL'
        )->firstOrFail();

        $lightingSourceCategory = MasterCategory::where(
            'code',
            'LIGHTING_SOURCE'
        )->firstOrFail();

        $waterSources = $waterSourceCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $toiletTypes = $toiletTypeCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $fecalDisposals = $fecalDisposalCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $lightingSources = $lightingSourceCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $wastewaterDisposalCategory = MasterCategory::where(
            'code',
            'WASTEWATER_DISPOSAL'
        )->first();

        $wastewaterDisposals = $wastewaterDisposalCategory ? $wastewaterDisposalCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get() : collect();

        $house->load([
            'sanitation.waterSource',
            'sanitation.wastewaterDisposal',
            'sanitation.toiletType',
            'sanitation.fecalDisposalType',
            'utility.lightingSource',
        ]);

        return view(
            'houses.sanitation-utility',
            compact(
                'house',
                'waterSources',
                'wastewaterDisposals',
                'toiletTypes',
                'fecalDisposals',
                'lightingSources'
            )
        );
    }

    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'water_source_id' => [
                'nullable',
                'exists:master_values,id',
            ],

            'wastewater_disposal_id' => [
                'nullable',
                'exists:master_values,id',
            ],

            'toilet_available' => [
                'required',
                'boolean',
            ],

            'toilet_type_id' => [
                'nullable',
                'exists:master_values,id',
            ],

            'fecal_disposal_type_id' => [
                'nullable',
                'exists:master_values,id',
            ],

            'water_fecal_distance' => [
                'nullable',
                'string',
                'max:255',
            ],

            'light_opening' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ventilation' => [
                'nullable',
                'string',
                'max:255',
            ],

            'lighting_source_id' => [
                'nullable',
                'exists:master_values,id',
            ],
        ]);

        DB::transaction(function () use ($house, $validated) {

            HouseSanitation::updateOrCreate(
                ['house_id' => $house->id],
                [
                    'water_source_id' =>
                        $validated['water_source_id'] ?? null,

                    'wastewater_disposal_id' =>
                        $validated['wastewater_disposal_id'] ?? null,

                    'toilet_available' =>
                        $validated['toilet_available'],

                    'toilet_type_id' =>
                        $validated['toilet_type_id'] ?? null,

                    'fecal_disposal_type_id' =>
                        $validated['fecal_disposal_type_id'] ?? null,

                    'water_fecal_distance' =>
                        $validated['water_fecal_distance'] ?? null,
                ]
            );

            HouseUtility::updateOrCreate(
                ['house_id' => $house->id],
                [
                    'light_opening' =>
                        $validated['light_opening'] ?? null,

                    'ventilation' =>
                        $validated['ventilation'] ?? null,

                    'lighting_source_id' =>
                        $validated['lighting_source_id'] ?? null,
                ]
            );
        });

        return redirect()
            ->route('houses.show', $house)
            ->with(
                'success',
                'Data sanitasi dan utilitas berhasil disimpan.'
            );
    }
}