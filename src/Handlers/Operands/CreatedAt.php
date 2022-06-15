<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operands;

use Hellomayaagency\Enso\Mailer\Handlers\Operands\BaseOperand;

/**
 * Operand that deals with Selection base on when
 * a queried item was created at.
 */
class CreatedAt extends BaseOperand
{
    /**
     * Allowed operators for this Operand.
     *
     * @var array
     */
    protected $allowed_operators = [
        'date_greater_than' => 'date_greater_than',
        'date_on_or_greater_than' => 'date_on_or_greater_than',
        'date_equals' => 'date_equals',
        'date_on_or_less_than' => 'date_on_or_less_than',
        'date_less_than' => 'date_less_than',
    ];

    protected $label = 'Created';

    protected $operand = 'users.created_at';
}
