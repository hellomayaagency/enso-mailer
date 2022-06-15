<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\CampaignPreviewController as CampaignPreviewControllerContract;
use Illuminate\Http\Request;
use Yadda\Enso\Facades\EnsoCrud;

class CampaignPreviewController implements CampaignPreviewControllerContract
{
    public function show(Request $request, $campaign_id)
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        $campaign_config = EnsoCrud::config('mailer_campaign');

        return view($campaign_config->getCrudView('preview.show'), [
            'crud' => $campaign_config,
            'item' => $campaign,
        ]);
    }
}
