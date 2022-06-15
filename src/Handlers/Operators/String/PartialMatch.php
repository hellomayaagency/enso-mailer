<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\String;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseStringOperator;

class PartialMatch extends BaseStringOperator
{
    protected $label = 'Contains';

    protected $stub = '%#VALUE#%';
}
