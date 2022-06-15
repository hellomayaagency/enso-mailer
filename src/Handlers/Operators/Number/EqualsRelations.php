<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators\Number;

use Hellomayaagency\Enso\Mailer\Handlers\Operators\BaseRelationshipCountOperator;

class EqualsRelations extends BaseRelationshipCountOperator
{
    protected $label = 'Equals';

    protected $operator = '=';

    protected $arrayable = true;
}
