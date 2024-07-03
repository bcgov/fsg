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
        Schema::create('program_years', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 32)->index()->unique();
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->float('claim_percent', 2)->default(0)->index();

            $table->string('status')->default('active')->index()->comment('active|completed|cancelled');
            $table->text('comment')->nullable();
            $table->string('last_touch_by_user_guid')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_years');
    }
};
