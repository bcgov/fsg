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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 32)->index()->unique();

            $table->string('institution_guid', 32);
            $table->foreign('institution_guid')->references('guid')->on('institutions')
                ->onDelete('cascade');

            $table->string('program_name');
            $table->string('program_type');
            $table->string('program_number')->nullable()->comment('Historical program number');
            $table->string('delivery_method')->nullable()->comment('In-person|Hybrid or Blended|Online');
            $table->string('online_delivery_type')->nullable()
                ->comment('Synchronous|Asynchronous');
            $table->string('credential_type')->nullable()
                ->comment('Micro-Credential|Short-Certificate|Other');
            $table->string('high_priority_industry')->nullable();

            $table->float('total_duration_hrs')->default(0)->nullable();

            $table->boolean('creditable')->default(true);
            $table->boolean('full_time')->default(true);
            $table->boolean('prov_funded_micro_cred')->default(false)
                ->comment('ProvinciallyFundedMicroCredentialFlg');
            $table->boolean('indigenous_related_learning')->default(false)
                ->comment('Indigenous Related Learning Flg');
            $table->boolean('diversity_inclusion_related_learning')->default(false)
                ->comment('Diversity Inclusion Related Flg');

            $table->boolean('active_status')->comment('program status set by inst.: true / false');
            $table->string('last_touch_by_user_guid')->nullable();

            $table->string('excel_guid')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
