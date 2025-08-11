<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->foreignId("active_id")->constrained();
            $table->foreignId("auction_type_id")->constrained();
            $table->dateTime("start_date");
            $table->dateTime("end_date");
            $table->float("appraisal_value");
            $table->float("start_price");
            $table->float("deposit")->nullable();
            $table->float("commission");
            $table->float("bid_price_interval")->nullable();
            $table->integer("bid_time_interval")->nullable();
            $table->text("description")->nullable();
            $table->text("land_registry")->nullable();
            $table->text("technical_specifications")->nullable();
            $table->text("conditions")->nullable();
            $table->foreignId("auction_status_id")->default(0)->constrained();;
            $table->integer("views");
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
        Schema::dropIfExists('auctions');
    }
}
