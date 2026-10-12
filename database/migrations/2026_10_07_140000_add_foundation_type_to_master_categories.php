<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cat = DB::table('master_categories')->where('code', 'FOUNDATION_TYPE')->first();
        $cid = $cat ? $cat->id : DB::table('master_categories')->insertGetId([
            'code' => 'FOUNDATION_TYPE',
            'name' => 'Jenis / Kondisi Pondasi',
            'description' => 'Pilihan jenis atau kondisi konstruksi pondasi rumah',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $values = [
            ['BATU_KALI', 'BATU KALI / BATU BELAH'],
            ['BETON_BERTULANG', 'BETON BERTULANG / TAPAK'],
            ['BATA_ROLAG', 'BATA MERAH / ROLAG'],
            ['KAYU_CERUCUK', 'KAYU / CERUCUK'],
            ['BAMBU', 'BAMBU'],
            ['TANPA_PONDASI', 'TANPA PONDASI / MENUMPANG TANAH'],
            ['OTHER', 'LAINNYA'],
        ];

        foreach ($values as $i => [$vcode, $label]) {
            DB::table('master_values')->updateOrInsert(
                [
                    'master_category_id' => $cid,
                    'code' => $vcode,
                ],
                [
                    'label' => $label,
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $cat = DB::table('master_categories')->where('code', 'FOUNDATION_TYPE')->first();
        if ($cat) {
            DB::table('master_values')->where('master_category_id', $cat->id)->delete();
            DB::table('master_categories')->where('id', $cat->id)->delete();
        }
    }
};
