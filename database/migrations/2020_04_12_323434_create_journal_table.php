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
            $table->integer('jou_schoolboy_id')->index();
            $table->integer('jou_subject_id')->index();
            $table->integer('jou_teacher_id')->index();
            $table->integer('jou_rating');
            $table->integer('jou_rating_type_id')->index();
            $table->integer('jou_school_id')->index();
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
