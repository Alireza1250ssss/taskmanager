<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_metas', function (Blueprint $table) {
            $table->id('task_meta_id');
            $table->foreignId('task_ref_id');
            $table->foreign('task_ref_id')->references('task_id')->on('tasks')
            ->cascadeOnDelete();
            $table->string('task_key');
            $table->string('task_value');
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
        Schema::dropIfExists('task_metas');
    }
}
