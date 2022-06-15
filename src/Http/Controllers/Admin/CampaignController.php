<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignController as CampaignControllerContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Controller;
use Yadda\Enso\Users\Contracts\UserCrud;

class CampaignController extends Controller implements CampaignControllerContract
{
    protected $crud_name = 'mailer_campaign';

    /**
     * Additional 'show' route for the CrudController
     *
     * @param int $campaign_id
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

    /**
     * Clone an item
     *
     * @todo When Ensō makes the underlying unique slug code available outside
     *       of the SlugField, update this code to defer to that
     *
     * @param Model $item
     *
     * @return Model
     */
    protected function cloneItem(Model $item): Model
    {
        $new_item = $item->replicate(['slug', 'sent_at']);

        $slug_field = \Yadda\Enso\Crud\Forms\Fields\SlugField::make('slug');
        $section = \Yadda\Enso\Crud\Forms\Section::make('main');
        $section->addField($slug_field);

        $slug_field->applyRequestData($new_item, ['main' => ['slug' => $item->slug]]);

        return $new_item;
    }

    /**
     * Copy any relationship that need
     *
     * @param Model $item
     *
     * @return void
     */
    protected function replicateRelations(Model $item, Model $clone): void
    {
        $clone->audiences()->sync($item->audiences->pluck('id'));
    }
}
