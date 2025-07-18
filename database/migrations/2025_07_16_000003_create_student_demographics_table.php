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
        Schema::create('student_demographics', function (Blueprint $table) {
            $table->id();
            $table->string('student_guid', 32)->index();
            $table->foreign('student_guid')->references('guid')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('demographic_id');
            $table->foreign('demographic_id')->references('id')->on('demographics')->onDelete('cascade');
            $table->text('question_snapshot');
            $table->enum('type', ['text', 'select', 'multi-select', 'radio', 'checkbox']);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['student_guid', 'demographic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_demographics');
    }
};
