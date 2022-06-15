<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmailPreviewController implements CampaignEmailController
{
    public function show(Request $request, $campaign_id)
    {
        $campaign = App::make(Campaign::class)::findOrFail($campaign_id);

        return view($campaign->getEmailTemplate(), ['mailable' => $campaign]);
    }
}
