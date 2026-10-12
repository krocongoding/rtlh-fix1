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
     */
    public const COMPONENT_DEFINITIONS = [
        'structure_sloof' => [
            'category' => 'STRUKTUR_SLOOF',
            'name' => 'Sloof / Balok Dasar',
            'getter' => 'structure.sloof_condition_id',
        ],
        'structure_column' => [
            'category' => 'STRUKTUR_KOLOM',
            'name' => 'Kolom / Tiang Penopang',
            'getter' => 'structure.column_condition_id',
        ],
        'structure_beam' => [
            'category' => 'STRUKTUR_BALOK',
            'name' => 'Balok Pengikat',
            'getter' => 'structure.beam_condition_id',
        ],
        'floor_condition' => [
            'category' => 'LANTAI',
            'name' => 'Kondisi Lantai',
            'getter' => 'floor.condition_id',
        ],
        'wall_condition' => [
            'category' => 'DINDING',
            'name' => 'Kondisi Dinding',
            'getter' => 'wall.condition_id',
        ],
        'ceiling_condition' => [
            'category' => 'PLAFON',
            'name' => 'Kondisi Plafon',
            'getter' => 'ceiling.condition_id',
        ],
        'roof_frame_condition' => [
            'category' => 'RANGKA_ATAP',
            'name' => 'Kondisi Rangka Atap',
            'getter' => 'roof.frame_condition_id',
        ],
        'roof_condition' => [
            'category' => 'PENUTUP_ATAP',
            'name' => 'Kondisi Penutup Atap',
            'getter' => 'roof.condition_id',
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

        if ($sortOrder !== null && $sortOrder >= 0 && $sortOrder <= 3) {
            return (int)$sortOrder;
        }

        return 0;
    }

    /**
     * Hitung rincian skor dan total untuk sebuah rumah dari komponen fisiknya.
     */
    public function calculateHouseSurveyScores(House $house): array
    {
        $house->loadMissing([
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'floor.condition',
            'wall.condition',
            'ceiling.condition',
            'roof.frameCondition',
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
            'roof_frame_condition' => [
                'category' => 'RANGKA_ATAP',
                'name' => 'Kondisi Rangka Atap',
                'model' => $house->roof?->frameCondition,
                'is_structural' => true,
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
                'value' => $conditionModel?->code ?? ($score > 0 ? 'NOT_GOOD' : 'GOOD'),
                'label' => $conditionModel?->label ?? 'Belum Dinilai',
                'score' => $score,
            ];
        }

        // Tentukan tingkat prioritas bantuan
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
            'items' => $items,
        ];
    }

    /**
     * Sinkronisasi data skor otomatis ke tabel rtlh_assessments dan rtlh_assessment_items.
     */
    public function syncAssessment(House $house, ?int $userId = null, ?string $notes = null): RtlhAssessment
    {
        $calculated = $this->calculateHouseSurveyScores($house);

        return DB::transaction(function () use ($house, $calculated, $userId, $notes) {
            $year = $house->survey_year ?: (int)date('Y');
            $assessorId = $userId ?: $house->updated_by ?: $house->created_by ?: auth()->id();

            $assessment = RtlhAssessment::updateOrCreate(
                [
                    'house_id' => $house->id,
                    'assessment_year' => $year,
                ],
                [
                    'assessment_date' => now(),
                    'assessor_id' => $assessorId,
                    'status' => in_array($house->status, ['submitted', 'verified', 'published']) ? 'completed' : 'draft',
                    'score' => $calculated['total_score'],
                    'priority_level' => $calculated['priority_level'],
                    'notes' => $notes ?: ($house->latestAssessment?->notes ?? null),
                ]
            );

            // Perbarui item-item penilaian
            RtlhAssessmentItem::where('assessment_id', $assessment->id)->delete();

            foreach ($calculated['items'] as $item) {
                RtlhAssessmentItem::create([
                    'assessment_id' => $assessment->id,
                    'category' => $item['category'],
                    'item_code' => $item['item_code'],
                    'value' => $item['value'],
                    'other_value' => null,
                    'score' => $item['score'],
                    'notes' => $item['name'] . ': ' . $item['label'] . ' (Skor: ' . $item['score'] . ')',
                ]);
            }

            return $assessment;
        });
    }
}
