<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(1);
            $table->string('referenced')->nullable();
            $table->string('name')->nullable();
            $table->integer('discount_type')->nullable();
            $table->integer('discount')->nullable();
            $table->string('claim_code')->nullable();
            $table->string('prefix')->nullable();
            $table->timestamp('init_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('all_users')->default(1);
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
        Schema::dropIfExists('campaigns');
    }
}
