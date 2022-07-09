<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoColumnsToTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('stage_ref_id')->default(7); // defaults the id of 'to do' record
            $table->foreign('stage_ref_id')->references('stage_id')->on('stages');
            $table->foreignId('status_ref_id')->nullable();
            $table->foreign('status_ref_id')->references('status_id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['status_ref_id','stage_ref_id']);
        });
    }
}
