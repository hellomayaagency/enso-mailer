<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

interface CampaignController
{
    /**
     * Additional 'show' route for the CrudController
     *
     * @param int $campaign_id
     *
     * @return View
     */
    public function show($campaign_id);
}
