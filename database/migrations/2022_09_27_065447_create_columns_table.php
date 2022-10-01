<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('columns', function (Blueprint $table) {
            $table->id('column_id');
            $table->string('name');
            $table->string('title');
            $table->foreignId('personal_ref_id')->nullable();
            $table->foreign('personal_ref_id')->references('personal_id')->on('personals');
            $table->string('type');
            $table->string('default')->nullable();
            $table->string('enum_values')->nullable();
            $table->boolean('nullable')->default(false);
            $table->unsignedInteger('length')->nullable();
            $table->longText('params')->nullable();
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
        Schema::dropIfExists('columns');
    }
}
