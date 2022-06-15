<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailerCampaignStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailer_campaign_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->unique();
            $table->integer('send')->unsigned()->nullable();
            $table->integer('open')->unsigned()->nullable();
            $table->integer('click')->unsigned()->nullable();
            $table->integer('soft_bounce')->unsigned()->nullable();
            $table->integer('hard_bounce')->unsigned()->nullable();
            $table->integer('spam')->unsigned()->nullable();
            $table->integer('unsub')->unsigned()->nullable();
            $table->integer('reject')->unsigned()->nullable();
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
        Schema::dropIfExists('mailer_campaign_stats');
    }
}
