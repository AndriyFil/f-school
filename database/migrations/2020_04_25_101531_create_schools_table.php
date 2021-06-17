<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->integer('sch_id')->autoIncrement();
            $table->string('sch_name')->nullable();
            $table->string('sch_region')->nullable();
            $table->string('sch_city')->nullable();
            $table->timestamp('sch_created')->useCurrent();
            $table->timestamp('sch_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
