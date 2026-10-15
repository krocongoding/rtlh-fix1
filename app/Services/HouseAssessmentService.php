<?php

namespace App\Services;

use App\Models\House;
use App\Models\MasterValue;
use App\Models\RtlhAssessment;
use App\Models\RtlhAssessmentItem;
use Illuminate\Support\Facades\DB;

class HouseAssessmentService
{
    /**
     * Map kode atau label kondisi rumah ke nilai score standar.
     * BAIK = 0
     * RUSAK RINGAN (PERMUKAAN) = 1
     * RUSAK SEDANG (MATERIAL) = 2
     * RUSAK BERAT = 3
     */
    public const SCORE_MAP = [
        'GOOD' => 0,
        'BAIK' => 0,
        'RR_SURFACE' => 1,
        'RUSAK RINGAN (PERMUKAAN)' => 1,
        'RUSAK RINGAN' => 1,
        'RS_MATERIAL' => 2,
        'RUSAK SEDANG (MATERIAL)' => 2,
        'RUSAK SEDANG' => 2,
        'RB' => 3,
        'RUSAK BERAT' => 3,
    ];

    /**
     * Komponen fisik yang dinilai dalam survei rumah beserta kodenya.
     * 4 Komponen Struktural (Keselamatan Bangunan) dan 4 Komponen Arsitektural / Pengisi.
     */
    public const COMPONENT_DEFINITIONS = [
        'structure_sloof' => [
            'category' => 'STRUKTUR_SLOOF',
            'name' => 'Sloof / Balok Dasar',
            'is_structural' => true,
        ],
        'structure_column' => [
            'category' => 'STRUKTUR_KOLOM',
            'name' => 'Kolom / Tiang Penopang',
            'is_structural' => true,
        ],
        'structure_beam' => [
            'category' => 'STRUKTUR_BALOK',
            'name' => 'Balok Pengikat',
            'is_structural' => true,
        ],
        'roof_frame_condition' => [
            'category' => 'RANGKA_ATAP',
            'name' => 'Kondisi Rangka Atap',
            'is_structural' => true,
        ],
        'floor_condition' => [
            'category' => 'LANTAI',
            'name' => 'Kondisi Lantai',
            'is_structural' => false,
        ],
        'wall_condition' => [
            'category' => 'DINDING',
            'name' => 'Kondisi Dinding',
            'is_structural' => false,
        ],
        'ceiling_condition' => [
            'category' => 'PLAFON',
            'name' => 'Kondisi Plafon',
            'is_structural' => false,
        ],
        'roof_condition' => [
            'category' => 'PENUTUP_ATAP',
            'name' => 'Kondisi Penutup Atap',
            'is_structural' => false,
        ],
    ];

    /**
     * Dapatkan skor untuk sebuah ID master_values.
     */
    public static function getScoreForMasterValueId(?int $valueId): int
    {
        if (!$valueId) {
            return 0;
        }

        static $cachedValues = [];
        if (!isset($cachedValues[$valueId])) {
            $cachedValues[$valueId] = MasterValue::find($valueId);
        }

        $val = $cachedValues[$valueId];
        if (!$val) {
            return 0;
        }

        return self::getScoreForCodeOrLabel($val->code, $val->label, $val->sort_order);
    }

    /**
     * Dapatkan skor untuk code atau label.
     */
    public static function getScoreForCodeOrLabel(?string $code, ?string $label = null, ?int $sortOrder = null): int
    {
        $codeUpper = strtoupper(trim((string)$code));
        $labelUpper = strtoupper(trim((string)$label));

        if (isset(self::SCORE_MAP[$codeUpper])) {
            return self::SCORE_MAP[$codeUpper];
        }

        if (isset(self::SCORE_MAP[$labelUpper])) {
            return self::SCORE_MAP[$labelUpper];
        }

        if (str_contains($labelUpper, 'BERAT') || str_contains($codeUpper, 'RB')) {
            return 3;
        }

        if (str_contains($labelUpper, 'SEDANG') || str_contains($codeUpper, 'RS')) {
            return 2;
        }

        if (str_contains($labelUpper, 'RINGAN') || str_contains($codeUpper, 'RR')) {
            return 1;
        }

        if (str_contains($labelUpper, 'BAIK') || str_contains($codeUpper, 'GOOD')) {
            return 0;
        }

        return 0;
    }

