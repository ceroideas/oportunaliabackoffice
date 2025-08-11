<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn('technical_specifications_path');
            $table->dropColumn('description_path');
            $table->dropColumn('land_registry_path');
            $table->dropColumn('conditions_path');
            $table->foreignId('technical_archive_id')->constrained("archives");
            $table->foreignId('description_archive_id')->constrained("archives");
            $table->foreignId('land_registry_archive_id')->constrained("archives");
            $table->foreignId('conditions_archive_id')->constrained("archives");
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
