<?php

use App\Models\House;
use App\Models\HouseFloor;
use App\Models\HouseRoof;
use App\Models\HouseStructure;
use App\Models\HouseWall;
use App\Models\HouseCeiling;
use App\Models\MasterCategory;
use App\Models\MasterValue;
use App\Models\Region;
use App\Models\Role;
use App\Models\RtlhAssessment;
use App\Models\User;
use App\Services\HouseAssessmentService;
use App\Services\HouseDataService;

beforeEach(function () {
    // Pastikan role admin tersedia
    $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $this->admin = User::firstOrCreate(
        ['email' => 'admin-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($role);
    }

    // Ambil atau buat region
    $this->region = Region::firstOrCreate(
        ['code' => '32.74.01.1001'],
        [
            'name' => 'Kesenden',
            'type' => 'village',
            'level' => 2,
            'is_active' => true,
        ]
    );

    // Ambil master values untuk HOUSE_CONDITION
    $cat = MasterCategory::firstOrCreate(['code' => 'HOUSE_CONDITION'], ['name' => 'Kondisi Rumah', 'is_active' => true]);
    $this->valBaik = MasterValue::firstOrCreate(['master_category_id' => $cat->id, 'code' => 'GOOD'], ['label' => 'BAIK', 'sort_order' => 0, 'is_active' => true]);
    $this->valRR = MasterValue::firstOrCreate(['master_category_id' => $cat->id, 'code' => 'RR_SURFACE'], ['label' => 'RUSAK RINGAN', 'sort_order' => 1, 'is_active' => true]);
    $this->valRS = MasterValue::firstOrCreate(['master_category_id' => $cat->id, 'code' => 'RS_MATERIAL'], ['label' => 'RUSAK SEDANG', 'sort_order' => 2, 'is_active' => true]);
    $this->valRB = MasterValue::firstOrCreate(['master_category_id' => $cat->id, 'code' => 'RB'], ['label' => 'RUSAK BERAT', 'sort_order' => 3, 'is_active' => true]);
});

test('calculateHouseSurveyScores calculates 8 components accurately', function () {
    $house = House::create([
        'house_code' => 'TEST-RTLH-001',
        'region_id' => $this->region->id,
        'nik' => '3274010101010001',
        'head_of_household_name' => 'Budi Test',
        'survey_year' => 2026,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    HouseStructure::create([
        'house_id' => $house->id,
        'sloof_condition_id' => $this->valBaik->id,
        'column_condition_id' => $this->valRR->id, // 1
        'beam_condition_id' => $this->valRS->id,   // 2
    ]);

    HouseRoof::create([
        'house_id' => $house->id,
        'frame_condition_id' => $this->valBaik->id, // 0
        'condition_id' => $this->valRR->id,         // 1
    ]);

    HouseFloor::create([
        'house_id' => $house->id,
        'condition_id' => $this->valBaik->id, // 0
    ]);

    HouseWall::create([
        'house_id' => $house->id,
        'condition_id' => $this->valRS->id, // 2
    ]);

    HouseCeiling::create([
        'house_id' => $house->id,
        'condition_id' => $this->valRR->id, // 1
    ]);

    $service = app(HouseAssessmentService::class);
    $scores = $service->calculateHouseSurveyScores($house);

    // Total: column(1) + beam(2) + roof(1) + wall(2) + ceiling(1) = 7
    expect($scores['total_score'])->toBe(7);
    expect($scores['priority_level'])->toBe('sedang');
    expect(count($scores['items']))->toBe(8);

    // Structural component with Rusak Berat automatically triggers Prioritas Tinggi
    $house->structure->update(['sloof_condition_id' => $this->valRB->id]);
    $scoresSevere = $service->calculateHouseSurveyScores($house->fresh());

    expect($scoresSevere['has_severe_structural_damage'])->toBeTrue();
    expect($scoresSevere['priority_level'])->toBe('tinggi');
});

test('assessment edit page loads successfully with prefilled 8 physical components', function () {
    $house = House::create([
        'house_code' => 'TEST-RTLH-002',
        'region_id' => $this->region->id,
        'nik' => '3274010101010002',
        'head_of_household_name' => 'Siti Test',
        'survey_year' => 2026,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    HouseStructure::create([
        'house_id' => $house->id,
        'sloof_condition_id' => $this->valRR->id,
        'column_condition_id' => $this->valBaik->id,
        'beam_condition_id' => $this->valBaik->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('houses.assessment.edit', $house));

    $response->assertStatus(200);
    $response->assertSee('8 Parameter Kondisi Fisik Rumah');
    $response->assertSee('ASPEK KESELAMATAN STRUKTUR');
    $response->assertSee('ASPEK ARSITEKTUR & PENUTUP', false);
    $response->assertSee('Sloof / Balok Dasar');
    $response->assertSee('Kondisi Rangka Atap');
});

test('assessment update synchronizes physical house tables and assessment items without mismatch', function () {
    $house = House::create([
        'house_code' => 'TEST-RTLH-003',
        'region_id' => $this->region->id,
        'nik' => '3274010101010003',
        'head_of_household_name' => 'Ahmad Test',
        'survey_year' => 2026,
        'status' => 'draft',
        'created_by' => $this->admin->id,
    ]);

    $updatePayload = [
        'assessment_year' => 2026,
        'assessment_date' => now()->format('Y-m-d'),
        'status' => 'verified',
        'notes' => 'Catatan verifikator dinas',
        'conditions' => [
            'structure_sloof' => $this->valRS->id,          // 2
            'structure_column' => $this->valRS->id,         // 2
            'structure_beam' => $this->valRR->id,           // 1
            'roof_frame_condition' => $this->valBaik->id,   // 0
            'floor_condition' => $this->valBaik->id,        // 0
            'wall_condition' => $this->valRS->id,           // 2
            'ceiling_condition' => $this->valRR->id,        // 1
            'roof_condition' => $this->valRR->id,           // 1
        ],
        'item_notes' => [
            'structure_sloof' => 'Sloof mengalami retak sedang',
        ],
    ];

    $response = $this->actingAs($this->admin)
        ->put(route('houses.assessment.update', $house), $updatePayload);

    $response->assertRedirect(route('houses.show', $house));
    $response->assertSessionHas('success');

    // Cek bahwa tabel fisik rumah ter-update
    $house->refresh();
    expect($house->structure->sloof_condition_id)->toBe($this->valRS->id);
    expect($house->structure->column_condition_id)->toBe($this->valRS->id);
    expect($house->wall->condition_id)->toBe($this->valRS->id);

    // Cek bahwa rtlh_assessments tersinkronkan
    $assessment = RtlhAssessment::where('house_id', $house->id)->first();
    expect($assessment)->not->toBeNull();
    // Total: 2 + 2 + 1 + 0 + 0 + 2 + 1 + 1 = 9
    expect((float)$assessment->score)->toBe(9.0);
    expect($assessment->priority_level)->toBe('sedang');
    expect($assessment->status)->toBe('verified');
    expect($assessment->notes)->toBe('Catatan verifikator dinas');

    // Cek bahwa 8 item assessment tersimpan dengan benar
    expect($assessment->items()->count())->toBe(8);
    $sloofItem = $assessment->items()->where('item_code', 'structure_sloof')->first();
    expect($sloofItem->notes)->toBe('Sloof mengalami retak sedang');
    expect((float)$sloofItem->score)->toBe(2.0);
});
