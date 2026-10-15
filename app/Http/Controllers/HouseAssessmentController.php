<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\HouseCeiling;
use App\Models\HouseFloor;
use App\Models\HouseRoof;
use App\Models\HouseStructure;
use App\Models\HouseWall;
use App\Models\MasterCategory;
use App\Models\RtlhAssessment;
use App\Services\HouseAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseAssessmentController extends Controller
{
    public function edit(House $house, HouseAssessmentService $assessmentService)
    {
        $house->load([
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'roof.frameCondition',
            'floor.condition',
            'wall.condition',
            'ceiling.condition',
            'roof.condition',
        ]);

        $assessment = RtlhAssessment::with('items')
            ->where('house_id', $house->id)
            ->latest('assessment_year')
            ->latest('id')
            ->first();

        // Hitung skor dari data survei fisik rumah sebagai referensi/default
        $surveyScoreData = $assessmentService->calculateHouseSurveyScores($house);

        // Ambil opsi standar kondisi rumah: Baik (0), Rusak Ringan (1), Rusak Sedang (2), Rusak Berat (3)
        $houseConditionCategory = MasterCategory::where('code', 'HOUSE_CONDITION')->first();
        $conditions = $houseConditionCategory ? $houseConditionCategory->values()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get() : collect();

        // 8 Komponen fisik yang dinilai
        $components = $surveyScoreData['items'];

        // Map catatan per item yang sudah ada
        $existingItemNotes = $assessment
            ? $assessment->items->pluck('notes', 'item_code')->toArray()
            : [];

        return view('houses.assessment', compact(
            'house',
            'assessment',
            'conditions',
            'components',
            'surveyScoreData',
            'existingItemNotes'
        ));
    }

    public function update(Request $request, House $house, HouseAssessmentService $assessmentService)
    {
        $validated = $request->validate([
            'assessment_year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'assessment_date' => [
                'required',
                'date',
            ],
            'status' => [
                'required',
                'string',
                'in:draft,completed,verified',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'conditions' => [
                'required',
                'array',
            ],
            'conditions.structure_sloof' => ['nullable', 'exists:master_values,id'],
            'conditions.structure_column' => ['nullable', 'exists:master_values,id'],
            'conditions.structure_beam' => ['nullable', 'exists:master_values,id'],
            'conditions.roof_frame_condition' => ['nullable', 'exists:master_values,id'],
            'conditions.floor_condition' => ['nullable', 'exists:master_values,id'],
            'conditions.wall_condition' => ['nullable', 'exists:master_values,id'],
            'conditions.ceiling_condition' => ['nullable', 'exists:master_values,id'],
            'conditions.roof_condition' => ['nullable', 'exists:master_values,id'],
            'item_notes' => [
                'nullable',
                'array',
            ],
            'item_notes.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        DB::transaction(function () use ($house, $validated, $assessmentService) {
            $conds = $validated['conditions'] ?? [];

            // 1. Sinkronkan kondisi ke tabel fisik rumah (Single Source of Truth)
            HouseStructure::updateOrCreate(
                ['house_id' => $house->id],
                [
                    'sloof_condition_id' => $conds['structure_sloof'] ?? null,
                    'column_condition_id' => $conds['structure_column'] ?? null,
                    'beam_condition_id' => $conds['structure_beam'] ?? null,
                ]
            );

            HouseRoof::updateOrCreate(
                ['house_id' => $house->id],
                [
                    'frame_condition_id' => $conds['roof_frame_condition'] ?? null,
                    'condition_id' => $conds['roof_condition'] ?? null,
                ]
            );

            HouseFloor::updateOrCreate(
                ['house_id' => $house->id],
                ['condition_id' => $conds['floor_condition'] ?? null]
            );

            HouseWall::updateOrCreate(
                ['house_id' => $house->id],
                ['condition_id' => $conds['wall_condition'] ?? null]
            );

            HouseCeiling::updateOrCreate(
                ['house_id' => $house->id],
                ['condition_id' => $conds['ceiling_condition'] ?? null]
            );

            // 2. Perbarui tahun survei rumah jika disesuaikan
            if (!empty($validated['assessment_year']) && $house->survey_year != $validated['assessment_year']) {
                $house->update(['survey_year' => $validated['assessment_year']]);
            }

            // 3. Sinkronkan penilaian skor otomatis & item assessment
            $assessmentService->syncAssessment(
                $house,
                auth()->id(),
                $validated['notes'] ?? null,
                $validated['item_notes'] ?? [],
                $validated['assessment_date'],
                $validated['status']
            );
        });

        return redirect()
            ->route('houses.show', $house)
            ->with('success', 'Penilaian Assessment RTLH berhasil disimpan dan disinkronkan dengan data fisik rumah.');
    }
}
