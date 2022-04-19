<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TaskSkipActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_skip_actions', function (Blueprint $table) {
            $table->id();
            $table->integer("task_id");
            $table->string("task_user");
            $table->string("task_user_url");
            $table->string("task_user_img");            
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
        Schema::dropIfExists('task_actions');
    }
}
