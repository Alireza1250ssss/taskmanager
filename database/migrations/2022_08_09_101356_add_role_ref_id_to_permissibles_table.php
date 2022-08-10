<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleRefIdToPermissiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissibles', function (Blueprint $table) {
            $table->foreignId('role_ref_id');
            $table->foreign('role_ref_id')->references('role_id')->on('roles')
                ->cascadeOnDelete();
            $table->dropColumn('user_ref_id');

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
            //
        });
    }
}
