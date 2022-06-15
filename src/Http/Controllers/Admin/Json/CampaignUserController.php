<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\Json;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\JsonCampaignUserController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Response;

class CampaignUserController implements JsonCampaignUserController
{
    public function index(Request $request, $campaign_id)
    {
        $campaign = App::make(Campaign::class)::findOrFail($campaign_id);

        $args = [
            'orderby' => $request->input('orderby', 'order'),
            'order'   => $request->input('order', 'desc'),
            'search'  => $request->input('search', null),
        ];

        $query = $campaign->generateAudienceQuery();
        $search = $request->input('search', null);

        if ($search) {
            $query->where('username', 'LIKE', '%' . $search . '%');
        }

        $query->orderBy($request->input('orderby', 'id'), $request->input('order', 'desc'));

        $count = $query->count();

        $this->doPagination($query, $request);

        $items = $query->get()->map(function ($user) {
            $user_data = $user->toArray();
            $user_data['name_column'] = $user->name_column;
            return $user_data;
        });

        return Response::json([
            'total' => $count,
            'items' => $items,
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
