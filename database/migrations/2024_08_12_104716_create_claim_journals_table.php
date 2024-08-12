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
        Schema::create('claim_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id')->index();
            $table->json('updated_fields'); // Stores the fields that were updated
            $table->json('old_values'); // Stores the old values of the fields
            $table->json('new_values'); // Stores the new values of the fields

            $table->timestamps();

            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_journals');
    }
};
