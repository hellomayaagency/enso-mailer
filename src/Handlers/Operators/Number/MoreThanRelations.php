<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Number;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipCountOperator;

class MoreThanRelations extends BaseRelationshipCountOperator
{
    protected $label = 'More than';

    protected $operator = '>';

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
        if ($apply_as === 'AND') {
            return $this->sliceLargestNumber($collection);
        } else {
            return $this->sliceSmallestNumber($collection);
        }
    }
}
