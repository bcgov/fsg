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
        Schema::create('demographic_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demographic_id')->constrained('demographics')->onDelete('cascade');
            $table->string('label');
            $table->string('value')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['demographic_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demographic_options');
    }
};
