<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Number;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseNumberOperator;

class Equals extends BaseNumberOperator
{
    protected $label = 'Equals';

    protected $operator = '=';

    protected $arrayable = true;
}
