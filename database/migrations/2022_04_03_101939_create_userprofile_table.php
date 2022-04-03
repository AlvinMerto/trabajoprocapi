<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserprofileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userprofile', function (Blueprint $table) {
            $table->increments("empid")->unique();
            $table->integer("userid");
            $table->string("name");
            $table->text("location");
            $table->string("locationlatitude");
            $table->string("locationlongitude");
            $table->text("address");
            $table->string("addresslatitude");
            $table->string("addresslongitude");
            $table->string("pricewage");
            $table->string("perwhat");
            $table->tinyInteger("status");
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
        Schema::dropIfExists('userprofile');
    }
}
