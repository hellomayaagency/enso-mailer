<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsMailerEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailer_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('driver')->index();
            $table->string('campaign_mail_id')->index();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('triggered_at')->nullable();
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
        Schema::dropIfExists('mailer_events');
    }
}
