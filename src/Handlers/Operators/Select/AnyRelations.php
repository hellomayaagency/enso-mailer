<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Select;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;
use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipSelectOperator;

class AnyRelations extends BaseRelationshipSelectOperator
{
    protected $label = 'Has any of';

    protected $operator = '>';

    /**
     * Gets the 'number' or relationships to match against. The data should
     * have been pre-sorted to present the correct data piece (highest or lowest
     * value) where appropriate.
     *
     * @param Collection $data
     *
     * @return integer
     */
    protected function getCount($data)
    {
        return 0;
    }
}
