<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToMailerCampaignStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mailer_campaign_stats', function (Blueprint $table) {
            $table->integer('unique_open')->unsigned()->nullable()->after('open');
            $table->integer('unique_click')->unsigned()->nullable()->after('click');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mailer_campaign_stats', function (Blueprint $table) {
            $table->dropColumn(['unique_open', 'unique_click']);
        });
    }
}
