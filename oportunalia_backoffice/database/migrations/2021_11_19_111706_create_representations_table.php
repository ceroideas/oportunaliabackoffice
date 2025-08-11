<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepresentationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('representations', function (Blueprint $table) {
            $table->id();
            $table->string("alias");
            $table->string("firstname");
            $table->string("lastname");
            $table->string("document_number");
            $table->string("document_path");
            $table->string("address");
            $table->string("city");
            $table->string("cp");
            $table->foreignId("country_id")->constrained();
            $table->foreignId("province_id")->constrained();
            $table->foreignId("representation_type_id")->constrained();
            $table->foreignId("user_id")->constrained();
            $table->boolean("valid");
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
        Schema::dropIfExists('representations');
    }
}
