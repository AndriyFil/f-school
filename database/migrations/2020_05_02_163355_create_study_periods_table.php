<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
class CreateStudyPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_periods', function (Blueprint $table) {
            $table->integer('stuper_id')->autoIncrement();
            $table->string('stuper_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_periods');
    }
}
