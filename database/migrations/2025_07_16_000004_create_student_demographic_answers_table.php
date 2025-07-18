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
        Schema::create('student_demographic_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_demographic_id')->constrained('student_demographics')->onDelete('cascade');
            $table->string('value');
            $table->string('label_snapshot'); // Snapshot of what the label was at the time of answering
            $table->timestamps();

            $table->index(['student_demographic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_demographic_answers');
    }
};
