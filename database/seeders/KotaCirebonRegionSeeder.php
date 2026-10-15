<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class KotaCirebonRegionSeeder extends Seeder
{
    public function run(): void
    {
        // ── Root: Kota Cirebon ──────────────────────────────────────
        $kota = Region::updateOrCreate(
            ['code' => '32.74'],
            [
                'parent_id' => null,
                'name'      => 'Kota Cirebon',
                'type'      => 'city',
                'level'     => 0,
                'is_active' => true,
            ]
        );

        // ── 5 Kecamatan Kota Cirebon (Kemendagri / BPS) ──────────────
        $kecamatan = [
            ['32.74.01', 'Kejaksan'],
            ['32.74.02', 'Lemahwungkuk'],
            ['32.74.03', 'Harjamukti'],
            ['32.74.04', 'Pekalipan'],
            ['32.74.05', 'Kesambi'],
        ];

        foreach ($kecamatan as [$code, $name]) {
            Region::updateOrCreate(
                ['code' => $code],
                [
                    'parent_id' => $kota->id,
                    'name'      => $name,
                    'type'      => 'district',
                    'level'     => 1,
                    'is_active' => true,
                ]
            );
        }

        // ── 22 Kelurahan Kota Cirebon (Kemendagri / BPS) ─────────────
        // Format: [kode_kelurahan, nama_kelurahan, kode_kecamatan_induk]
        $kelurahan = [
            // Kec. Kejaksan (32.74.01)
            ['32.74.01.1001', 'KEJAKSAN',    '32.74.01'],
            ['32.74.01.1002', 'SUKAPURA',    '32.74.01'],
            ['32.74.01.1003', 'KESENDEN',    '32.74.01'],
            ['32.74.01.1004', 'KEBONBARU',   '32.74.01'],

            // Kec. Lemahwungkuk (32.74.02)
            ['32.74.02.1001', 'KASEPUHAN',   '32.74.02'],
            ['32.74.02.1002', 'LEMAHWUNGKUK','32.74.02'],
            ['32.74.02.1003', 'PANJUNAN',    '32.74.02'],
            ['32.74.02.1004', 'PEGAMBIRAN',  '32.74.02'],

            // Kec. Harjamukti (32.74.03)
            ['32.74.03.1001', 'KECAPI',      '32.74.03'],
            ['32.74.03.1002', 'HARJAMUKTI',  '32.74.03'],
            ['32.74.03.1003', 'LARANGAN',    '32.74.03'],
            ['32.74.03.1004', 'KALIJAGA',    '32.74.03'],
            ['32.74.03.1005', 'ARGASUNYA',   '32.74.03'],

            // Kec. Pekalipan (32.74.04)
            ['32.74.04.1001', 'JAGASATRU',   '32.74.04'],
            ['32.74.04.1002', 'PEKALIPAN',   '32.74.04'],
            ['32.74.04.1003', 'PEKALANGAN',  '32.74.04'],
            ['32.74.04.1004', 'PULASAREN',   '32.74.04'],

            // Kec. Kesambi (32.74.05)
            ['32.74.05.1001', 'PEKIRINGAN',  '32.74.05'],
            ['32.74.05.1002', 'SUNYARAGI',   '32.74.05'],
            ['32.74.05.1003', 'DRAJAT',      '32.74.05'],
            ['32.74.05.1004', 'KESAMBI',     '32.74.05'],
            ['32.74.05.1005', 'KARYAMULYA',  '32.74.05'],
        ];

        foreach ($kelurahan as [$code, $name, $parentCode]) {
            $parent = Region::where('code', $parentCode)->first();

            if (!$parent) {
                if ($this->command) {
                    $this->command->warn("Kecamatan {$parentCode} untuk {$code} - {$name} tidak ditemukan. Dilewati.");
                }
                continue;
            }

            Region::updateOrCreate(
                ['code' => $code],
                [
                    'parent_id' => $parent->id,
                    'name'      => $name,
                    'type'      => 'village',
                    'level'     => 2,
                    'is_active' => true,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('Import wilayah Kota Cirebon selesai: 5 kecamatan, ' . count($kelurahan) . ' kelurahan.');
        }
    }
}
