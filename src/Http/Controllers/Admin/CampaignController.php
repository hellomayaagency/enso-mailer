<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignController as CampaignControllerContract;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Controller;
use Yadda\Enso\Users\Contracts\UserCrud;

class CampaignController extends Controller implements CampaignControllerContract
{
    protected $crud_name = 'mailer_campaign';

    /**
     * Additional 'show' route for the CrudController
     *
     * @param integer $campaign_id
     *
     * @return View
     */
    public function show($campaign_id)
    {
        $campaign = App::make(Campaign::class)::findOrFail($campaign_id);

        $crud = $this->getConfig();

        $user_config = App::make(UserCrud::class);

        $columns = $user_config->getJsConfig()['columns'];

        return view($crud->getCrudView('show'), [
            'crud' => $crud,
            'columns' => $user_config,
            'item' => $campaign,
        ]);
    }
}
