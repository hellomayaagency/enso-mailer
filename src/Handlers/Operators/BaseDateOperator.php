<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Hellomayaagency\Enso\Mailer\Handlers\DataParser;
use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseOperator;

abstract class BaseDateOperator extends BaseOperator
{
    protected $component = 'enso-field-date';

    /**
     * Accepts a query, condition and type of application, and alters the
     * query based on these three things.
     *
     * @param \Illuminate\Database\Schema\Builder    $query
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

        // Date fields with an '=' really mean to query by whole days.
        if ($operator === '=') {
            /**
             * Adds conditional to a query that have dates between the start and end of
             * each day in the data collection.
             */
            return $this->queryDays($query, $condition, $data, $apply_as, $condition->getMatchType());
        }

        /**
         * Non-equal data types imply 'before' of 'after'. As such, data should be
         * sorted appropriately so that the first date is the most relevant
         */
        return $query->{$how_to_modify}($operand, $operator, $data->first());
    }

    /**
     * Build a Subquery that selects items based on their operand and
     * on the start and ends of any days passed.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Support\Collection     $days
     */
    protected function queryDays($query, $condition, Collection $days, $apply_as = 'AND', $children_apply_as = 'AND')
    {
        $date_ranges = $days->map(function ($day) {
            return [
                'start' => clone ($day)->startOfDay(),
                'end' => clone ($day)->endOfDay(),
            ];
        });

        $this->queryDateRanges($query, $condition, $date_ranges, $apply_as, $children_apply_as);
    }

    /**
     * Additional functionality to allow querying of date ranges. queryDays defers to this
     * after setting the start and end days to the same day.
     *
     * @param \Illuminate\Database\Query\Builder     $query
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $condition
     * @param \Illuminate\Support\Collection         $ranges
     * @param string                                 $apply_as
     * @param string                                 $children_apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function queryDateRanges(
        $query,
        $condition,
        Collection $ranges,
        $apply_as = 'AND',
        $children_apply_as = 'AND'
    ) {
        $operand = $condition->getOperandObject()->getFieldName();

        if ($ranges->count() === 1) {
            $how_to_modify = $apply_as === 'AND' ? 'whereBetween' : 'orWhereBetween';
            $range = $ranges->first();

            return $query->{$how_to_modify}($operand, [$range['start'], $range['end']]);
        }

        $how_to_modify = $this->howToApply($apply_as);

        return $query->{$how_to_modify}(function ($sub_query) use ($ranges, $operand, $children_apply_as) {
            $how_to_modify_children = ($children_apply_as === 'AND' ? 'whereBetween' : 'orWhereBetween');

            $ranges->each(function ($range) use ($sub_query, $operand, $how_to_modify_children) {
                $sub_query->{$how_to_modify_children}($operand, [$range['start'], $range['end']]);
            });
        });
    }

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
        return DataParser::parseDates(collect($data), [$this, 'alterDataCollection'], $apply_as);
    }
}
