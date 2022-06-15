<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Number;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipCountOperator;

class NotEqualsRelations extends BaseRelationshipCountOperator
{
    protected $label = 'Does not Equal';

    protected $operator = '!=';

    protected $arrayable = true;

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
