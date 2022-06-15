<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\CampaignCrud;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignPreviewController as CampaignPreviewControllerContract;
use Hellomayaagency\Enso\Mailer\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CampaignPreviewController implements CampaignPreviewControllerContract
{
    public function show(Request $request, $campaign_id)
    {
        $campaign = App::make(Campaign::class)::findOrFail($campaign_id);

        $campaign_config = App::make(CampaignCrud::class);

        return view($campaign_config->getCrudView('preview.show'), [
            'crud' => $campaign_config,
            'item' => $campaign,
        ]);
    }
}
