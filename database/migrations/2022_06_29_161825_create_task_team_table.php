<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_team', function (Blueprint $table) {
            $table->id('task_team_id');
            $table->foreignId('task_ref_id');
            $table->foreign('task_ref_id')->references('task_id')->on('tasks');
            $table->foreignId('team_ref_id');
            $table->foreign('team_ref_id')->references('team_id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_team');
    }
}
