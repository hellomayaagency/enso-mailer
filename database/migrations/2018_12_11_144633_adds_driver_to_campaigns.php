<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsDriverToCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mailer_campaigns', function (Blueprint $table) {
            $table->string('driver')->nullable()->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mailer_campaigns', function (Blueprint $table) {
            $table->dropColumn('driver');
        });
    }
}
