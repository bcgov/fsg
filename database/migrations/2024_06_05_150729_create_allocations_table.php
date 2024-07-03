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
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 32)->index()->unique();

            $table->string('institution_guid', 32)->index();
            $table->foreign('institution_guid')->references('guid')->on('institutions')
                ->onDelete('cascade');
            $table->string('program_year_guid', 32)->index();
            $table->foreign('program_year_guid')->references('guid')->on('program_years')
                ->onDelete('cascade');

            $table->float('total_amount', 2)->default(0)->index();
            $table->float('used_amount', 2)->default(0)->index();
            $table->float('overage_warning_percent', 2)->default(0)->index();
            $table->string('status')->default('active')->index()->comment('active|completed|cancelled');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
