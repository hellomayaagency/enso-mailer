<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\String;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseStringOperator;

class NotBeginsWith extends BaseStringOperator
{
    protected $label = 'Does not begin with';

    protected $operator = 'NOT LIKE';

    protected $stub = '#VALUE#%';
}
