<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operands;

use Hellomayaagency\Enso\Mailer\Handlers\Operands\BaseOperand;

class Username extends BaseOperand
{
    /**
     * Allowed operators for this Operand.
     *
     * @var array
     */
    protected $allowed_operators = [
        'string_matches' => 'string_matches',
        'string_partial_match' => 'string_partial_match',
        'string_begins_with' => 'string_begins_with',
        'string_ends_with' => 'string_ends_with',
        'string_not_matches' => 'string_not_matches',
        'string_not_partial_match' => 'string_not_partial_match',
        'string_not_begins_with' => 'string_not_begins_with',
        'string_not_ends_with' => 'string_not_ends_with',
    ];

    protected $label = 'Username';

    protected $operand = 'users.username';
}
