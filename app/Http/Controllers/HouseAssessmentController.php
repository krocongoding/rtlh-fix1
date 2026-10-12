<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\MasterCategory;
use App\Models\RtlhAssessment;
use App\Models\RtlhAssessmentItem;
use App\Services\HouseAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseAssessmentController extends Controller
{
    public function edit(House $house, HouseAssessmentService $assessmentService)
    {
        $assessment = RtlhAssessment::with('items')
            ->where('house_id', $house->id)
            ->latest('assessment_year')
            ->latest('id')
            ->first();

        // Hitung skor dari data survey rumah sebagai referensi/default
        $surveyScoreData = $assessmentService->calculateHouseSurveyScores($house);

        $categories = MasterCategory::with([
            'values' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->orderBy('id')
        ->get();

        return view('houses.assessment', compact(
            'house',
            'assessment',
            'categories',
            'surveyScoreData'
        ));
    }

    public function update(Request $request, House $house)
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
                'max:50',
            ],

            'score' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'priority_level' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.category' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.item_code' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.value' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.other_value' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.score' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($house, $validated) {
            $computedItemScores = [];
            $totalScore = 0;

            foreach ($validated['items'] ?? [] as $item) {
                $val = $item['value'] ?? null;
                // Hitung skor otomatis untuk setiap item
                // BAIK = 0, RUSAK RINGAN = 1, RUSAK SEDANG = 2, RUSAK BERAT = 3
                $itemScore = isset($item['score']) && $item['score'] !== '' && $item['score'] !== null
                    ? (float)$item['score']
                    : HouseAssessmentService::getScoreForCodeOrLabel($val);

                $computedItemScores[] = [
                    'category' => $item['category'],
                    'item_code' => $item['item_code'],
                    'value' => $val,
                    'other_value' => strtoupper($val ?? '') === 'OTHER' ? ($item['other_value'] ?? null) : null,
                    'score' => $itemScore,
                    'notes' => $item['notes'] ?? null,
                ];

                $totalScore += $itemScore;
            }

            // Gunakan skor yang dikirim atau total skor yang dihitung otomatis
            $finalScore = ($validated['score'] !== null && $validated['score'] !== '')
                ? (float)$validated['score']
                : $totalScore;

            // Prioritas otomatis jika tidak dipilih manual
            $priority = $validated['priority_level'] ?? null;
            if (!$priority) {
                if ($finalScore >= 12) {
                    $priority = 'tinggi';
                } elseif ($finalScore >= 6) {
                    $priority = 'sedang';
                } else {
                    $priority = 'rendah';
                }
            }

            $assessment = RtlhAssessment::updateOrCreate(
                [
                    'house_id' => $house->id,
                    'assessment_year' => $validated['assessment_year'],
                ],
                [
                    'assessment_date' => $validated['assessment_date'],
                    'assessor_id' => auth()->id(),
                    'status' => $validated['status'],
                    'score' => $finalScore,
                    'priority_level' => $priority,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            /*
             * Update detail item assessment
             */
            RtlhAssessmentItem::where(
                'assessment_id',
                $assessment->id
            )->delete();

            foreach ($computedItemScores as $itemData) {
                RtlhAssessmentItem::create([
                    'assessment_id' => $assessment->id,
                    'category' => $itemData['category'],
                    'item_code' => $itemData['item_code'],
                    'value' => $itemData['value'],
                    'other_value' => $itemData['other_value'],
                    'score' => $itemData['score'],
                    'notes' => $itemData['notes'],
                ]);
            }
        });

        return redirect()
            ->route('houses.show', $house)
            ->with(
                'success',
                'Assessment RTLH & Penilaian Skor Otomatis berhasil disimpan.'
            );
    }
}
