<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments("employerid")->unique();
            $table->string("title");
            $table->string("definition");
            $table->string("joblocationlatitude");
            $table->string("joblocationlongitude");
            $table->text("jobReadableLocation");
            $table->string("price");
            $table->string("perwhatjb");
            $table->string("range");
            $table->string("workonlyfor");
            $table->string("from");
            $table->string("to");
            $table->string("jobstatus");
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
