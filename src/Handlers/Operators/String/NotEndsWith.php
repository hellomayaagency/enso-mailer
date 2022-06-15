<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\String;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseStringOperator;

class NotEndsWith extends BaseStringOperator
{
    protected $label = 'Does not end with';

    protected $operator = 'NOT LIKE';

    protected $stub = '%#VALUE#';
}
