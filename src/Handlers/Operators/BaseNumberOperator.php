<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;

abstract class BaseNumberOperator extends BaseOperator
{
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
        return DataParser::parseNumbers(collect($data), [$this, 'alterDataCollection'], $apply_as);
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
}
