<?php

namespace Hellomayaagency\Enso\Mailer\Http\Resources;

use Yadda\Enso\Crud\Resources\TableResource;

class UserTableResource extends TableResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            [
                'name_column' => $this->resource->name_column,
            ]
        );
    }
}
