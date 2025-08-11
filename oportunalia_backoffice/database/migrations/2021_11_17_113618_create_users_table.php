<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('firstname');
            $table->string('lastname');
            $table->date('birthdate');
            $table->string('email',100)->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('cp')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('role_id')->constrained();
            $table->foreignId('province_id')->nullable()->constrained();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->string('document_number');
            $table->string('document_path')->nullable();
            $table->integer('validated')->default(0);
            $table->integer('confirmed')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
