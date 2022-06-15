<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\CampaignEmailController;
use Illuminate\Http\Request;
use Yadda\Enso\Facades\EnsoCrud;

class EmailPreviewController implements CampaignEmailController
{
    public function show(Request $request, $campaign_id)
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        return view($campaign->getEmailTemplate(), ['mailable' => $campaign]);
    }
}
