<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operators;

abstract class BaseRelationshipAggregateOperator extends BaseOperator
{
    protected $can_be_grouped = false;

    public function modifyQuery($query, $condition, $apply_as = 'AND')
    {
        throw new Exception('Relationship Aggregate queries are currently not implemented');
    }
}
