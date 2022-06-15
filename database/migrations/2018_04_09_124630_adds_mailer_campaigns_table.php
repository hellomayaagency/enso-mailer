<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsMailerCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Slug isn't strictly needed for this to work at it's current implementation,
     * however adding one at this stage will allow us to come back to this and
     * introduce a front-end version of an a campaign.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailer_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('subject');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('mail_title')->nullable();
            $table->dateTime('mail_date')->nullable();
            $table->json('mail_body')->nullable();
            $table->datetime('sent_at')->nullable();
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
        Schema::dropIfExists('mailer_campaigns');
    }
}
