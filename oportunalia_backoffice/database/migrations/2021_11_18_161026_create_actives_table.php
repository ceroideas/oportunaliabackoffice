<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actives', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId("active_category_id")->constrained();
            $table->string("address");
            $table->string("city");
            $table->foreignId("province_id")->constrained();
            $table->boolean("refund");
            $table->foreignId("active_condition_id")->constrained();
            $table->double("area");
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
        Schema::dropIfExists('actives');
    }
}
