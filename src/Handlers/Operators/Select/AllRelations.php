<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Select;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipSelectOperator;

class AllRelations extends BaseRelationshipSelectOperator
{
    protected $label = 'Has all of';

    protected $operator = '>=';

    /**
     * Gets the 'number' or relationships to match against. The data should
     * have been pre-sorted to present the correct data piece (highest or lowest
     * value) where appropriate.
     *
     * @param Collection $data
     *
     * @return int
     */
    protected function getCount($data)
    {
        return $data->count();
    }
}
