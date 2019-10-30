<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challengers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->notNullable();
            $table->bigInteger('robot_id')->unsigned()->notNullable();
            $table->bigInteger('battle_id')->unsigned()->notNullable();
            $table->boolean('is_victorious')->default(false);
            $table->boolean('is_initiator')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('robot_id')->references('id')->on('robots');
            $table->foreign('battle_id')->references('id')->on('battles');

            $table->index(['robot_id', 'user_id', 'battle_id'], 'challengers_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challengers');
    }
}
