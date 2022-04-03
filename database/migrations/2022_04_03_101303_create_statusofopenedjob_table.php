<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusofopenedjobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statusofopenedjob', function (Blueprint $table) {
            $table->increments("soojid")->unique();
            $table->string("jobid");
            $table->string("workerid");
            $table->integer("status");
            $table->date("targetdate");            
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
        Schema::dropIfExists('statusofopenedjob');
    }
}
