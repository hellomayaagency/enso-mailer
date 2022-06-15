<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;
use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipCountOperator;

abstract class BaseRelationshipSelectOperator extends BaseRelationshipCountOperator
{
    /**
     * Name of Vue component
     *
     * @var string
     */
    protected $component = 'enso-field-select';

    /**
     * Alters the raw data taken from the Condition into a format that will work
     * as part of a query modification.
     *
     * @param array  $data
     * @param string $apply_as
     *
     * @return \Illuminate\Support\Collection
     */
    protected function parseData($data, $apply_as = 'AND')
    {
        return DataParser::parseSelectData(collect($data), [$this, 'alterDataCollection'], $apply_as);
    }

    /**
     * Potentially modifies a relationship count modifier to add extra conditions
     * to a subquery (converting it from a 'has' to a 'whereHas' type query);
     *
     * @param \Illuminate\Database\Query\Builder     $query
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $condition
     * @param \Illuminate\Support\Collection         $data
     *
     * @param string $apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function modifyChildQuery($query, $condition, $data, $apply_as = 'OR')
    {
        $operand_object = $condition->getOperandObject();

        if ($operand_object->overridesChildQueryModifier()) {
            return $operand_object->overrideChildQueryModifier($query, $condition, $data, $apply_as);
        }

        $operand = $condition->getOperandObject()->getFieldName();

        return $query->whereIn($operand, $data);
    }
}
