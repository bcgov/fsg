<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('claims', 'funding_type')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->string('funding_type')->nullable()
                    ->comment('Snapshot of the program funding type at claim time. Frozen once the claim is Claimed.')
                    ->after('program_guid');
            });
        }

        // Backfill existing claims with their program's current funding type.
        // Correlated sub-query update is supported by both PostgreSQL (prod) and SQLite (tests).
        DB::statement(
            "UPDATE claims SET funding_type = COALESCE("
            ."(SELECT funding_type FROM programs WHERE programs.guid = claims.program_guid), 'Gov. Priorities') "
            ."WHERE funding_type IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('claims', 'funding_type')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->dropColumn('funding_type');
            });
        }
    }
};
