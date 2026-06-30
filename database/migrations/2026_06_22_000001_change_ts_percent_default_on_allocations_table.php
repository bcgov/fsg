<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columnExists = Schema::hasColumn('allocations', 'ts_percent');

        Schema::table('allocations', function (Blueprint $table) use ($columnExists) {
            $column = $table->float('ts_percent', 4, 2)->default(0)
                ->comment('DEPRECATED: Transferable Skills Percent. Replaced by allocation_funding_types. Default changed from 20 to 0.');

            if ($columnExists) {
                $column->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('allocations', 'ts_percent')) {
            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->float('ts_percent', 4, 2)->default(20)
                ->comment('Transferable Skills Percent, default is 20%')
                ->change();
        });
    }
};
