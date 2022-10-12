<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteAdditionalFieldsFromTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropColumn(['params','name','tags']);
            $table->string('before_value',100)->nullable();
            $table->string('after_value',100)->nullable();
            $table->string('action',40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            //
        });
    }
}