    /**
     * Hitung rincian skor dan total untuk sebuah rumah dari 8 komponen fisik standarnya.
     */
    public function calculateHouseSurveyScores(House $house): array
    {
        $house->loadMissing([
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'roof.frameCondition',
            'floor.condition',
            'wall.condition',
            'ceiling.condition',
            'roof.condition',
        ]);

        $items = [];
        $totalScore = 0;
        $hasSevereStructuralDamage = false;

        $componentMap = [
            'structure_sloof' => [
                'category' => 'STRUKTUR_SLOOF',
                'name' => 'Sloof / Balok Dasar',
                'model' => $house->structure?->sloofCondition,
                'is_structural' => true,
            ],
            'structure_column' => [
                'category' => 'STRUKTUR_KOLOM',
                'name' => 'Kolom / Tiang Penopang',
                'model' => $house->structure?->columnCondition,
                'is_structural' => true,
            ],
            'structure_beam' => [
                'category' => 'STRUKTUR_BALOK',
                'name' => 'Balok Pengikat',
                'model' => $house->structure?->beamCondition,
                'is_structural' => true,
            ],
            'roof_frame_condition' => [
                'category' => 'RANGKA_ATAP',
                'name' => 'Kondisi Rangka Atap',
                'model' => $house->roof?->frameCondition,
                'is_structural' => true,
            ],
            'floor_condition' => [
                'category' => 'LANTAI',
                'name' => 'Kondisi Lantai',
                'model' => $house->floor?->condition,
                'is_structural' => false,
            ],
            'wall_condition' => [
                'category' => 'DINDING',
                'name' => 'Kondisi Dinding',
                'model' => $house->wall?->condition,
                'is_structural' => false,
            ],
            'ceiling_condition' => [
                'category' => 'PLAFON',
                'name' => 'Kondisi Plafon',
                'model' => $house->ceiling?->condition,
                'is_structural' => false,
            ],
            'roof_condition' => [
                'category' => 'PENUTUP_ATAP',
                'name' => 'Kondisi Penutup Atap',
                'model' => $house->roof?->condition,
                'is_structural' => false,
            ],
        ];

        foreach ($componentMap as $key => $conf) {
            $conditionModel = $conf['model'];
            $score = $conditionModel
                ? self::getScoreForCodeOrLabel($conditionModel->code, $conditionModel->label, $conditionModel->sort_order)
                : 0;

            if ($conf['is_structural'] && $score >= 3) {
                $hasSevereStructuralDamage = true;
            }

            $totalScore += $score;

            $items[$key] = [
                'category' => $conf['category'],
                'item_code' => $key,
                'name' => $conf['name'],
                'is_structural' => $conf['is_structural'],
                'condition_id' => $conditionModel?->id,
                'value' => $conditionModel?->code ?? null,
                'label' => $conditionModel?->label ?? 'Belum Dinilai',
                'score' => $score,
            ];
        }

        // Tentukan tingkat prioritas bantuan:
        // Prioritas Tinggi: Skor >= 12 ATAU ada kerusakan berat pada struktur utama
        // Prioritas Sedang: Skor >= 6
        // Prioritas Rendah: Skor < 6
        if ($totalScore >= 12 || $hasSevereStructuralDamage) {
            $priorityLevel = 'tinggi';
        } elseif ($totalScore >= 6) {
            $priorityLevel = 'sedang';
        } else {
            $priorityLevel = 'rendah';
        }

        return [
            'total_score' => $totalScore,
            'priority_level' => $priorityLevel,
            'has_severe_structural_damage' => $hasSevereStructuralDamage,
            'items' => $items,
        ];
    }

    /**
     * Sinkronisasi data skor otomatis ke tabel rtlh_assessments dan rtlh_assessment_items.
     */
    public function syncAssessment(
        House $house,
        ?int $userId = null,
        ?string $notes = null,
        array $itemNotes = [],
        ?string $assessmentDate = null,
        ?string $status = null
    ): RtlhAssessment {
        // Unset relasi kondisi fisik agar data terbaru diambil dari database
        $house->unsetRelation('structure');
        $house->unsetRelation('roof');
        $house->unsetRelation('floor');
        $house->unsetRelation('wall');
        $house->unsetRelation('ceiling');

        $calculated = $this->calculateHouseSurveyScores($house);

        return DB::transaction(function () use ($house, $calculated, $userId, $notes, $itemNotes, $assessmentDate, $status) {
            $year = $house->survey_year ?: (int)date('Y');
            $assessorId = $userId ?: $house->updated_by ?: $house->created_by ?: auth()->id();

            $existingAssessment = RtlhAssessment::with('items')
                ->where('house_id', $house->id)
                ->where('assessment_year', $year)
                ->first();

            $existingItemNotes = $existingAssessment
                ? $existingAssessment->items->pluck('notes', 'item_code')->toArray()
                : [];

            $finalDate = $assessmentDate
                ?: ($existingAssessment?->assessment_date ? $existingAssessment->assessment_date->format('Y-m-d') : now()->format('Y-m-d'));

            $finalStatus = $status
                ?: ($existingAssessment?->status ?: (in_array($house->status, ['submitted', 'verified', 'published']) ? 'completed' : 'draft'));

            $finalNotes = $notes !== null ? $notes : ($existingAssessment?->notes ?? null);

            $assessment = RtlhAssessment::updateOrCreate(
                [
                    'house_id' => $house->id,
                    'assessment_year' => $year,
                ],
                [
                    'assessment_date' => $finalDate,
                    'assessor_id' => $assessorId,
                    'status' => $finalStatus,
                    'score' => $calculated['total_score'],
                    'priority_level' => $calculated['priority_level'],
                    'notes' => $finalNotes,
                ]
            );

            // Perbarui item-item penilaian
            RtlhAssessmentItem::where('assessment_id', $assessment->id)->delete();

            foreach ($calculated['items'] as $item) {
                $customNote = $itemNotes[$item['item_code']]
                    ?? ($existingItemNotes[$item['item_code']] ?? null)
                    ?: ($item['name'] . ': ' . $item['label'] . ' (Skor: ' . $item['score'] . ')');

                RtlhAssessmentItem::create([
                    'assessment_id' => $assessment->id,
                    'category' => $item['category'],
                    'item_code' => $item['item_code'],
                    'value' => $item['value'] ?? 'UNASSESSED',
                    'other_value' => null,
                    'score' => $item['score'],
                    'notes' => $customNote,
                ]);
            }

            return $assessment;
        });
    }
}
