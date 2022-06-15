<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

interface CampaignStatusController
{
    /**
     * Shows the statistics page for the given Campaign
     *
     * @param integer $campaign_id
     *
     * @return View
     */
    public function show($campaign_id);

    /**
     * Sends the given campaign to it's intended recipients
     *
     * @param integer $campaign_id
     *
     * @return Redirect
     */
    public function send($campaign_id);

    /**
     * Gets the current stats for the given campaign from it's sender
     *
     * @param integer $campaign_id
     *
     * @return Redirect
     */
    public function refresh($campaign_id);
}
