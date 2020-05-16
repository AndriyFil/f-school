<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
class CreateParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->integer('par_id')->autoIncrement();
//            $table->string('par_firstname')->nullable();
//            $table->string('par_secondname')->nullable();
//            $table->string('par_middlename')->nullable();
//            $table->string('par_phone_number')->nullable();
//            $table->string('par_email')->unique();
            $table->string('par_user_id')->index();
            $table->timestamp('par_created')->useCurrent();
            $table->timestamp('par_updated')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parents');
    }
}
