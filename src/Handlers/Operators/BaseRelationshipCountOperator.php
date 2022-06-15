<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;
use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseOperator;

abstract class BaseRelationshipCountOperator extends BaseOperator
{
    /**
     * @var bool
     */
    protected $can_be_grouped = false;

    /**
     * Name of Vue component
     *
     * @var string
     */
    protected $component = 'enso-field-text';

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
        return DataParser::parseNumbers(collect($data), [$this, 'alterDataCollection'], $apply_as);
    }

    /**
     * Function that can be overriden if needed to alter the data collection once
     * it has been parsed into the appropriate format.
     *
     * By default, any greater | less than operations only need the appropriate max | min
     * numbers to compare against, depending on whether they are to be 'AND'ed or 'OR'ed
     * together.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string                         $apply_as
     *
     * @return \Illuminate\Support\Collection
     */
    public function alterDataCollection($collection, $apply_as = 'AND')
    {
        return $collection;
    }

    /**
     * Returns a collection with just the smallest item in it.
     *
     * @param \Illuminate\Support\Collection $collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sliceSmallestNumber($collection)
    {
        return $collection->sortBy(function ($number) {
            return $number;
        })->slice(0, 1);
    }

    /**
     * Returns a collection with just the largest item in it.
     *
     * @param \Illuminate\Support\Collection $collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sliceLargestNumber($collection)
    {
        return $collection->sortByDesc(function ($number) {
            return $number;
        })->slice(0, 1);
    }

    /**
     * Accepts a query, condition and type of application, and alters the
     * query based on these three things.
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
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

        if (empty($condition->getQueryLevel())) {
            throw new Exception('Bad Query Generation: Relationship Operands required a relationship name');
        }

        $relationship_name = $condition->getQueryLevel();
        $operator = $this->getOperatorString();

        // @todo - Investigate a more efficient way of deciding whether a Model has 'X, Y OR Z' relation item count.
        if ($operator === '=') {
            $how_to_modify = $apply_as = 'AND' ? 'where' : 'orWhere';

            return $query->{$how_to_modify}(
                function ($sub_query) use ($relationship_name, $operator, $condition, $data) {
                    $data->each(function ($datum) use ($sub_query, $relationship_name, $operator, $condition, $data) {
                        // We're strictly using orHas here because has with the
                        // potential for multiple values makes no sense
                        $sub_query->orWhereHas($relationship_name, function ($sub_query) use ($condition, $data) {
                            return $this->modifyChildQuery($sub_query, $condition, $data, 'OR');
                        }, $operator, $datum);
                    });
                }
            );
        }

        $how_to_modify = $this->howToApply($apply_as);

        return $query->{$how_to_modify}($relationship_name, function ($sub_query) use ($condition, $data) {
            return $this->modifyChildQuery($sub_query, $condition, $data, $condition->getMatchType());
        }, $operator, $this->getCount($data));
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
        return $apply_as === 'AND' ? 'whereHas' : 'orWhereHas';
    }

    /**
     * Gets the 'number' or relationships to match against. The data should
     * have been pre-sorted to present the correct data piece (highest or lowest
     * value) where appropriate.
     *
     * @param \Illuminate\Support\Collection $data
     *
     * @return integer
     */
    protected function getCount($data)
    {
        return $data->first();
    }

    /**
     * Potentially modifies a relationship count modifier to add extra conditions
     * to a subquery (converting it from a 'has' to a 'whereHas' type query);
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
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

        return $query;
    }
}
