<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\CampaignUserController as CampaignUserControllerContract;
use Yadda\Enso\Facades\EnsoCrud;

class CampaignUserController implements CampaignUserControllerContract
{
    public function index($campaign_id)
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);
        $campaign_config = EnsoCrud::config('mailer_campaign');
        $user_config = EnsoCrud::config('user');

        $columns = $user_config->getJsConfig()['columns'];

        return view($campaign_config->getCrudView('users.show'), [
            'crud' => $campaign_config,
            'columns' => $columns,
            'item' => $campaign,
        ]);
    }
}
