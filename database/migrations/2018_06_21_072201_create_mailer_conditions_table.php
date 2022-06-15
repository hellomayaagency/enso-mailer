<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailerConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailer_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('audience_id')->index();
            $table->string('component')->default('query-group');
            $table->string('type')->default('AND');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('operand')->nullable();
            $table->string('operator')->nullable();
            $table->json('data')->nullable();
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
        Schema::dropIfExists('mailer_conditions');
    }
}
