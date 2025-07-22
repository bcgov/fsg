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
        Schema::create('student_demographic_shares', function (Blueprint $table) {
            $table->id();
            $table->string('student_guid', 32)->index();
            $table->foreign('student_guid')->references('guid')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('demographic_id');
            $table->foreign('demographic_id')->references('id')->on('demographics')->onDelete('cascade');
            $table->unsignedBigInteger('shareable_entity_id');
            $table->foreign('shareable_entity_id')->references('id')->on('shareable_entities')->onDelete('cascade');
            $table->boolean('is_shared')->default(false);
            $table->timestamp('shared_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['student_guid', 'demographic_id', 'shareable_entity_id'], 'student_demo_entity_idx');
            $table->index(['is_shared', 'shared_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_demographic_shares');
    }
};
