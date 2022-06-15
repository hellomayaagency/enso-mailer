<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Select;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseSelectOperator;

class Any extends BaseSelectOperator
{
    protected $label = 'Contains';

    protected $operator = '=';
}
