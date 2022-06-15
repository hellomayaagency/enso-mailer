<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Select;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseSelectOperator;

class NotAny extends BaseSelectOperator
{
    protected $label = 'Does not Contain';

    protected $operator = '!=';

    /**
     * Determines how to modify a query with arrayable data
     *
     * @return string
     */
    protected function applyDataArray($query, $operand, $data)
    {
        return $query->where(function ($sub_query) use ($operand, $data) {
            $sub_query->whereNotIn($operand, $data)->orWhereNull($operand);
        });
    }
}
