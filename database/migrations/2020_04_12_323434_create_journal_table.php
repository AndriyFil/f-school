<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->integer('jou_record')->autoIncrement();
            $table->integer('jou_student_id');
            $table->foreign('jou_student_id')
                ->references('stu_id')
                ->on('students')
                ->onUpdate('cascade');
            $table->integer('jou_teacher_id');
            $table->foreign('jou_teacher_id')
                ->references('tea_id')
                ->on('teachers')
                ->onUpdate('cascade');
            $table->integer('jou_subject_id');
            $table->foreign('jou_subject_id')
                ->references('sub_id')
                ->on('subjects')
                ->onUpdate('cascade');
            $table->integer('jou_rating_type_id');
            $table->foreign('jou_rating_type_id')
                ->references('rattyp_id')
                ->on('rating_types')
                ->onUpdate('cascade');
            $table->timestamp('jou_created')->useCurrent();
            $table->timestamp('jou_updated')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal');
    }
}
