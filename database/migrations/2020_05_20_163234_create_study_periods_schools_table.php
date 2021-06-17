<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudyPeriodsSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_periods_schools', function (Blueprint $table) {
                $table->integer('stupersch_id')->autoIncrement();
                $table->integer('stupersch_active')->index()->nullable();
                $table->string('stupersch_date_from')->nullable();
                $table->string('stupersch_date_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_periods_schools');
    }
}
