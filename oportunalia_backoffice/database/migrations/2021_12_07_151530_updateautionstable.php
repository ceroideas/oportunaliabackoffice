<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updateautionstable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('auction_documents');

        Schema::table('auctions', function (Blueprint $table) {
            $table->string('description_path');
            $table->string('technical_specifications_path');
            $table->string('land_registry_path');
            $table->string('conditions_path');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
