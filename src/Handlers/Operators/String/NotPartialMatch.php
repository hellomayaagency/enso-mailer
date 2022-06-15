<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\String;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseStringOperator;

class NotPartialMatch extends BaseStringOperator
{
    protected $label = 'Does not contain';

    protected $operator = 'NOT LIKE';

    protected $stub = '%#VALUE#%';
}
