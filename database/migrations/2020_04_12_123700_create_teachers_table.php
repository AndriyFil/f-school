<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->integer('tea_id')->autoIncrement();
            $table->string('tea_firstname')->nullable();
            $table->string('tea_secondname')->nullable();
            $table->string('tea_middlename')->nullable();
            $table->string('tea_phone_number')->nullable();
            $table->string('tea_email')->unique();
            $table->string('tea_user_id')->index();
            $table->integer('tea_class_from')->nullable()->index();
            $table->integer('tea_class_to')->nullable()->index();
            $table->integer('tea_class_number')->nullable()->index();
            $table->integer('tea_class_letter')->nullable()->index();
            $table->integer('tea_type_id')->index();
            $table->integer('tea_school_id')->index();
            $table->timestamp('tea_created')->useCurrent();
            $table->timestamp('tea_updated')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}
