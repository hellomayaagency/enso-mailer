<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\String;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseStringOperator;

class NotMatches extends BaseStringOperator
{
    protected $label = 'Is not';

    protected $operator = 'NOT LIKE';

    protected $arrayable = true;

    /**
     * Determines how to modify a query with arrayable data
     *
     * @return string
     */
    protected function applyDataArray($query, $operand, $data)
    {
        return $query->whereNotIn($operand, $data);
    }
}
