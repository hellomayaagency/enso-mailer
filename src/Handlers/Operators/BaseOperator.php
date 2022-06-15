<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;

abstract class BaseOperator
{
    /**
     * This is the label that will be applied to the select option for this
     * Modifier. Attempts should be made to keep this in line with the
     * concept of fluently reading the results.
     *
     * For example, a great_than_or_equal_to operator, could be 'At least', so that it could read
     * 'Followers At Least [number]'.
     *
     * @var string
     */
    public function getLabel()
    {
        return $this->label ?? 'No Label';
    }

    /**
     * Gets the canonical name of this Operator
     *
     * @return void
     */
    public function getOperatorString()
    {
        return $this->operator;
    }

    /**
     * Gets the Component name for this Modifier
     *
     * @return string
     */
    protected function getComponent()
    {
        return $this->component ?? 'enso-field-text';
    }

    /**
     * Gets the Component Props for this Modifier. This should be an
     * array of data that can be directly bound to the component
     *
     * @return array
     */
    protected function getComponentProps()
    {
        return [];
    }

    /**
     * Checks whether this Operator can have it's data items merged
     * together to create a 'whereIn' clause, in place of multi 'orWhere'
     * clauses.
     *
     * @return bool
     */
    public function isArrayable()
    {
        return $this->arrayable ?? false;
    }

    /**
     * Checks whether this Operator type can be grouped
     * together or not with other operators with the same
     * relationship tree.
     *
     * @return bool
     */
    public function canBeGrouped()
    {
        return $this->can_be_grouped ?? true;
    }

    /**
     * Returns all relevant data that a Query Builder form needs to
     * populate an edit page.
     *
     * @return array
     */
    public function getJsonData()
    {
        return [
            'label' => $this->getLabel(),
            'component' => $this->getComponent(),
            'component_props' => $this->getComponentProps(),
        ];
    }

    /**
     * Function that can be overriden if needed to alter the data collection once
     * it has been parsed into the appropriate format.
     *
     * By default, any greater | less than operations only need the respective max | min
     * numbers to compare against, depending on whether they are to be 'AND'ed or 'OR'ed
     * sends a time along with it's date that it doesn't affect the results.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string $apply_as
     *
     * @return \Illuminate\Support\Collection
     */
    public function alterDataCollection($collection, $apply_as = 'AND')
    {
        return $collection;
    }

    /**
     * Alters the raw data taken from the Condition into a format that will work
     * as part of a query modification.
     *
     * @param array $data
     * @param string $apply_as
     *
     * @return \Illuminate\Support\Collection
     */
    protected function parseData($data, $apply_as = 'AND')
    {
        return DataParser::parseGeneric($data, [$this, 'alterDataCollection'], $apply_as);
    }

    /**
     * Accepts a query, condition and type of application, and alters the
     * query based on these three things.
     *
     * @param \Illuminate\Database\Query\Builder     $query
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $condition
     * @param string                                 $apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function modifyQuery($query, $condition, $apply_as = 'AND')
    {
        $data = $this->parseData($condition->getData(), $condition->getMatchType());

        if ($data->count() === 0) {
            return $query;
        }

        $how_to_modify = $this->howToApply($apply_as);
        $operand = $condition->getOperandObject()->getFieldName();
        $operator = $this->getOperatorString();

        // If there is only one data item, return the more efficient format.
        if ($data->count() === 1) {
            return $query->{$how_to_modify}($operand, $operator, $data->first());
        }

        if ($this->isArrayable()) {
            return $this->applyDataArray($query, $operand, $data);
        }

        return $query->{$how_to_modify}(function ($sub_query) use ($condition, $operand, $operator, $data) {
            $how_to_modify = $this->howToApplyChildren($condition->getMatchType());

            foreach ($data as $data) {
                $sub_query->{$how_to_modify}($operand, $operator, $data);
            }
        });
    }

    /**
     * Determines what type of query modifier to use, base on an
     * 'AND'/'OR' value.
     *
     * @param string $apply_as
     *
     * @return string
     */
    protected function howToApply($apply_as)
    {
        return $apply_as === 'AND' ? 'where' : 'orWhere';
    }

    /**
     * Determines how to modify a query with arrayable data
     *
     * @return string
     */
    protected function applyDataArray($query, $operand, $data)
    {
        return $query->whereIn($operand, $data);
    }

    /**
     * Determines what type of query modifier to use, based on
     * a condition who children need combining.
     *
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $condition
     *
     * @return string
     */
    protected function howToApplyChildren($apply_as)
    {
        return $apply_as === 'AND' ? 'where' : 'orWhere';
    }
}
