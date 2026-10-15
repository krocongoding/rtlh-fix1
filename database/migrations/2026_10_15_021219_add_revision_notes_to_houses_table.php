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
            $table->text('revision_notes')->nullable()->after('proposal_notes');
            $table->timestamp('revised_at')->nullable()->after('revision_notes');
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete()->after('revised_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revised_by');
            $table->dropColumn(['revision_notes', 'revised_at']);
        });
    }
};
