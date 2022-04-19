<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string("task_name");
            $table->string("task_type");
            $table->string("login_email");
            $table->string("login_password");            
            $table->string("search_query");
            $table->text("message_male");
            $table->text("message_female");
            $table->text("message_undetect");
            $table->integer("request_delay");
            $table->integer("max_request");
            $table->integer("task_status_id");            
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
        Schema::dropIfExists('tasks');
    }
}
