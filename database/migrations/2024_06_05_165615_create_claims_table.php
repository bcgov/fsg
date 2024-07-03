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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();

            $table->string('guid', 32)->index()->unique();

            $table->string('institution_guid', 32)->index();
            $table->foreign('institution_guid')->references('guid')->on('institutions')
                ->onDelete('cascade');
            $table->string('allocation_guid', 32)->index();
            $table->foreign('allocation_guid')->references('guid')->on('allocations')
                ->onDelete('cascade');
            $table->string('student_guid', 32)->index();
            $table->foreign('student_guid')->references('guid')->on('students')
                ->onDelete('cascade');
            $table->string('program_guid', 32)->index();
            $table->foreign('program_guid')->references('guid')->on('programs')
                ->onDelete('cascade');

            $table->bigInteger('sin')->nullable()->comment('Social insurance number.')->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('dob');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('claim_type')->nullable()->default("Program")
                ->comment('This used to be course-by-course. All new records will be set to Program');
            $table->string('course_name')->nullable()->comment("Historical. no need to be populated. ");

            $table->string('claim_status')->nullable();

            $table->float('registration_fee', 2)->default(0)->nullable();
            $table->float('materials_fee', 2)->default(0)->nullable();
            $table->float('program_fee', 2)->default(0)->nullable();
            $table->float('claim_percent', 2)->default(0)->nullable();

            $table->float('estimated_hold_amount', 2)->default(0)->nullable();
            $table->float('total_claim_amount', 2)->default(0)->nullable();

            $table->date('stable_enrolment_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('psi_claim_request_date')->nullable();
            $table->date('reporting_completed_date')->nullable();
            $table->date('claimed_date')->nullable();

            $table->boolean('fifty_two_week_affirmation')->default(true);
            $table->boolean('agreement_confirmed')->default(false);
            $table->boolean('registration_confirmed')->default(false);

            $table->string('claimed_by_user_guid')->nullable();
            $table->string('student_excel_guid')->nullable();
            $table->string('program_excel_guid')->nullable();
            $table->string('claim_excel_guid')->nullable();
            $table->text('process_feedback')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
