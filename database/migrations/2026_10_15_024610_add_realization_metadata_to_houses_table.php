<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->timestamp('realized_at')->nullable()->after('realization_status');
            $table->foreignId('realized_by')->nullable()->constrained('users')->nullOnDelete()->after('realized_at');
            $table->text('realization_notes')->nullable()->after('realized_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('realized_by');
            $table->dropColumn(['realized_at', 'realization_notes']);
        });
    }
};
