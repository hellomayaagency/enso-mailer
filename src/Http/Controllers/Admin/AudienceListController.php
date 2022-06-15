<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Hellomayaagency\Enso\Mailer\Contracts\Audience;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceListController as AudienceListControllerContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Resources\ListResource;
use Yadda\Enso\Facades\EnsoCrud;

class AudienceListController extends BaseController implements AudienceListControllerContract
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        $query = EnsoCrud::modelClass('mailer_audience')::with('conditions');

        $search = $request->get('search', null);

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        return $this->makeListCollection($query->get());
    }

    /**
     * ResourceCollection representing models as data required to fill select
     * lists
     *
     * @param Collection $items
     *
     * @return ResourceCollection
     */
    public function makeListCollection(Collection $items): ResourceCollection
    {
        return ListResource::collection($items);
    }
}
