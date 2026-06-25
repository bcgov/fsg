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
        Schema::create('allocation_funding_types', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 32)->index()->unique();

            $table->string('allocation_guid', 32)->index();
            $table->foreign('allocation_guid')->references('guid')->on('allocations')
                ->onDelete('cascade');

            $table->string('funding_type')->index()->comment('Funding Type name from utils');
            $table->float('amount', 2)->default(0)->comment('Portion of the allocation allowed for this funding type');

            $table->string('last_touch_by_user_guid', 32)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_funding_types');
    }
};
