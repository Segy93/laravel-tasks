<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Createing table tasks with name, priority(position), project_id (foreign key on Projects)
         */
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->unsignedBigInteger('priority')->unique();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
         * Dropping table tasks
         */
        Schema::dropIfExists('tasks');
    }
}
