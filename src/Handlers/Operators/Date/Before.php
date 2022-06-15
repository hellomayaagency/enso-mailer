<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Date;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseDateOperator;

class Before extends BaseDateOperator
{
    protected $label = 'Before';

    protected $operator = '<';

    /**
     * Function that can be overriden if needed to alter the data collection once
     * it has been parsed into the appropriate format.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string                         $apply_as
     *
     * @return \Illuminate\Support\Collection
     */
    public function alterDataCollection($collection, $apply_as = 'AND')
    {
        return $collection->map->startOfDay();
    }
}
