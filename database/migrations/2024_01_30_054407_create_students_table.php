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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 32)->index()->unique();

            $table->string('user_guid', 32)->index();
            $table->foreign('user_guid')->references('guid')->on('users')
                ->onDelete('cascade');

            $table->bigInteger('sin')->nullable()->comment('Social insurance number.')->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('dob');
            $table->string('gender', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('citizenship', 20)->nullable();
            $table->string('grade12_or_over19', 20)->nullable();

            $table->boolean('bc_resident')->default(false);
            $table->boolean('info_consent')->default(false);
            $table->boolean('duplicative_funding')->default(false);
            $table->boolean('tax_implications')->default(false);
            $table->boolean('lifetime_max')->default(false);
            $table->boolean('fed_prov_benefits')->default(false);
            $table->boolean('workbc_client')->default(false);
            $table->boolean('additional_supports')->default(false);
            $table->float('total_grant')->default(0);

            $table->string('excel_guid')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
