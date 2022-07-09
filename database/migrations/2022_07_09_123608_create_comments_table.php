<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->foreignId('user_ref_id');
            $table->foreign('user_ref_id')->references('user_id')->on('users')
                ->cascadeOnDelete();
            $table->string('content');
            $table->morphs('commentable');
            $table->foreignId('parent_id')->nullable();
            $table->foreign('parent_id')->references('comment_id')->on('comments');
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
        Schema::dropIfExists('comments');
    }
}
