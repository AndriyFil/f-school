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
        Schema::create('schoolboys', function (Blueprint $table) {
            $table->integer('schboy_id')->autoIncrement();
            $table->string('schboy_ticket')->index();
            $table->string('schboy_firstname')->nullable();
            $table->string('schboy_secondname')->nullable();
            $table->string('schboy_middlename')->nullable();
            $table->string('schboy_email')->unique();
            $table->string('schboy_phone_number')->nullable();
            $table->integer('schboy_class_number')->nullable()->index();
            $table->string('schboy_class_letter')->nullable()->index();
            $table->integer('schboy_user_id')->index();
            $table->integer('schboy_school_id')->index();
            $table->timestamp('schboy_created')->useCurrent();
            $table->timestamp('schboy_updated')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
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
