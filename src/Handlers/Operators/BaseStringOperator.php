<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

use Hellomayaagency\Enso\Mailer\Handlers\DataParser;

abstract class BaseStringOperator extends BaseOperator
{
    protected $component = 'enso-field-text';

    protected $operator = 'LIKE';

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
        return DataParser::parseStrings(collect($data), [$this, 'alterDataCollection'], $apply_as, $this->getStub());
    }

    /**
     * String data is parsed by optionally replacing the string '#VALUE#' in a stub with the
     * value entered into the form. This allows for you to create stubs that convert strings
     * into Sql fuzzy matched values
     *
     * A Stub of #VALUE#% will convert data from 'a' to 'a%', to simulate 'begins with' for
     * a MySQL `LIKE` statement.
     *
     * @return string|null
     */
    protected function getStub()
    {
        return $this->stub ?? null;
    }
}
