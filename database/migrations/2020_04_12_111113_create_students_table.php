<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->integer('stu_id')->autoIncrement();
            $table->string('stu_ticket')->index();
            $table->string('stu_firstname');
            $table->string('stu_secondname');
            $table->string('stu_middlename');
            $table->integer('stu_class_number_id');
            $table->foreign('stu_class_number_id')
                ->references('cla_id')
                ->on('classes')
                ->onUpdate('cascade');
            $table->string('stu_class_letter');
            $table->timestamp('stu_created')->useCurrent();
            $table->timestamp('stu_updated')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
