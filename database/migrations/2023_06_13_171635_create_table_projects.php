<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Creating table projects with primary id, name and timestamps
         */
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
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
        /**
         * Dropping table projects
         */
        Schema::dropIfExists('projects');
    }
}
