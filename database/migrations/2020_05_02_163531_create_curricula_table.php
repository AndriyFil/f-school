<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
class CreateCurriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curricula', function (Blueprint $table) {
            $table->integer('stuper_id')->autoIncrement();
            $table->string('stuper_lesson_theme');
            $table->integer('stuper_study_period_id')->index();
            $table->integer('stuper_school_id')->index()->nullable();
            $table->integer('stuper_subject_id')->index();
            $table->integer('stuper_class_number')->index();
            $table->timestamp('stuper_created')->useCurrent();
            $table->timestamp('stuper_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curricula');
    }
}
