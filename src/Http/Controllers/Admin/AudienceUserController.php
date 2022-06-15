<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\Audience;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceCrud;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceUserController as AudienceUserControllerContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Response;
use Yadda\Enso\Facades\EnsoCrud;
use Yadda\Enso\Users\Contracts\UserCrud;

class AudienceUserController implements AudienceUserControllerContract
{
    /**
     * Controller action to show the index page for a given Audience
     *
     * @param Request $request
     * @param string  $audience_id
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $audience_id): \Illuminate\View\View
    {
        $audience = EnsoCrud::modelClass('mailer_audience')::findOrFail($audience_id);
        $audience_config = EnsoCrud::config('mailer_audience');

        return View::make($audience_config->getCrudView('users.show'), [
            'crud' => $audience_config,
            'columns' => EnsoCrud::config('user')->getJsConfig()['columns'],
            'item' => $audience,
        ]);
    }
}
