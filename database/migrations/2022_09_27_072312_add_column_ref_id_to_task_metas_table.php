<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRefIdToTaskMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_metas', function (Blueprint $table) {
            $table->foreignId('column_ref_id')->nullable();
            $table->foreign('column_ref_id')->references('column_id')->on('columns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_metas', function (Blueprint $table) {
            //
        });
    }
}
