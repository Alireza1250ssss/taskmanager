<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToPermissiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissibles', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('permissibles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissibles', function (Blueprint $table) {
            $table->dropColumn(['parent_id']);
        });
    }
}
