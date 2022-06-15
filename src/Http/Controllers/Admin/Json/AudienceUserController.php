<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\Json;

use Hellomayaagency\Enso\Mailer\Contracts\JsonAudienceUserController;
use Hellomayaagency\Enso\Mailer\Http\Resources\UserTableResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Response;
use Yadda\Enso\Facades\EnsoCrud;

class AudienceUserController implements JsonAudienceUserController
{
    public function index(Request $request, $audience_id)
    {
        $audience = EnsoCrud::modelClass('mailer_audience')::findOrFail($audience_id);

        $query = $audience->generateAudienceQuery();

        if ($search = $request->input('search', null)) {
            $user_config = EnsoCrud::config('user');

            $query->where('username', 'LIKE', '%' . $search . '%');
        }

        $query->orderBy($request->input('orderby', 'id'), $request->input('order', 'desc'));

        $count = $query->count();

        $this->doPagination($query, $request);

        return Response::json([
            'items' => UserTableResource::collection($query->get()),
            'total' => $count,
        ]);
    }

    /**
     * Apply pagination from values in a request to a query
     *
     * @param Builder $query
     * @param Request $request
     *
     * @return Builder $query
     */
    protected function doPagination(Builder $query, Request $request)
    {
        $per_page = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $per_page;

        return $query->offset($offset)->limit($per_page);
    }
}
