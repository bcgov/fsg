<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->float('ts_percent', 4, 2)->default(20)
                ->comment('Transferable Skills Percent, default is 20%');
        });
    }
    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropColumn('ts_percent');
        });
    }
};
