<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;
use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseOperator;

abstract class BaseSelectOperator extends BaseOperator
{
    protected $component = 'enso-field-select';

    protected $arrayable = true;

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
        return DataParser::parseSelectData(collect($data), [$this, 'alterDataCollection'], $apply_as);
    }
}
