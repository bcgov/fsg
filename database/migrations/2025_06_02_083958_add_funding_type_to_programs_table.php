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
        Schema::table('programs', function (Blueprint $table) {
            // Adding funding_type column, nullable with default Gov. Priorities, comment, and index
            $table->string('funding_type')
                ->nullable()
                ->default('Gov. Priorities')
                ->comment('Gov. Priorities|Transferable Skills')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('funding_type');
        });
    }
};
