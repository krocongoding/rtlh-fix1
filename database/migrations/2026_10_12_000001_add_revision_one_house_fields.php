<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->unique()->after('region_id');
            $table->string('head_of_household_name')->nullable()->after('nik');
            $table->string('realization_status', 30)->default('not_realized')->index()->after('status');
            $table->string('funding_source', 100)->nullable()->after('realization_status');
            $table->decimal('budget_amount', 15, 2)->nullable()->after('funding_source');
            $table->string('proposal_document_path')->nullable()->after('budget_amount');
            $table->text('proposal_notes')->nullable()->after('proposal_document_path');
        });

        Schema::table('house_roofs', function (Blueprint $table) {
            $table->foreignId('frame_material_id')->nullable()->constrained('master_values')->nullOnDelete()->after('house_id');
        });

        Schema::table('house_sanitation', function (Blueprint $table) {
            $table->foreignId('wastewater_disposal_id')->nullable()->constrained('master_values')->nullOnDelete()->after('toilet_type_id');
        });

        $now = now();
        $ownershipCategoryId = DB::table('master_categories')->where('code', 'OWNERSHIP_STATUS')->value('id');
        if ($ownershipCategoryId) {
            DB::table('master_values')->where('master_category_id', $ownershipCategoryId)->update(['is_active' => false, 'updated_at' => $now]);
            DB::table('master_values')->updateOrInsert(
                ['master_category_id' => $ownershipCategoryId, 'code' => 'OWNED'],
                ['label' => 'MILIK SENDIRI', 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $landCategoryId = DB::table('master_categories')->where('code', 'LAND_STATUS')->value('id');
        if ($landCategoryId) {
            DB::table('master_values')->where('master_category_id', $landCategoryId)->update(['is_active' => false, 'updated_at' => $now]);
            foreach ([['PARENT', 'ORANG TUA'], ['SIBLING', 'SAUDARA'], ['CHILD', 'ANAK']] as $order => [$code, $label]) {
                DB::table('master_values')->updateOrInsert(
                    ['master_category_id' => $landCategoryId, 'code' => $code],
                    ['label' => $label, 'sort_order' => $order, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $waterCategoryId = DB::table('master_categories')->where('code', 'WATER_SOURCE')->value('id');
        if ($waterCategoryId) {
            DB::table('master_values')->updateOrInsert(
                ['master_category_id' => $waterCategoryId, 'code' => 'NOT_AVAILABLE'],
                ['label' => 'BELUM ADA', 'sort_order' => 99, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $wastewaterCategoryId = DB::table('master_categories')->where('code', 'WASTEWATER_DISPOSAL')->value('id');
        if (!$wastewaterCategoryId) {
            $wastewaterCategoryId = DB::table('master_categories')->insertGetId([
                'code' => 'WASTEWATER_DISPOSAL', 'name' => 'Sanitasi Air Kotor', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach ([['DITCH', 'AIR KALI'], ['RIVER', 'SUNGAI']] as $order => [$code, $label]) {
            DB::table('master_values')->updateOrInsert(
                ['master_category_id' => $wastewaterCategoryId, 'code' => $code],
                ['label' => $label, 'sort_order' => $order, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $roofFrameCategoryId = DB::table('master_categories')->where('code', 'ROOF_FRAME_MATERIAL')->value('id');
        if (!$roofFrameCategoryId) {
            $roofFrameCategoryId = DB::table('master_categories')->insertGetId([
                'code' => 'ROOF_FRAME_MATERIAL', 'name' => 'Bahan Rangka Atap', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach ([['WOOD', 'KAYU'], ['METAL', 'METAL']] as $order => [$code, $label]) {
            DB::table('master_values')->updateOrInsert(
                ['master_category_id' => $roofFrameCategoryId, 'code' => $code],
                ['label' => $label, 'sort_order' => $order, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::table('house_sanitation', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wastewater_disposal_id');
        });

        Schema::table('house_roofs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('frame_material_id');
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropIndex(['realization_status']);
            $table->dropColumn([
                'nik',
                'head_of_household_name',
                'realization_status',
                'funding_source',
                'budget_amount',
                'proposal_document_path',
                'proposal_notes',
            ]);
        });
    }
};
