<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsMailerRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailer_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->index();
            $table->string('message_id')->index();
            $table->timestamps();
            $table->unique(['campaign_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailer_recipients');
    }
}
